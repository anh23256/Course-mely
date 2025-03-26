@extends('layouts.app')

@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
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
        <div class="row cursor-pointer">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card stats-card total-card">
                    <div class="card-body text-center">
                        <i class="ri-file-list-3-line card-icon"></i>
                        <h5 class="card-title">Tổng số yêu cầu</h5>
                        <p class="card-text">{{ number_format($approvalCount->total_approval ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card stats-card approved-card">
                    <div class="card-body text-center">
                        <i class="ri-checkbox-circle-line card-icon"></i>
                        <h5 class="card-title">Đã kiểm duyệt</h5>
                        <p class="card-text">{{ number_format($approvalCount->approved_approval ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card stats-card pending-card">
                    <div class="card-body text-center">
                        <i class="ri-time-line card-icon"></i>
                        <h5 class="card-title">Chờ xử lý</h5>
                        <p class="card-text">{{ number_format($approvalCount->pending_approval ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card stats-card rejected-card">
                    <div class="card-body text-center">
                        <i class="ri-close-circle-line card-icon"></i>
                        <h5 class="card-title">Bị từ chối</h5>
                        <p class="card-text">{{ number_format($approvalCount->rejected_approval ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- List-posts -->
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
                                            <div class="row">
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="approval_start_date" class="form-label">Ngày bắt đầu
                                                            kiểm duyệt</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="approval_start_date" id="approval_start_date" data-filter
                                                            value="{{ request()->input('approval_start_date') ?? '' }}">
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="approval_end_date" class="form-label">Ngày kết thúc
                                                            kiểm duyệt</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="approval_end_date" id="approval_end_date" data-filter
                                                            value="{{ request()->input('approval_end_date') ?? '' }}">
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
                                    <label class="form-label">Tiêu đề bài viết</label>
                                    <input class="form-control form-control-sm" name="post_title_approved" type="text"
                                        placeholder="Nhập tiêu đề bài viết..."
                                        value="{{ request()->input('post_title_approved') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tên tác giả</label>
                                    <input class="form-control form-control-sm" name="user_name_approved" type="text"
                                        placeholder="Nhập tên tác giả..."
                                        value="{{ request()->input('user_name_approved') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tên người kiểm duyệt</label>
                                    <input class="form-control form-control-sm" name="approver_name_approved"
                                        type="text" placeholder="Nhập tên người kiểm duyệt..."
                                        value="{{ request()->input('approver_name_approved') ?? '' }}"
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label for="statusItem" class="form-label">Trạng thái kiểm duyệt</label>
                                    <select class="form-select form-select-sm" name="status" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn trạng thái</option>
                                        <option value="approved" @selected(request()->input('status') === 'approved')>Đã
                                            kiểm duyệt
                                        </option>
                                        <option value="pending" @selected(request()->input('status') === 'pending')>Chờ
                                            xử lý
                                        </option>
                                        <option value="rejected" @selected(request()->input('status') === 'rejected')>Từ
                                            chối
                                        </option>
                                    </select>
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
                        <div class="listjs-table" id="postList">
                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="postTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>STT</th>
                                            <th>Tiêu đề bài viết</th>
                                            <th>Tác giả</th>
                                            <th>Hình ảnh</th>
                                            <th>Người kiểm duyệt</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày gửi yêu cầu</th>
                                            <th>Ngày kiểm duyệt</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @forelse ($approvals as $approval)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $approval->approvable ? \Illuminate\Support\Str::limit($approval->approvable->title ?? 'Không có tiêu đề', 50) : 'Không có bài viết' }}
                                                </td>
                                                <td>{{ $approval->approvable && $approval->approvable->user ? $approval->approvable->user->name : '' }}
                                                </td>
                                                <td>
                                                    <img style="height: 80px"
                                                        src="{{ $approval->approvable && $approval->approvable->thumbnail ? $approval->approvable->thumbnail : asset('assets/images/no-photo.jpg') }}"
                                                        alt="" class="w-100 object-fit-cover">
                                                </td>
                                                <td>
                                                    {!! $approval->approver
                                                        ? $approval->approver->name
                                                        : '<span class="btn btn-sm btn-soft-success">Hệ thống đã xử lý</span>' !!}
                                                </td>
                                                <td>
                                                    @if ($approval->status == 'pending')
                                                        <span class="btn btn-sm btn-soft-warning">Chờ xử lý</span>
                                                    @elseif($approval->status == 'approved')
                                                        <span class="btn btn-sm btn-soft-success">Đã kiểm duyệt</span>
                                                    @else
                                                        <span class="btn btn-sm btn-soft-danger">Từ chối</span>
                                                    @endif
                                                </td>
                                                <td>{!! $approval->request_date
                                                    ? \Carbon\Carbon::parse($approval->request_date)->format('d/m/Y')
                                                    : '<span class="btn btn-sm btn-soft-warning">Chưa kiểm duyệt</span>' !!}</td>
                                                <td>
                                                    @if ($approval->approved_at)
                                                        {{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y') }}
                                                    @elseif($approval->rejected_at)
                                                        {{ \Carbon\Carbon::parse($approval->rejected_at)->format('d/m/Y') }}
                                                    @else
                                                        <span class="btn btn-sm btn-soft-warning">Chưa kiểm duyệt</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.approvals.posts.show', $approval->id) }}">
                                                        <button class="btn btn-sm btn-info edit-item-btn">
                                                            <span class="ri-eye-line"></span>
                                                        </button>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">Không có bài viết nào để kiểm
                                                    duyệt.</td>
                                            </tr>
                                        @endforelse
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
        <!-- end List-posts -->
    </div>
@endsection

@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.approvals.posts.index') }}";

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
