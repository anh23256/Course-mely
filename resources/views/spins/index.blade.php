@extends('layouts.app')

@push('page-css')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .container { margin-top: 20px; }
        .card { margin-bottom: 20px; }
        .table { font-size: 14px; }
        .form-control-sm { max-width: 100px; }
        canvas { max-width: 100%; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 1050; }
    </style>
@endpush

@php
    $title = 'Quản lý Vòng Quay May Mắn';
@endphp

@section('content')
    <div class="container">
        <!-- Toast Container -->
        <div class="toast-container">
            <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Thông báo</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Đã thêm thành công!
                </div>
            </div>
        </div>
        <h1 class="mb-4">{{ $title }}</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Cấu hình tỷ lệ trúng -->
        <div class="card">
            <div class="card-header">
                <h3>Cấu hình tỷ lệ trúng (Tổng: {{ $totalProbability }}%)</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGiftModal">
                        Thêm quà hiện vật
                    </button>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Loại</th>
                            <th>Tên</th>
                            <th>Tỷ lệ (%)</th>
                            <th>Hành động</th>
                            <th>Chọn</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Spin Configs -->
                        @foreach ($spinConfigs as $config)
                            <tr>
                                <td>{{ $config->type }}</td>
                                <td>{{ $config->name }}</td>
                                <td>{{ $config->probability }}</td>
                                <td>
                                    <form action="{{ route('admin.spins.spin-config.update', $config->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="probability" value="{{ $config->probability }}" step="0.01" min="0" max="100" class="form-control form-control-sm d-inline" required>
                                        <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                    </form>
                                </td>
                                <td>
                                    <input type="checkbox" checked disabled>
                                </td>
                            </tr>
                        @endforeach
                        <!-- Gifts -->
                        @foreach ($gifts as $gift)
                            <tr>
                                <td>Gift</td>
                                <td>{{ $gift->name }}</td>
                                <td>{{ $gift->probability }}</td>
                                <td>
                                    <form action="{{ route('admin.spins.gift.update', $gift->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="probability" value="{{ $gift->probability }}" step="0.01" min="0" max="100" class="form-control form-control-sm d-inline" required>
                                        <input type="hidden" name="name" value="{{ $gift->name }}">
                                        <input type="hidden" name="stock" value="{{ $gift->stock }}">
                                        <input type="hidden" name="description" value="{{ $gift->description }}">
                                        <input type="hidden" name="image_url" value="{{ $gift->image_url }}">
                                        <input type="hidden" name="is_active" value="{{ $gift->is_active }}">
                                        <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                    </form>
                                    <form action="{{ route('admin.spins.gift.delete', $gift->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('admin.spins.toggle-selection', ['type' => 'gift', 'id' => $gift->id]) }}" method="POST" class="toggle-selection-form">
                                        @csrf
                                        <input type="checkbox" name="is_selected" onchange="this.form.submit()" {{ $gift->is_selected ? 'checked' : '' }}>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Form thêm quà hiện vật (giữ nguyên) -->
                <h4 class="mt-4">Thêm quà hiện vật</h4>
                <form action="{{ route('admin.spins.gift.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Tên quà" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="stock" value="{{ old('stock') }}" class="form-control" placeholder="Số lượng" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="probability" value="{{ old('probability') }}" class="form-control" placeholder="Tỷ lệ (%)" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="col-md-3">
                        <input type="url" name="image_url" value="{{ old('image_url') }}" class="form-control" placeholder="URL ảnh">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Thêm quà</button>
                    </div>
                    <div class="col-12">
                        <textarea name="description" class="form-control" placeholder="Mô tả">{{ old('description') }}</textarea>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal thêm quà hiện vật -->
        <div class="modal fade" id="addGiftModal" tabindex="-1" aria-labelledby="addGiftModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGiftModalLabel">Chọn quà hiện vật</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($availableGifts->isEmpty())
                            <p>Không có quà hiện vật nào để chọn.</p>
                        @else
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tên quà</th>
                                        <th>Tỷ lệ (%)</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($availableGifts as $gift)
                                        <tr>
                                            <td>{{ $gift->name }}</td>
                                            <td>{{ $gift->probability }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm select-reward" data-type="gift" data-id="{{ $gift->id }}">Chọn</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng số lượt quay (Biểu đồ) -->
        <div class="card">
            <div class="card-header">
                <h3>Tổng số lượt quay</h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="spinTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="day-tab" data-bs-toggle="tab" href="#day" role="tab">Theo ngày</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="month-tab" data-bs-toggle="tab" href="#month" role="tab">Theo tháng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="year-tab" data-bs-toggle="tab" href="#year" role="tab">Theo năm</a>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="spinTabContent">
                    <div class="tab-pane fade show active" id="day" role="tabpanel">
                        <canvas id="spinChartDay"></canvas>
                    </div>
                    <div class="tab-pane fade" id="month" role="tabpanel">
                        <canvas id="spinChartMonth"></canvas>
                    </div>
                    <div class="tab-pane fade" id="year" role="tabpanel">
                        <canvas id="spinChartYear"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách người dùng trúng quà hiện vật -->
        <div class="card">
            <div class="card-header">
                <h3>Danh sách người dùng trúng quà hiện vật</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tên người dùng</th>
                            <th>Quà trúng</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($giftWinners as $winner)
                            <tr>
                                <td>{{ $winner->user->name ?? 'Không xác định' }}</td>
                                <td>{{ $winner->reward_name }}</td>
                                <td>{{ $winner->spun_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Hàm đóng modal thủ công
        function forceCloseModal(modal) {
            try {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                    console.log('Modal closed using Bootstrap Modal instance');
                } else {
                    console.warn('Bootstrap Modal instance not found, closing manually');
                }

                // Đảm bảo modal được ẩn
                modal.classList.remove('show');
                modal.style.display = 'none';

                // Gỡ bỏ lớp modal-open trên body
                document.body.classList.remove('modal-open');

                // Gỡ bỏ lớp modal-backdrop
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());

                // Đặt lại overflow của body
                document.body.style.overflow = 'auto';
                document.body.style.paddingRight = '0px';

                console.log('Modal closed successfully');
            } catch (error) {
                console.error('Error while closing modal:', error);
            }
        }
        // Biểu đồ (giữ nguyên)
        const spinChartDay = new Chart(document.getElementById('spinChartDay'), {
            type: 'bar',
            data: {
                labels: [@foreach ($spinStatsDay as $stat)"{{ $stat->date }}",@endforeach],
                datasets: [{
                    label: 'Số lượt quay',
                    data: [@foreach ($spinStatsDay as $stat){{ $stat->spins }},@endforeach],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const spinChartMonth = new Chart(document.getElementById('spinChartMonth'), {
            type: 'bar',
            data: {
                labels: [@foreach ($spinStatsMonth as $stat)"{{ $stat->month }}/{{ $stat->year }}",@endforeach],
                datasets: [{
                    label: 'Số lượt quay',
                    data: [@foreach ($spinStatsMonth as $stat){{ $stat->spins }},@endforeach],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const spinChartYear = new Chart(document.getElementById('spinChartYear'), {
            type: 'bar',
            data: {
                labels: [@foreach ($spinStatsYear as $stat)"{{ $stat->year }}",@endforeach],
                datasets: [{
                    label: 'Số lượt quay',
                    data: [@foreach ($spinStatsYear as $stat){{ $stat->spins }},@endforeach],
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Xử lý chọn phần thưởng từ modal
        document.querySelectorAll('.select-reward').forEach(button => {
            button.addEventListener('click', function () {
                const type = this.getAttribute('data-type');
                const id = this.getAttribute('data-id');
                const row = this.closest('tr');
                const name = row.cells[0].textContent;
                const probability = row.cells[1].textContent;
                const modal = this.closest('.modal');

                // Vô hiệu hóa nút để tránh click liên tục
                button.disabled = true;

                fetch(`/admin/spins/spin/toggle-selection/${type}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Đóng modal
                        setTimeout(() => {
                            forceCloseModal(modal);
                        }, 100);

                        // Hiển thị toast
                        const toastElement = document.getElementById('successToast');
                        if (toastElement) {
                            const toast = new bootstrap.Toast(toastElement, {
                                autohide: true,
                                delay: 3000
                            });
                            toast.show();
                        } else {
                            console.error('Không tìm thấy toast element');
                            alert('Đã thêm thành công!');
                        }

                        // Thêm phần thưởng vào bảng
                        const tbody = document.querySelector('table.table-striped tbody');
                        const newRow = document.createElement('tr');
                        newRow.innerHTML = `
                            <td>${type.charAt(0).toUpperCase() + type.slice(1)}</td>
                            <td>${name}</td>
                            <td>${probability}</td>
                            <td>
                                ${type === 'gift' ? `
                                    <form action="/admin/spins/gifts/${id}" method="POST" class="d-inline">
                                        <input type="hidden" name="_method" value="PUT">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                                        <input type="number" name="probability" value="${probability}" step="0.01" min="0" max="100" class="form-control form-control-sm d-inline" required>
                                        <input type="hidden" name="name" value="${name}">
                                        <input type="hidden" name="stock" value="0">
                                        <input type="hidden" name="description" value="">
                                        <input type="hidden" name="image_url" value="">
                                        <input type="hidden" name="is_active" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                    </form>
                                    <form action="/admin/spins/gifts/${id}" method="POST" class="d-inline">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</button>
                                    </form>
                                ` : `<span class="text-muted">Quản lý ở mục Coupon</span>`}
                            </td>
                            <td>
                                <form action="/admin/spins/spin/toggle-selection/${type}/${id}" method="POST" class="toggle-selection-form">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                                    <input type="checkbox" name="is_selected" onchange="this.form.submit()" checked>
                                </form>
                            </td>
                        `;
                        tbody.appendChild(newRow);

                        // Xóa hàng khỏi modal
                        row.remove();

                        // Cập nhật tổng tỷ lệ trúng
                        const totalProbabilityElement = document.querySelector('.card-header h3');
                        const currentTotal = parseFloat(totalProbabilityElement.textContent.match(/Tổng: ([\d.]+)%/)[1]);
                        const newTotal = currentTotal + parseFloat(probability);
                        totalProbabilityElement.textContent = `Cấu hình tỷ lệ trúng (Tổng: ${newTotal.toFixed(4)}%)`;
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra, vui lòng thử lại');
                });
            });
        });

        // Xử lý bỏ chọn từ bảng
        document.querySelectorAll('.toggle-selection-form input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const form = this.closest('form');
                const type = form.action.match(/toggle-selection\/(\w+)\//)[1];
                const id = form.action.match(/toggle-selection\/\w+\/(\d+)/)[1];

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = form.closest('tr');
                        const probability = parseFloat(row.cells[2].textContent);
                        row.remove();

                        // Cập nhật tổng tỷ lệ trúng
                        const totalProbabilityElement = document.querySelector('.card-header h3');
                        const currentTotal = parseFloat(totalProbabilityElement.textContent.match(/Tổng: ([\d.]+)%/)[1]);
                        const newTotal = currentTotal - probability;
                        totalProbabilityElement.textContent = `Cấu hình tỷ lệ trúng (Tổng: ${newTotal.toFixed(4)}%)`;
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra, vui lòng thử lại');
                });
            });
        });
    </script>
@endpush