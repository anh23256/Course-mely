@extends('layouts.app')

@push('page-css')
    <style>
        .card {
            margin-bottom: 20px;
        }

        .table {
            font-size: 14px;
        }

        .form-control-sm {
            max-width: 100px;
        }

        canvas {
            max-width: 100%;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .row-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .col-70 {
            flex: 0 0 70%;
            max-width: 70%;
            padding: 0 10px;
        }

        .col-30 {
            flex: 0 0 30%;
            max-width: 30%;
            padding: 0 10px;
        }

        .filter-form {
            margin-bottom: 15px;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #405189;
            margin-bottom: 8px;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
@endpush

@php
    $title = 'Quản lý Vòng Quay May Mắn';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.transactions.index') }}">{{ $title ?? '' }}</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row cursor-pointer">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">{{ $totalSpins ?? 0 }}</div>
                    <div class="stats-label">Tổng lượt quay</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">{{ $totalGifts ?? 0 }}</div>
                    <div class="stats-label">Tổng quà tặng</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">{{ $totalWinners ?? 0 }}</div>
                    <div class="stats-label">Người trúng thưởng</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">{{ $totalProbability }}%</div>
                    <div class="stats-label">Tỷ lệ trúng</div>
                </div>
            </div>
        </div>

        <!-- Cấu hình tỷ lệ trúng và Danh sách quà hiện vật -->
        <div class="row row-flex">
            <!-- Cấu hình tỷ lệ trúng -->
            <div class="col-70">
                <div class="card">
                    <div class="card-header">
                        <h3>Cấu hình tỷ lệ trúng (Tổng: {{ $totalProbability }}%)</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <!-- Nút thêm ô quà trong vòng quay -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addSpinConfigModal">
                                Thêm ô quà trong vòng quay
                            </button>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Loại</th>
                                    <th>Tên</th>
                                    <th>Tỷ lệ (%)</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($spinConfigs as $config)
                                    <tr data-id="{{ $config->id }}" data-type="spin_config">
                                        <td>{{ $config->type }}</td>
                                        <td>{{ $config->name }}</td>
                                        <td>{{ $config->probability }}</td>
                                        <td>
                                            <form action="{{ route('admin.spins.spin-config.update', $config->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="probability" value="{{ $config->probability }}"
                                                    step="0.01" min="0" max="100"
                                                    class="form-control form-control-sm d-inline" required>
                                                <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                            </form>
                                            <form action="{{ route('admin.spins.deleteSpinConfig', $config->id) }}"
                                                method="POST" class="d-inline delete-spin-config-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm delete-spin-config"
                                                    onclick="return confirm('Bạn chắc chắn muốn xóa ô quà này?')">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach ($gifts as $gift)
                                    <tr data-id="{{ $gift->id }}" data-type="gift">
                                        <td>Gift</td>
                                        <td>{{ $gift->name }}</td>
                                        <td>{{ $gift->probability }}</td>
                                        <td>
                                            <form action="{{ route('admin.spins.gift.update', $gift->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="probability" value="{{ $gift->probability }}"
                                                    step="0.01" min="0" max="100"
                                                    class="form-control form-control-sm d-inline" required>
                                                <input type="hidden" name="name" value="{{ $gift->name }}">
                                                <input type="hidden" name="stock" value="{{ $gift->stock }}">
                                                <input type="hidden" name="description" value="{{ $gift->description }}">
                                                <input type="hidden" name="image_url" value="{{ $gift->image_url }}">
                                                <input type="hidden" name="is_active" value="{{ $gift->is_active }}">
                                                <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                            </form>
                                            <form action="{{ route('admin.spins.gift.delete', $gift->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Danh sách quà hiện vật -->
            <div class="col-30">
                <div class="card">
                    <div class="card-header">
                        <h3>Danh sách quà hiện vật</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <!-- Nút thêm quà hiện vật -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addGiftModal">
                                Thêm quà hiện vật
                            </button>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tên quà</th>
                                    <th>Số lượng</th>
                                    <th>Hình ảnh</th>
                                    <th>Kích hoạt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($giftsAll as $gift)
                                    <tr>
                                        <td>{{ $gift->name }}</td>
                                        <td>{{ $gift->stock }}</td>
                                        <td>
                                            @if ($gift->image_url)
                                                <img src="{{ $gift->image_url }}" alt="{{ $gift->name }}"
                                                    style="max-width: 50px;">
                                            @else
                                                Không có ảnh
                                            @endif
                                        </td>
                                        <td>
                                            <form
                                                action="{{ route('admin.spins.toggle-selection', ['type' => 'gift', 'id' => $gift->id]) }}"
                                                method="POST" class="toggle-selection-form">
                                                @csrf
                                                <input type="checkbox" name="is_selected" onchange="this.form.submit()"
                                                    {{ $gift->is_selected ? 'checked' : '' }}>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal thêm quà hiện vật -->
        <div class="modal fade" id="addGiftModal" tabindex="-1" aria-labelledby="addGiftModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGiftModalLabel">Thêm quà hiện vật</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.spins.gift.store') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-12">
                                <label for="name" class="form-label">Tên quà</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                                    placeholder="Tên quà" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Số lượng</label>
                                <input type="number" name="stock" value="{{ old('stock') }}" class="form-control"
                                    placeholder="Số lượng" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="probability" class="form-label">Tỷ lệ (%)</label>
                                <input type="number" name="probability" value="{{ old('probability') }}"
                                    class="form-control" placeholder="Tỷ lệ (%)" step="0.01" min="0"
                                    max="100" required>
                            </div>
                            <div class="col-md-12">
                                <label for="image_url" class="form-label">URL ảnh</label>
                                <input type="url" name="image_url" value="{{ old('image_url') }}"
                                    class="form-control" placeholder="URL ảnh">
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea name="description" class="form-control" placeholder="Mô tả">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary w-100">Thêm quà</button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal thêm ô quà trong vòng quay -->
        <div class="modal fade" id="addSpinConfigModal" tabindex="-1" aria-labelledby="addSpinConfigModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSpinConfigModalLabel">Thêm ô quà trong vòng quay</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.spins.spin-config.store') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-12">
                                <label for="type" class="form-label">Loại</label>
                                <input type="text" name="type" value="{{ old('type') }}" class="form-control"
                                    placeholder="Loại (ví dụ: coupon, message)" required>
                            </div>
                            <div class="col-md-12">
                                <label for="name" class="form-label">Tên</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                                    placeholder="Tên ô quà" required>
                            </div>
                            <div class="col-md-12">
                                <label for="probability" class="form-label">Tỷ lệ (%)</label>
                                <input type="number" name="probability" value="{{ old('probability') }}"
                                    class="form-control" placeholder="Tỷ lệ (%)" step="0.01" min="0"
                                    max="100" required>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary w-100">Thêm ô quà</button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng số lượt quay và Danh sách người trúng -->
        <div class="row row-flex">
            <!-- Tổng số lượt quay -->
            <div class="col-70">
                <div class="card">
                    <div class="card-header">
                        <h3>Tổng số lượt quay</h3>
                    </div>
                    <div class="card-body">
                        <!-- Bộ lọc -->
                        <form method="GET" action="{{ route('admin.spins.index') }}" class="filter-form row g-3">
                            <div class="col-md-4">
                                <label for="filter_type" class="form-label">Loại lọc</label>
                                <select name="filter_type" id="filter_type" class="form-control"
                                    onchange="this.form.submit()">
                                    <option value="day"
                                        {{ request('filter_type', 'day') === 'day' ? 'selected' : '' }}>Theo ngày</option>
                                    <option value="month" {{ request('filter_type') === 'month' ? 'selected' : '' }}>Theo
                                        tháng</option>
                                    <option value="year" {{ request('filter_type') === 'year' ? 'selected' : '' }}>Theo
                                        năm</option>
                                </select>
                            </div>
                            @if (request('filter_type', 'day') === 'day')
                                <div class="col-md-4">
                                    <label for="filter_date" class="form-label">Chọn ngày</label>
                                    <input type="date" name="filter_date"
                                        value="{{ request('filter_date', now()->format('Y-m-d')) }}" class="form-control"
                                        onchange="this.form.submit()">
                                </div>
                            @elseif (request('filter_type') === 'month')
                                <div class="col-md-4">
                                    <label for="filter_month" class="form-label">Chọn tháng</label>
                                    <input type="month" name="filter_month"
                                        value="{{ request('filter_month', now()->format('Y-m')) }}" class="form-control"
                                        onchange="this.form.submit()">
                                </div>
                            @elseif (request('filter_type') === 'year')
                                <div class="col-md-4">
                                    <label for="filter_year" class="form-label">Chọn năm</label>
                                    <select name="filter_year" class="form-control" onchange="this.form.submit()">
                                        @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                            <option value="{{ $year }}"
                                                {{ request('filter_year', now()->year) == $year ? 'selected' : '' }}>
                                                {{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            @endif
                        </form>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active">
                                <canvas id="spinChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách người trúng quà hiện vật -->
            <div class="col-30">
                <div class="card">
                    <div class="card-header">
                        <h3>Danh sách người trúng quà hiện vật</h3>
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

                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.style.overflow = 'auto';
                document.body.style.paddingRight = '0px';
                console.log('Modal closed successfully');
            } catch (error) {
                console.error('Error while closing modal:', error);
            }
        }

        // Biểu đồ tổng số lượt quay
        const filterType = "{{ request('filter_type', 'day') }}";
        let labels = [];
        let data = [];

        if (filterType === 'day') {
            labels = [
                @foreach ($spinStatsDay as $stat)
                    "{{ $stat->date }}",
                @endforeach
            ];
            data = [
                @foreach ($spinStatsDay as $stat)
                    {{ $stat->spins }},
                @endforeach
            ];
        } else if (filterType === 'month') {
            labels = [
                @foreach ($spinStatsMonth as $stat)
                    "{{ $stat->month }}/{{ $stat->year }}",
                @endforeach
            ];
            data = [
                @foreach ($spinStatsMonth as $stat)
                    {{ $stat->spins }},
                @endforeach
            ];
        } else if (filterType === 'year') {
            labels = [
                @foreach ($spinStatsYear as $stat)
                    "{{ $stat->year }}",
                @endforeach
            ];
            data = [
                @foreach ($spinStatsYear as $stat)
                    {{ $stat->spins }},
                @endforeach
            ];
        }

        const spinChart = new Chart(document.getElementById('spinChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số lượt quay',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Xử lý toggle-selection (kích hoạt quà hiện vật cho vòng quay)
        // document.querySelectorAll('.toggle-selection-form input[type="checkbox"]').forEach(checkbox => {
        //     checkbox.addEventListener('change', function () {
        //         const form = this.closest('form');
        //         const type = form.action.match(/toggle-selection\/(\w+)\//)[1];
        //         const id = form.action.match(/toggle-selection\/\w+\/(\d+)/)[1];
        //         const row = form.closest('tr');
        //         const name = row.cells[0].textContent;
        //         const probability = parseFloat(row.cells[2].textContent);

        //         fetch(form.action, {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/json',
        //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        //             },
        //             body: JSON.stringify({})
        //         })
        //             .then(response => response.json())
        //             .then(data => {
        //                 if (data.success) {
        //                     const tbody = document.querySelector('.table-striped tbody');
        //                     if (this.checked) {
        //                         // Thêm vào bảng "Cấu hình tỷ lệ trúng"
        //                         const newRow = document.createElement('tr');
        //                         newRow.setAttribute('data-id', id);
        //                         newRow.setAttribute('data-type', type);
        //                         newRow.innerHTML = `
    //                             <td>${type.charAt(0).toUpperCase() + type.slice(1)}</td>
    //                             <td>${name}</td>
    //                             <td>${probability}</td>
    //                             <td>
    //                                 <form action="/admin/spins/gifts/${id}" method="POST" class="d-inline">
    //                                     <input type="hidden" name="_method" value="PUT">
    //                                     <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
    //                                     <input type="number" name="probability" value="${probability}" step="0.01" min="0" max="100" class="form-control form-control-sm d-inline" required>
    //                                     <input type="hidden" name="name" value="${name}">
    //                                     <input type="hidden" name="stock" value="0">
    //                                     <input type="hidden" name="description" value="">
    //                                     <input type="hidden" name="image_url" value="">
    //                                     <input type="hidden" name="is_active" value="1">
    //                                     <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
    //                                 </form>
    //                                 <form action="/admin/spins/gifts/${id}" method="POST" class="d-inline">
    //                                     <input type="hidden" name="_method" value="DELETE">
    //                                     <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
    //                                     <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</button>
    //                                 </form>
    //                             </td>
    //                             <td>
    //                                 <form action="/admin/spins/spin/toggle-selection/${type}/${id}" method="POST" class="toggle-selection-form">
    //                                     <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
    //                                     <input type="checkbox" name="is_selected" onchange="this.form.submit()" checked>
    //                                 </form>
    //                             </td>
    //                         `;
        //                         tbody.appendChild(newRow);

        //                         // Cập nhật tổng tỷ lệ trúng
        //                         const totalProbabilityElement = document.querySelector('.card-header h3');
        //                         const currentTotal = parseFloat(totalProbabilityElement.textContent.match(/Tổng: ([\d.]+)%/)[1]);
        //                         const newTotal = currentTotal + probability;
        //                         totalProbabilityElement.textContent = `Cấu hình tỷ lệ trúng (Tổng: ${newTotal.toFixed(4)}%)`;

        //                         showToast('succes','Thêm quà hiện vật vào vòng quay thành công');
        //                     } else {
        //                         // Xóa khỏi bảng "Cấu hình tỷ lệ trúng"
        //                         const rows = tbody.querySelectorAll('tr');
        //                         rows.forEach(row => {
        //                             if (row.getAttribute('data-id') === id && row.getAttribute('data-type') === type) {
        //                                 row.remove();
        //                             }
        //                         });

        //                         // Cập nhật tổng tỷ lệ trúng
        //                         const totalProbabilityElement = document.querySelector('.card-header h3');
        //                         const currentTotal = parseFloat(totalProbabilityElement.textContent.match(/Tổng: ([\d.]+)%/)[1]);
        //                         const newTotal = currentTotal - probability;
        //                         totalProbabilityElement.textContent = `Cấu hình tỷ lệ trúng (Tổng: ${newTotal.toFixed(4)}%)`;
        //                     }
        //                 } else {
        //                     showToast('error','Có lỗi xảy ra');
        //                 }
        //             })
        //             .catch(error => {
        //                 console.error('Error:', error);
        //                 alert('Có lỗi xảy ra, vui lòng thử lại');
        //             });
        //     });
        // });

        // Xử lý xóa ô quà trong vòng quay
        document.querySelectorAll('.delete-spin-config').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.delete-spin-config-form');
                const row = this.closest('tr');
                const probability = parseFloat(row.cells[2].textContent);
                const id = row.getAttribute('data-id');

                fetch(form.action, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', 'Xóa ô quà khỏi vòng quay thành công');
                            // Xóa hàng khỏi bảng
                            row.remove();

                            // Cập nhật tổng tỷ lệ trúng
                            const totalProbabilityElement = document.querySelector('.card-header h3');
                            const currentTotal = parseFloat(totalProbabilityElement.textContent.match(
                                /Tổng: ([\d.]+)%/)[1]);
                            const newTotal = currentTotal - probability;
                            totalProbabilityElement.textContent =
                                `Cấu hình tỷ lệ trúng (Tổng: ${newTotal.toFixed(4)}%)`;

                            // Hiển thị toast
                            showToast();

                        } else {
                            showToast('error', 'Có lỗi xảy ra, vui lòng thử lại');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'Có lỗi xảy ra, vui lòng thử lại');
                    });
            });
        });
    </script>
@endpush
