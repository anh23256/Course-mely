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
                            <a href="{{ route('admin.approvals.instructors.export') }}" class="btn btn-sm btn-success h-75">Export dữ liệu</a>
                            <button class="btn btn-sm btn-primary h-75" id="toggleAdvancedSearch">
                                <i class="ri-filter-2-line"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Tìm kiếm nâng cao -->
                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Tên giảng viên</label>
                                <input class="form-control form-control-sm" name="name_instructor" type="text"
                                    placeholder="Nhập tên giảng viên..."
                                    value="{{ request()->input('name_instructor') ?? '' }}" data-advanced-filter>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Số điện thoại giảng viên</label>
                                <input class="form-control form-control-sm" name="phone_instructor" type="text"
                                    placeholder="Nhập số điện thoại giảng viên..."
                                    value="{{ request()->input('phone_instructor') ?? '' }}" data-advanced-filter>
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
                            <div class="col-md-3 mt-3">
                                <label for="statusItem" class="form-label">Trạng thái kiểm duyệt</label>
                                <select class="form-select form-select-sm" name="status" id="statusItem"
                                    data-advanced-filter>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="approved" @selected(request()->input('status') === 'approved')>Đã kiểm duyệt</option>
                                    <option value="pending" @selected(request()->input('status') === 'pending')>Chờ xử lý</option>
                                    <option value="rejected" @selected(request()->input('status') === 'rejected')>Từ chối</option>
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                <div class="mb-2">
                                    <label for="request_start_date" class="form-label">Ngày bắt đầu gửi
                                        yêu cầu</label>
                                    <input type="date" class="form-control form-control-sm" name="request_start_date"
                                        id="request_start_date" data-advanced-filter
                                        value="{{ request()->input('request_start_date') ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3 mt-3">
                                <div class="mb-2">
                                    <label for="request_end_date" class="form-label">Ngày kết thúc gửi
                                        yêu cầu</label>
                                    <input type="date" class="form-control form-control-sm" name="request_end_date"
                                        id="request_end_date" data-advanced-filter
                                        value="{{ request()->input('request_end_date') ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3 mt-3">
                                <label for="approval_start_date" class="form-label">Ngày bắt đầu
                                    kiểm duyệt</label>
                                <input type="date" class="form-control form-control-sm" name="approval_start_date"
                                    id="approval_start_date" data-advanced-filter
                                    value="{{ request()->input('approval_start_date') ?? '' }}">
                            </div>
                            </li>
                            <div class="col-md-3 mt-3">
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
                    </div>

                    <!-- end card header -->
                    <div class="card-body" id="item_List">
                        <div class="listjs-table" id="customerList">
                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên giảng viên</th>
                                            <th>Email</th>
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
                                                <td>
                                                    <img src="{{ $approval->user->avatar ?? '' }}" alt="Avatar"
                                                        class="user-avatar me-3">
                                                    {{ $approval->user->name ?? '' }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-danger font-weight-bold">{{ $approval->user->email ?? '' }}</span>
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $approval->user->profile->phone ?? '' }}</small>
                                                </td>
                                                <td>
                                                    {!! !empty($approval->approver->name)
                                                        ? '<span class="badge bg-primary text-white"><i class="bx bx-user"></i> ' . $approval->approver->name . '</span>'
                                                        : '<span class="badge bg-secondary text-white"><i class="bx bx-cog"></i> Hệ thống đã xử lý</span>' !!}
                                                </td>
                                                <td>
                                                    @if ($approval->status == 'pending')
                                                        <span class="badge bg-warning text-dark"><i
                                                                class="bx bx-time-five"></i>
                                                            Chờ xử lý</span>
                                                    @elseif($approval->status == 'approved')
                                                        <span class="badge bg-success text-white"><i
                                                                class="bx bx-check-circle"></i> Đã kiểm duyệt</span>
                                                    @else
                                                        <span class="badge bg-danger text-white"><i
                                                                class="bx bx-x-circle"></i> Từ
                                                            chối</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {!! $approval->request_date
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
                                                        <span class="badge bg-warning text-dark"><i
                                                                class="bx bx-time"></i>
                                                            Chưa kiểm duyệt</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a
                                                        href="{{ route('admin.approvals.instructors.show', $approval->id) }}">
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
        var routeUrlFilter = "{{ route('admin.approvals.instructors.index') }}";

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
