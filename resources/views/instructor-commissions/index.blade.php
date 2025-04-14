@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .stat-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            height: 150px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .card-title-custom {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .action-buttons .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0;
        }

        .coupon-table th {
            background-color: #f5f7fa;
        }

        .history-log-list li:hover {
            background-color: #f8f9fa;
            transition: 0.2s;
            border-radius: 4px;
            padding: 4px 8px;
        }
    </style>
@endpush
@php
    $title = 'Danh sách coupon';
@endphp
@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $subTitle }}</h4>
                        <div class="d-flex gap-2">

                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-filter-2-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown"
                                    style="min-width: 500px;">
                                    <div class="container">
                                        <div class="container">
                                            <div class="row">

                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="startDate" class="form-label">Ngày bắt đầu</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="start_date" id="startDate" data-filter
                                                            value="{{ request()->input('start_date') ?? '' }}">
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Ngày kết thúc</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="expire_date" id="endDate" data-filter
                                                            value="{{ request()->input('expire_date') ?? '' }}">
                                                    </div>
                                                </li>
                                            </div>
                                            <li class="mt-2">
                                                <button class="btn btn-sm btn-primary w-100" id="applyFilter">Áp
                                                    dụng</button>
                                            </li>

                                        </div>
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Tìm kiếm nâng cao -->
                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <form>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Mã giảm giá</label>
                                    <input class="form-control form-control-sm" name="code" type="text"
                                        value="{{ request()->input('code') ?? '' }}" placeholder="Nhập mã giảm giá..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tên mã giảm giá</label>
                                    <input class="form-control form-control-sm" name="name" type="text"
                                        value="{{ request()->input('name') ?? '' }}" placeholder="Nhập tên mã giảm giá..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Người tạo</label>
                                    <input class="form-control form-control-sm" name="user_id" type="text"
                                        value="{{ request()->input('user_id') ?? '' }}" placeholder="Nhập người tạo..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label for="statusItem" class="form-label">Loại giảm giá</label>
                                    <select class="form-select form-select-sm" name="discount_type" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn loại giảm giá</option>
                                        <option @selected(request()->input('discount_type') === 'percentage') value="percentage">Phần trăm</option>
                                        <option @selected(request()->input('discount_type') === 'fixed') value="fixed">Giảm trực tiếp</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <label for="statusItem" class="form-label">Trạng thái</label>
                                    <select class="form-select form-select-sm" name="status" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn trạng thái</option>
                                        <option @selected(request()->input('status') === '1') value="1">Hoạt động</option>
                                        <option @selected(request()->input('status') === '0') value="0">Không hoạt động</option>
                                    </select>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                                </div>

                            </div>
                        </form>
                    </div>

                    <div class="card-body" id="item_List">
                        <div class="listjs-table">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">

                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <form action="{{ route('admin.instructor-commissions.index') }}"
                                                method="get">
                                                <input type="text" name="search_full" class="form-control search"
                                                    placeholder="Search..." value="{{ old('search_full') }}">
                                                <i class="ri-search-line search-icon"></i>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="checkAll"
                                                        value="option">
                                                </div>
                                            </th>
                                            <th>STT</th>
                                            <th>Giảng viên</th>
                                            <th>Hoa hồng hiện tại (%)</th>
                                            <th>Cập nhật lúc</th>
                                            <th>Lịch sử thay đổi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
                                        @foreach ($instructorCommissions as $instructorCommission)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="itemID"
                                                            value="{{ $instructorCommission->id }}">
                                                    </div>
                                                </th>

                                                <td class="id">{{ $loop->iteration }}</td>

                                                <td>{{ $instructorCommission->instructor->name ?? 'Không tìm thấy' }}</td>
                                                <td>
                                                    <input type="number" step="1"
                                                        class="form-control input-rate"
                                                        data-id="{{ $instructorCommission->id }}"
                                                        value="{{ fmod($instructorCommission->rate * 100, 1) == 0
                                                            ? number_format($instructorCommission->rate * 100, 0)
                                                            : number_format($instructorCommission->rate * 100, 2) }}"
                                                        data-old="{{ fmod($instructorCommission->rate * 100, 1) == 0
                                                            ? number_format($instructorCommission->rate * 100, 0)
                                                            : number_format($instructorCommission->rate * 100, 2) }}"
                                                        data-name="{{ $instructorCommission->instructor->name }}""
                                                        style="width: 80px;" />
                                                </td>
                                                <td>{{ $instructorCommission->updated_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @php
                                                        $logs = json_decode($instructorCommission->rate_logs, true);
                                                    @endphp

                                                    <button type="button" class="btn btn-sm btn-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalLog_{{ $instructorCommission->id }}">
                                                        <i class="ri-eye-line"></i>
                                                    </button>

                                                    <div class="modal fade" id="modalLog_{{ $instructorCommission->id }}"
                                                        tabindex="-1"
                                                        aria-labelledby="modalLogLabel_{{ $instructorCommission->id }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-scrollable modal-md">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"
                                                                        id="modalLogLabel_{{ $instructorCommission->id }}">
                                                                        Lịch sử thay đổi hoa hồng
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Đóng"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    @if ($logs)
                                                                        <ul
                                                                            class="list-unstyled mb-0 small history-log-list">
                                                                            @foreach ($logs as $log)
                                                                                @php
                                                                                    $formattedRate =
                                                                                        fmod($log['rate'] * 100, 1) == 0
                                                                                            ? number_format(
                                                                                                $log['rate'] * 100,
                                                                                                0,
                                                                                            )
                                                                                            : number_format(
                                                                                                $log['rate'] * 100,
                                                                                                2,
                                                                                            );
                                                                                @endphp
                                                                                <li
                                                                                    class="mb-2 d-flex justify-content-between align-items-center border-bottom pb-1">
                                                                                    <span
                                                                                        class="text-danger fw-semibold fs-14">{{ $formattedRate }}%</span>
                                                                                    <span
                                                                                        class="badge bg-secondary text-white fs-13">{{ \Carbon\Carbon::parse($log['changed_at'])->format('d/m/Y H:i') }}</span>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @else
                                                                        <em>Không có lịch sử thay đổi</em>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                {{-- <td>
                                                    <div class="d-flex gap-2">
                                                        <div class="remove">
                                                            <a
                                                                href="{{ route('admin.instructorCommissions.edit', $instructorCommission->id) }}">
                                                                <button class="btn btn-sm btn-warning edit-item-btn">
                                                                    <span class="ri-edit-box-line"></span>
                                                                </button>
                                                            </a>
                                                        </div>
                                                        <div class="edit">
                                                            <a
                                                                href="{{ route('admin.instructorCommissions.show', $instructorCommission->id) }}">
                                                                <button class="btn btn-sm btn-info edit-item-btn">
                                                                    <span class="ri-eye-line"></span>
                                                                </button>
                                                            </a>
                                                        </div>
                                                        <div class="remove">
                                                            <a href="{{ route('admin.instructorCommissions.destroy', $instructorCommission->id) }}"
                                                                class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                                <span class="ri-delete-bin-7-line"></span>
                                                            </a>
                                                        </div>

                                                    </div>
                                                </td> --}}


                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $instructorCommissions->appends(request()->query())->links() }}
                        </div>
                    </div><!-- end card -->
                </div>
                <!-- end col -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->

    </div>
