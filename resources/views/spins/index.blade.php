@extends('layouts.app')

@push('page-css')
    <style>
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .card-header {
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            padding: 20px;
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

        .row-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .col-70 {
            flex: 0 0 65%;
            max-width: 65%;
            padding: 0 10px;
        }

        .col-30 {
            flex: 0 0 35%;
            max-width: 35%;
            padding: 0 10px;
        }

        .filter-form {
            margin-bottom: 15px;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .list-group-item {
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .fs-4 {
            font-size: 1.5rem;
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
            <!-- Modal cảnh báo không đủ phần thưởng hoặc quà hiện vật -->
            @if ($showConfigWarning)
            <div class="modal fade" id="configWarningModal" tabindex="-1" aria-labelledby="configWarningModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="configWarningModalLabel">Cảnh báo: Cấu hình không hợp lệ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if (!$hasEnoughSpinConfigTypes)
                                <p>Vòng quay chưa đủ 3 loại phần thưởng (no_reward, coupon, spin). Hiện tại thiếu:</p>
                                <ul>
                                    @foreach ($missingTypes as $type)
                                        <li>{{ ucfirst($type) }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if (!$hasEnoughGifts)
                                <p>Vòng quay chưa đủ 2 quà hiện vật. Hiện tại chỉ có {{ $currentSelectedGiftsCount }}
                                    quà hiện vật được chọn, cần thêm {{ $requiredGifts - $currentSelectedGiftsCount }}
                                    quà nữa.</p>
                            @endif
                            <p>Vui lòng hoàn thiện cấu hình trước khi tiếp tục.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
                        <table class="table align-middle table-nowrap"
                            style="border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                            <thead class="table-light">
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
                                                <button type="button" class="sweet-confirm btn btn-danger btn-sm delete-spin-config">
                                                    <span class="ri-delete-bin-7-line"></span>
                                                </button>
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
                                data-bs-target="#addGiftModal" {{ $showConfigWarning ? 'disabled' : '' }}>
                                Thêm quà hiện vật
                            </button>
                        </div>
                        <table class="table align-middle"
                            style="border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên quà</th>
                                    <th>Số lượng</th>
                                    <th>Hình ảnh</th>
                                    <th>Kích hoạt</th>
                                    <th>Hành động</th>
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
                                                <div class="form-check form-switch form-switch-warning">
                                                    <input class="form-check-input popular-course-toggle" role="switch"
                                                        type="checkbox" name="is_selected" onchange="this.form.submit()"
                                                        {{ $gift->is_selected ? 'checked' : '' }}>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.spins.deleteSpinConfig', $config->id) }}"
                                                class="sweet-confirm btn btn-sm btn-danger delete-gift-form"
                                                data-id="{{ $config->id }}">
                                                 <span class="ri-delete-bin-7-line"></span>
                                             </a>
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
                            <div class="col-md-12">
                                <label for="stock" class="form-label">Số lượng</label>
                                <input type="number" name="stock" value="{{ old('stock') }}" class="form-control"
                                    placeholder="Số lượng" min="0" required>
                            </div>
                            <div class="col-md-12">
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
                                <select name="type" value="{{ old('type') }}" class="form-select">
                                    <option value="">Chọn loại quà</option>
                                    <option value="no_reward">Không trúng</option>
                                    <option value="coupon">Mã giảm giá</option>
                                    <option value="spin">Thêm 1 lượt quay</option>
                                </select>
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
            <!-- Tổng số lượt quay -->
            <div class="col-70">
                <div class="card">
                    <div class="card-header text-white">
                        <h3 class="mb-0">Thống kê tổng quan</h3>
                    </div>
                    <div class="card-body">
                        <!-- Bộ lọc -->
                        <div class="row">
                            <!-- Thống kê tổng quan -->
                            <div class="col-md-4 mb-4">
                                <div class="d-flex flex-column"> <!-- Thay đổi từ 'row' thành 'd-flex flex-column' -->
                                    <div class="mb-3">
                                        <!-- Thay 'col-md-3' bằng 'mb-3' để thêm khoảng cách giữa các thẻ -->
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tổng số lượt quay</h6>
                                                <p class="card-text fs-4">{{ $totalSpins }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tổng số lượt trúng</h6>
                                                <p class="card-text fs-4">{{ $totalWinners }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tỷ lệ trúng thưởng</h6>
                                                <p class="card-text fs-4">{{ number_format($winRate, 2) }}%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Biểu đồ phân bố quà trúng -->
                            <div class="col-md-8 mb-4">
                                <h5 class="text-muted">Phân bố quà đã trúng</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <canvas id="winDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Biểu đồ -->
                        <div class="row">
                            <!-- Biểu đồ số lượt quay -->
                            <div class="mb-4">
                                <h5 class="text-muted">Bộ lọc</h5>
                                <form method="GET" action="{{ route('admin.spins.index') }}"
                                    class="filter-form row g-3">
                                    <div class="col-md-3">
                                        <label for="filter_type" class="form-label">Loại lọc</label>
                                        <select name="filter_type" id="filter_type" class="form-control"
                                            onchange="this.form.submit()">
                                            <option value="day"
                                                {{ request('filter_type', 'day') === 'day' ? 'selected' : '' }}>Theo ngày
                                            </option>
                                            <option value="month"
                                                {{ request('filter_type') === 'month' ? 'selected' : '' }}>Theo tháng
                                            </option>
                                            <option value="year"
                                                {{ request('filter_type') === 'year' ? 'selected' : '' }}>Theo năm</option>
                                        </select>
                                    </div>
                                    @if (request('filter_type', 'day') === 'day')
                                        <div class="col-md-3">
                                            <label for="filter_date" class="form-label">Chọn ngày</label>
                                            <input type="date" name="filter_date"
                                                value="{{ request('filter_date', now()->format('Y-m-d')) }}"
                                                class="form-control" onchange="this.form.submit()">
                                        </div>
                                    @elseif (request('filter_type') === 'month')
                                        <div class="col-md-3">
                                            <label for="filter_month" class="form-label">Chọn tháng</label>
                                            <input type="month" name="filter_month"
                                                value="{{ request('filter_month', now()->format('Y-m')) }}"
                                                class="form-control" onchange="this.form.submit()">
                                        </div>
                                    @elseif (request('filter_type') === 'year')
                                        <div class="col-md-3">
                                            <label for="filter_year" class="form-label">Chọn năm</label>
                                            <select name="filter_year" class="form-control"
                                                onchange="this.form.submit()">
                                                @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                                    <option value="{{ $year }}"
                                                        {{ request('filter_year', now()->year) == $year ? 'selected' : '' }}>
                                                        {{ $year }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    @endif
                                </form>
                            </div>
                            <div class="">
                                <h5 class="text-muted">Số lượt quay theo thời gian</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <canvas id="spinChart"></canvas>
                                    </div>
                                </div>
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
        document.addEventListener('DOMContentLoaded', function () {
            @if ($showConfigWarning)
                const configWarningModal = new bootstrap.Modal(document.getElementById('configWarningModal'), {
                    backdrop: 'static', // Không cho phép đóng modal bằng cách nhấp ra ngoài
                    keyboard: false // Không cho phép đóng modal bằng phím Esc
                });
                configWarningModal.show();
            @endif
        });
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

        // Biểu đồ phân bố quà trúng
        const winDistributionLabels = [
            @foreach ($groupedWinners as $type => $count)
                "{{ ucfirst($type) }}",
            @endforeach
        ];
        const winDistributionData = [
            @foreach ($groupedWinners as $type => $count)
                {{ $count }},
            @endforeach
        ];
        const winDistributionChart = new Chart(document.getElementById('winDistributionChart'), {
            type: 'pie',
            data: {
                labels: winDistributionLabels.map(label => `${label}`),
                datasets: [{
                    label: 'Phân bố quà trúng',
                    data: winDistributionData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    }
                }
            }
        });


        $('.delete-gift-form').on('submit', function(e) {
                e.preventDefault(); // Ngăn form submit mặc định

                const form = $(this);
                const url = form.attr('action'); // Lấy URL từ action của form
                const row = form.closest('tr'); // Lấy hàng trong bảng

                // Hiển thị SweetAlert để xác nhận
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: 'Bạn có muốn xóa quà hiện vật này không?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Gửi yêu cầu AJAX
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Hiển thị thông báo thành công
                                    showToast('success', response.message || 'Xóa quà hiện vật thành công!');

                                    // Xóa hàng khỏi bảng
                                    row.remove();
                                } else {
                                    // Hiển thị thông báo lỗi
                                    showToast('error', response.message || 'Có lỗi xảy ra, vui lòng thử lại');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error:', error);
                                showToast('error', 'Có lỗi xảy ra, vui lòng thử lại');
                            }
                        });
                    }
                });
            });

            // Xử lý xóa phần thưởng trong vòng quay (delete-spin-config)
            $('.delete-spin-config').on('click', function(e) {
                e.preventDefault(); // Ngăn hành vi mặc định của thẻ <a>

                const button = $(this);
                const url = button.attr('href'); // Lấy URL từ href
                const row = button.closest('tr'); // Lấy hàng trong bảng
                const probability = parseFloat(row.find('td:eq(2)').text()); // Lấy tỷ lệ trúng từ cột thứ 3
                const id = button.data('id'); // Lấy ID từ data-id

                // Hiển thị SweetAlert để xác nhận
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: 'Bạn có muốn xóa ô quà này khỏi vòng quay không?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Gửi yêu cầu AJAX
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Hiển thị thông báo thành công
                                    showToast('success', response.message || 'Xóa ô quà khỏi vòng quay thành công!');

                                    // Xóa hàng khỏi bảng
                                    row.remove();

                                    // Cập nhật tổng tỷ lệ trúng
                                    const totalProbabilityElement = $('.card-header h3');
                                    const currentTotal = parseFloat(totalProbabilityElement.text().match(/Tổng: ([\d.]+)%/)[1]);
                                    const newTotal = currentTotal - probability;
                                    totalProbabilityElement.text(`Cấu hình tỷ lệ trúng (Tổng: ${newTotal.toFixed(4)}%)`);
                                } else {
                                    // Hiển thị thông báo lỗi
                                    showToast('error', response.message || 'Có lỗi xảy ra, vui lòng thử lại');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error:', error);
                                showToast('error', 'Có lỗi xảy ra, vui lòng thử lại');
                            }
                        });
                    }
                });
            });
    </script>
@endpush
