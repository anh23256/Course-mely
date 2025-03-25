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

                            <li class="breadcrumb-item active"><a href="">{{ $subTitle ?? '' }}</a></li>

                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- List-customer -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $subTitle ?? '' }}</h4>

                        <div class="d-flex gap-2">
                            
                            <button class="btn btn-sm btn-primary" id="toggleAdvancedSearch">
                                Tìm kiếm nâng cao
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
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
                                                        <label for="startDate" class="form-label">Ngày bắt đầu</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="created_at" id="startDate" data-filter
                                                            value="{{ request()->input('startDate') ?? '' }}">
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Ngày kết thúc</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="updated_at" id="endDate" data-filter
                                                            value="{{ request()->input('endDate') ?? '' }}">
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

                    <div id="advancedSearch" class="card-header" style="display:none;">

                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại thông báo</label>
                                    <select class="form-select form-select-sm" name="notification_type" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn loại thông báo</option>
                                        @foreach ($notifications->unique(fn($n) => $n->data['type']) as $notification)
                                            <option value="{{ $notification->data['type'] }}">
                                                {{ ucfirst(str_replace('_', ' ', $notification->data['type'])) }}
                                            </option>
                                        @endforeach
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
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <button class="btn btn-danger" id="deleteSelected">
                                            <i class="ri-delete-bin-2-line"> Xóa nhiều</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <input type="text" name="search_full" id="searchFull"
                                                class="form-control search" placeholder="Tìm kiếm..." data-search
                                                value="{{ request()->input('search_full') ?? '' }}">
                                            <button id="search-full" class="ri-search-line search-icon m-0 p-0 border-0"
                                                style="background: none;"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link {{ request('status', 'all') === 'all' ? 'active' : '' }}"
                                        href="{{ route('admin.notifications.all-notifications', ['status' => 'all']) }}">
                                        Tất cả
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request('status') === 'unread' ? 'active' : '' }}"
                                        href="{{ route('admin.notifications.all-notifications', ['status' => 'unread']) }}">
                                        Chưa đọc
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request('status') === 'read' ? 'active' : '' }}"
                                        href="{{ route('admin.notifications.all-notifications', ['status' => 'read']) }}">
                                        Đã đọc
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content mt-3">
                                <!-- Tab Tất cả -->
                                <div class="tab-pane fade show active" id="all">
                                    <div class="table-responsive table-card">
                                        <table class="table align-middle table-nowrap">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="width: 50px;">
                                                        <input type="checkbox" id="checkAll">
                                                    </th>
                                                    <th>STT</th>
                                                    <th>Loại thông báo</th>
                                                    <th>Nội dung</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ngày gửi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($notifications as $key => $notification)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="checkItem" name="itemID"
                                                                value="{{ $notification->id }}">
                                                        </td>
                                                        <td>{{ $notifications->firstItem() + $key }}</td>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                {{ ucfirst(str_replace('_', ' ', $notification->data['type'] ?? 'Không xác định')) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $notification->data['message'] ?? 'Không có nội dung' }}
                                                        </td>
                                                        <td>
                                                            @if (is_null($notification->read_at))
                                                                <span class="badge bg-danger">Chưa đọc</span>
                                                            @else
                                                                <span class="badge bg-success">Đã đọc</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Hiển thị phân trang -->
                                    <div class="mt-3">
                                        {{ $notifications->links() }}
                                    </div>

                                </div>


                            </div>

                        </div>
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <!-- end col -->
        </div>

        <!-- end row -->
    </div>

    <!-- end List-customer -->
    </div>
@endsection
@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.notifications.all-notifications') }}";
        var routeDeleteAll = "{{ route('admin.notifications.forceDelete', ':itemID') }}";

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>

    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