@endsection
@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.instructor-commissions.index') }}";
        var routeDeleteAll = "{{ route('admin.coupons.destroy', ':itemID') }}";

        function updateRange() {
            let rangeValue = document.getElementById("amountMinRange").value;
            document.getElementById("amountMin").textContent = rangeValue;
        }

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });

        $(document).ready(function() {
            $('.input-rate').on('blur', function() {
                var $this = $(this);
                var id = $this.data('id');
                var newValue = parseFloat($this.val());
                var oldValue = parseFloat($this.data('old'));
                var nameInstructor = $this.data('name');

                if (isNaN(newValue) || !Number.isInteger(newValue)) {
                    toastr.error('Hoa hồng mới phải là một số Nguyên!');
                    $this.val(oldValue);
                    return;
                }

                if(newValue <= 0 || newValue >= 101){
                    toastr.error('Hoa hồng mới phải là một số năm trong khoảng 0 đến 100');
                    $this.val(oldValue);
                    return;
                }

                if (newValue === oldValue) {
                    return;
                }

                var note =
                    `giảm hoa hồng của giảng viên <b style="color:red">${nameInstructor}</b> từ <b>${oldValue}%</b> xuống <b>${newValue}%</b>`;
                if (newValue > oldValue) {
                    note =
                        `tăng hoa hồng của giảng viên <b style="color:red">${nameInstructor}</b> từ <b>${oldValue}%</b> lên <b>${newValue}%</b>`;
                }

                Swal.fire({
                    title: `Xác nhận thay đổi hoa hồng`,
                    html: `Bạn có chắc chắn muốn ${note} không?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Không',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.instructor-commissions.update') }}",
                            type: 'PUT',
                            data: {
                                id: id,
                                rate: newValue / 100,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                toastr.success('Cập nhật thành công');
                                let re = Math.round(res.data.rate * 10000) / 100;
                                $this.data('old', re);

                                let updatedAt = new Date(res.data.updated_at);
                                let day = updatedAt.getDate().toString().padStart(2,
                                    '0');
                                let month = (updatedAt.getMonth() + 1).toString()
                                    .padStart(2, '0');
                                let year = updatedAt.getFullYear();
                                let hours = updatedAt.getHours().toString().padStart(2,
                                    '0');
                                let minutes = updatedAt.getMinutes().toString()
                                    .padStart(2, '0');
                                let formattedDate =
                                    `${day}/${month}/${year} ${hours}:${minutes}`;

                                let logList = $(`#modalLog_${id} .history-log-list`);
                                let newLog = $(`
                                <li class="mb-2 d-flex justify-content-between align-items-center border-bottom pb-1">
                                    <span class="text-danger fw-semibold fs-14">${re}%</span>
                                    <span class="badge bg-secondary text-white fs-13">${formattedDate}</span>
                                </li>
                            `);
                                logList.append(newLog);
                            },
                            error: function() {
                                toastr.error('Cập nhật thất bại!');
                                $this.val(oldValue);
                            }
                        });
                    } else {
                        $this.val(oldValue);
                    }
                });
            });
        });
    </script>

    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/restore-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/resetFilter.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
