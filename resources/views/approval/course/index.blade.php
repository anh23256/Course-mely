@extends('layouts.app')

@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .hover-effect {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a>{{ $title ?? '' }}</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- social-customer -->
        <div class="row cursor-pointer">
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stats-card total-card">
                    <div class="card-body text-center">
                        <div class="stat-icon text-primary">
                            <i class="bx bx-list-check text-primary fs-1"></i>
                        </div>
                        <h5 class="card-title mt-2">Tổng số yêu cầu</h5>
                        <p class="card-text fw-bold">{{ $approvalCount->total_approval ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stats-card approved-card">
                    <div class="card-body text-center">
                        <div class="stat-icon text-success">
                            <i class="bx bx-check-circle text-success fs-1"></i>
                        </div>
                        <h5 class="card-title mt-2">Yêu cầu đã kiểm duyệt</h5>
                        <p class="card-text fw-bold text-success">{{ $approvalCount->approved_approval ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stats-card pending-card">
                    <div class="card-body text-center">
                        <div class="stat-icon text-warning">
                            <i class="bx bx-time-five text-warning fs-1"></i>
                        </div>
                        <h5 class="card-title mt-2">Yêu cầu chờ xử lý</h5>
                        <p class="card-text fw-bold text-warning">{{ $approvalCount->pending_approval ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stats-card rejected-card">
                    <div class="card-body text-center">
                        <div class="stat-icon text-danger">
                            <i class="bx bx-x-circle text-danger fs-1"></i>
                        </div>
                        <h5 class="card-title mt-2">Yêu cầu bị từ chối</h5>
                        <p class="card-text fw-bold text-danger">{{ $approvalCount->rejected_approval ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End social-customer -->

    <!-- List-customer -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ $subTitle ?? '' }}</h4>
                    <div class="d-flex gap-2">
                        <div class="col-sm">
                            <div class="d-flex justify-content-sm-end">
                                <div class="search-box ms-2">
                                    <input type="text" name="search_full" class="form-control search h-75"
                                        placeholder="Tìm kiếm..." data-search>
                                    <button id="search-full" class="h-75 ri-search-line search-icon m-0 p-0 border-0"
                                        style="background: none;"></button>
                                </div>
                            </div>
                        </div>
                        <a href="" class="btn btn-sm btn-success h-75">Export dữ liệu</a>
                        <button class="btn btn-sm btn-primary h-75" id="toggleAdvancedSearch">
                            Tìm kiếm nâng cao
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary h-75" type="button" id="filterDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-filter-2-line"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown"
                                style="min-width: 500px;">
                                <form>
                                    <div class="container">
                                        <li>
                                            <label for="amountRange" class="form-label">Số tiền</label>

                                            <div class="d-flex justify-content-between">
                                                <span id="amountMin">10,000 VND</span>
                                                <span id="amountMax">99,999,999 VND</span>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <input type="range" class="form-range w-50" id="amountMinRange"
                                                    name="amount_min" min="10000" max="49990000" step="10000"
                                                    value="{{ request()->input('amount_min') ?? 10000 }}"
                                                    oninput="updateRange()" data-filter>
                                                <input type="range" class="form-range w-50" id="amountMaxRange"
                                                    name="amount_max" min="50000000" max="99990000" step="10000"
                                                    value="{{ request()->input('amount_max') ?? 99990000 }}"
                                                    oninput="updateRange()" data-filter>
                                            </div>
                                        </li>
                                        <div class="row">
                                            <li class="col-6">
                                                <div class="mb-2">
                                                    <label for="request_start_date" class="form-label">Ngày bắt đầu
                                                        gửi yêu cầu</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="request_start_date" id="request_start_date" data-filter
                                                        value="{{ request()->input('request_start_date') ?? '' }}">
                                                </div>
                                            </li>
                                            <li class="col-6">
                                                <div class="mb-2">
                                                    <label for="request_end_date" class="form-label">Ngày kết thúc
                                                        gửi yêu cầu</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="request_end_date" id="request_end_date" data-filter
                                                        value="{{ request()->input('request_end_date') ?? '' }}">
                                                </div>
                                            </li>
                                        </div>
                                        <li class="mt-2 d-flex gap-1">
                                            <button class="btn btn-sm btn-success flex-grow-1" type="reset"
                                                id="resetFilter">Reset
                                            </button>
                                            <button class="btn btn-sm btn-primary flex-grow-1" id="applyFilter">Áp
                                                dụng
                                            </button>
                                        </li>
                                    </div>
                                </form>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Tìm kiếm nâng cao -->
                <div id="advancedSearch" class="card-header" style="display:none;">
                    <form>
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Tên khóa học</label>
                                <input class="form-control form-control-sm" name="course_name_approved" type="text"
                                    placeholder="Nhập tên khóa học..."
                                    value="{{ request()->input('course_name_approved') ?? '' }}" data-advanced-filter>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tên giảng viên</label>
                                <input class="form-control form-control-sm" name="name_instructor" type="text"
                                    placeholder="Nhập tên giảng viên..."
                                    value="{{ request()->input('name_instructor') ?? '' }}" data-advanced-filter>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email giảng viên</label>
                                <input class="form-control form-control-sm" name="instructor_email" type="text"
                                    placeholder="Nhập email giảng viên..."
                                    value="{{ request()->input('instructor_email') ?? '' }}" data-advanced-filter>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tên người kiểm duyệt</label>
                                <input class="form-control form-control-sm" name="approver_name_approved" type="text"
                                    placeholder="Nhập tên người kiểm duyệt..."
                                    value="{{ request()->input('approver_name_approved') ?? '' }}" data-advanced-filter>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="statusItem" class="form-label">Trạng thái kiểm duyệt</label>
                                <select class="form-select form-select-sm" name="status" id="statusItem"
                                    data-advanced-filter>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="approved" @selected(request()->input('status') === 'approved')>Đã
                                        kiểm duyệt
                                    </option>
                                    <option value="modify_request" @selected(request()->input('status') === 'modify_request')>Sửa đổi nội dung
                                    </option>
                                    <option value="pending" @selected(request()->input('status') === 'pending')>Chờ
                                        xử lý
                                    </option>
                                    <option value="rejected" @selected(request()->input('status') === 'rejected')>Từ
                                        chối
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="mb-2">
                                    <label for="approval_start_date" class="form-label">Ngày bắt đầu
                                        kiểm duyệt</label>
                                    <input type="date" class="form-control form-control-sm" name="approval_start_date"
                                        id="approval_start_date" data-advanced-filter
                                        value="{{ request()->input('approval_start_date') ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="mb-2">
                                    <label for="approval_end_date" class="form-label">Ngày kết thúc
                                        kiểm duyệt</label>
                                    <input type="date" class="form-control form-control-sm" name="approval_end_date"
                                        id="approval_end_date" data-advanced-filter
                                        value="{{ request()->input('approval_end_date') ?? '' }}">
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- end card header -->
                <div class="card-body" id="item_List">
                    <div class="listjs-table" id="customerList">
                        <div class="table-responsive table-card mt-3 mb-1">
                            <table class="table align-middle table-nowrap" id="customerTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên khoá học</th>
                                        <th>Giảng viên</th>
                                        <th>Hình ảnh</th>
                                        <th>Giá</th>
                                        <th>Người kiểm duyệt</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày gửi yêu cầu</th>
                                        <th>Ngày kiểm duyệt</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach ($approvals as $approval)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td
                                                style="max-width: 200px; white-space: normal; word-wrap: break-word; overflow-wrap: break-word;">
                                                {{ \Illuminate\Support\Str::limit($approval->course->name ?? 'Không có tên', 60) }}
                                            <td>
                                                <span
                                                    class="text-danger font-weight-bold">{{ $approval->course->user->name ?? '' }}</span>
                                                <br>
                                                <small
                                                    class="text-muted">{{ $approval->course->user->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <img style="height: 80px" src="{{ $approval->course->thumbnail }}"
                                                    alt="" class="w-100 object-fit-cover">
                                            </td>
                                            <td>{{ $approval->course->price > 0 ? number_format($approval->course->price) : 'Miễn phí' }}
                                            </td>
                                            <td>
                                                @if ($approval->status === 'pending')
                                                    <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Chưa xử lý</span>
                                                @elseif (!empty($approval->approver->name))
                                                    <span class="badge bg-primary text-white"><i class="bx bx-user"></i> {{ $approval->approver->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary text-white"><i class="bx bx-cog"></i> Hệ thống đã xử lý</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($approval->status == 'pending')
                                                    <span class="badge bg-warning text-dark"><i
                                                            class="bx bx-time-five"></i> Chờ xử lý</span>
                                                @elseif($approval->status == 'approved')
                                                    <span class="badge bg-success text-white"><i
                                                            class="bx bx-check-circle"></i> Đã kiểm duyệt</span>
                                                @elseif($approval->status == 'modify_request')
                                                    <span class="badge bg-info text-white"><i class="bx bx-edit"></i> Sửa
                                                        đổi nội dung</span>
                                                @else
                                                    <span class="badge bg-danger text-white"><i
                                                            class="bx bx-x-circle"></i> Từ chối</span>
                                                @endif
                                            </td>
                                            <td>
                                                {!! !empty($approval->request_date)
                                                    ? '<span class="badge bg-info text-white"><i class="bx bx-calendar"></i> ' .
                                                        \Carbon\Carbon::parse($approval->request_date)->format('d/m/Y') .
                                                        '</span>'
                                                    : '<span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Chưa kiểm duyệt</span>' !!}
                                            </td>
                                            <td>
                                                @if ($approval->approved_at)
                                                    <span class="badge bg-success text-white"><i
                                                            class="bx bx-calendar-check"></i>
                                                        {{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y') }}</span>
                                                @elseif($approval->rejected_at)
                                                    <span class="badge bg-danger text-white"><i
                                                            class="bx bx-calendar-x"></i>
                                                        {{ \Carbon\Carbon::parse($approval->rejected_at)->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="badge bg-warning text-dark"><i class="bx bx-time"></i>
                                                        Chưa kiểm duyệt</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.approvals.courses.show', $approval->id) }}">
                                                    <button class="btn btn-sm btn-info edit-item-btn">
                                                        <span class="ri-eye-line"></span>
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            {{ $approvals->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
                <!-- end card -->
            </div>
        </div>
        <!-- end col -->
    </div>
    <!-- end List-customer -->
    </div>
@endsection

@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.approvals.courses.index') }}";

        function updateRange() {
            var minValue = $('#amountMinRange').val();
            var maxValue = $('#amountMaxRange').val();
            document.getElementById('amountMin').textContent = formatCurrency(minValue) + ' VND';
            document.getElementById('amountMax').textContent = formatCurrency(maxValue) + ' VND';
        }

        function formatCurrency(value) {
            return value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        updateRange();

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
