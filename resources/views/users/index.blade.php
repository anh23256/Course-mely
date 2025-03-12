@extends('layouts.app')
@section('title', $title)
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 150px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .user-table th, .user-table td {
            vertical-align: middle;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Danh sách {{ $roleUser['actor'] }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.' . $roleUser['role_name'] . '.index') }}">Danh sách
                                    {{ $roleUser['actor'] }}</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stat-card bg-white">
                    <div class="card-body text-center">
                        <div class="stat-icon text-primary">
                            <i class="ri-user-3-line"></i>
                        </div>
                        <h5 class="card-title text-muted mb-2">Tổng số {{ $roleUser['actor'] }}</h5>
                        <h3 class="card-text fw-bold">{{ $userCounts->total_users ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stat-card bg-white">
                    <div class="card-body text-center">
                        <div class="stat-icon text-success">
                            <i class="ri-user-follow-line"></i>
                        </div>
                        <h5 class="card-title text-muted mb-2">{{ Str::ucfirst($roleUser['actor']) }} hoạt động</h5>
                        <h3 class="card-text fw-bold text-success">{{ $userCounts->active_users ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stat-card bg-white">
                    <div class="card-body text-center">
                        <div class="stat-icon text-warning">
                            <i class="ri-user-unfollow-line"></i>
                        </div>
                        <h5 class="card-title text-muted mb-2">{{ Str::ucfirst($roleUser['actor']) }} không hoạt động</h5>
                        <h3 class="card-text fw-bold text-warning">{{ $userCounts->inactive_users ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card stat-card bg-white">
                    <div class="card-body text-center">
                        <div class="stat-icon text-danger">
                            <i class="ri-lock-line"></i>
                        </div>
                        <h5 class="card-title text-muted mb-2">{{ Str::ucfirst($roleUser['actor']) }} bị khóa</h5>
                        <h3 class="card-text fw-bold text-danger">{{ $userCounts->blocked_users ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- List-customer -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Danh sách {{ $roleUser['actor'] }}</h4>
                        <div class="d-flex gap-2">
                            @if ($roleUser['name'] !== 'deleted')
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#importModal">Import dữ liệu</button>
                            @endif
                            <a class="btn btn-sm btn-success"
                               href="{{ route('admin.users.export', $roleUser['name']) }}">Export dữ liệu</a>
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
                                                        <label class="form-label">Ngày bắt đầu</label>
                                                        @if ($roleUser['name'] !== 'deleted')
                                                            <input type="date" class="form-control form-control-sm"
                                                                   name="created_at" data-filter
                                                                   value="{{ request()->input('created_at') ?? '' }}">
                                                        @else
                                                            <input type="date" class="form-control form-control-sm"
                                                                   name="start_deleted" data-filter
                                                                   value="{{ request()->input('created_at') ?? '' }}">
                                                        @endif
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label class="form-label">Ngày kết thúc</label>
                                                        @if ($roleUser['name'] !== 'deleted')
                                                            <input type="date" class="form-control form-control-sm"
                                                                   name="updated_at" id="endDate" data-filter
                                                                   value="{{ request()->input('updated_at') ?? '' }}">
                                                        @else
                                                            <input type="date" class="form-control form-control-sm"
                                                                   name="end_deleted" data-filter
                                                                   value="{{ request()->input('updated_at') ?? '' }}">
                                                        @endif
                                                    </div>
                                                </li>
                                            </div>
                                            <li class="mt-2 d-flex gap-1">
                                                <button class="btn btn-sm btn-success flex-grow-1" type="reset"
                                                        id="resetFilter">Reset</button>
                                                <button class="btn btn-sm btn-primary flex-grow-1" id="applyFilter">Áp
                                                    dụng</button>
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
                                    <label class="form-label">Mã người dùng</label>
                                    <input class="form-control form-control-sm" name="code" type="text"
                                           value="{{ request()->input('code') ?? '' }}" placeholder="Nhập mã người dùng..."
                                           data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tên khách hàng</label>
                                    <input class="form-control form-control-sm" name="name" type="text"
                                           value="{{ request()->input('name') ?? '' }}" placeholder="Nhập tên khách hàng..."
                                           data-advanced-filter>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Email</label>
                                    <input class="form-control form-control-sm" name="email" name="email"
                                           type="email" value="{{ request()->input('email') ?? '' }}"
                                           placeholder="Nhập email..." data-advanced-filter>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input class="form-control form-control-sm" name="profile_phone_user" type="text"
                                           value="{{ request()->input('profile_phone_user') ?? '' }}"
                                           placeholder="Nhập số điện thoại..." data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label for="statusItem" class="form-label">Trạng thái</label>
                                    <select class="form-select form-select-sm" name="status" id="statusItem"
                                            data-advanced-filter>
                                        <option value="">Chọn trạng thái</option>
                                        <option @selected(request()->input('status') === 'active') value="active">Hoạt động</option>
                                        <option @selected(request()->input('status') === 'inactive') value="inactive">Không hoạt động</option>
                                        <option @selected(request()->input('status') === 'blocked') value="blocked">Khóa</option>
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
                                        @if ($roleUser['name'] === 'deleted')
                                            <button class="btn btn-danger" id="restoreSelected">
                                                <i class=" ri-restart-line"> Khôi phục</i>
                                            </button>
                                        @else
                                            <a href="{{ route('admin.users.create') }}">
                                                <button type="button" class="btn btn-primary add-btn">
                                                    <i class="ri-add-line align-bottom me-1"></i> Thêm mới
                                                </button>
                                            </a>
                                        @endif
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

                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th>STT</th>
                                        <th>Họ và tên</th>
                                        <th>Email</th>
                                        <th>Số điện thoại</th>
                                        <th>Xác minh email</th>
                                        <th>Trạng Thái</th>
                                        <th>Vai Trò</th>
                                        @if ($roleUser['name'] !== 'deleted')
                                            <th>Ngày Tham Gia</th>
                                            <th>Hành Động</th>
                                        @else
                                            <th>Thời gian xóa</th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody class="list">
                                    @foreach ($users as $user)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="itemID"
                                                           value="{{ $user->id }}">
                                                </div>
                                            </th>
                                            <td class="id"><a
                                                    class="fw-medium link-primary">{{ $loop->index + 1 }}</a></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $user->avatar ?? '' }}" alt="Avatar" class="user-avatar me-3">
                                                    <div>
                                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="email">{{ $user->email ?? 'Chưa có thông tin' }}</td>
                                            <td class="phone">{{ $user->profile->phone ?? 'Chưa có thông tin' }}</td>
                                            <td>
                                                <div class="form-check form-switch form-switch-warning">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           {{ $roleUser['name'] !== 'deleted' ? 'name=email_verified' : 'disabled' }}
                                                           value="{{ $user->id }}" @checked($user->email_verified_at != null)>
                                                </div>
                                            </td>
                                            <td class="status">
                                                @if ($user->status === 'active')
                                                    <span class="badge bg-success w-100">
                                                            Active
                                                        </span>
                                                @elseif($user->status === 'inactive')
                                                    <span class="badge bg-warning w-100">
                                                            Inactive
                                                        </span>
                                                @else
                                                    <span class="badge bg-danger w-100">
                                                            Block
                                                        </span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $roleName =
                                                        $roleUser['name'] === 'deleted'
                                                            ? $user->roles->first()?->name
                                                            : $roleUser['name'] ?? 'member';

                                                    $badgeColor = Arr::get(
                                                        config('roles.colors'),
                                                        $roleName,
                                                        'bg-primary',
                                                ); @endphp
                                                <span class="badge {{ $badgeColor }} w-100">
                                                        {{ Str::ucfirst($roleName) }}
                                                    </span>
                                            </td>
                                            @if ($roleUser['name'] !== 'deleted')
                                                <td>
                                                    {{ optional($user->created_at)->format('d/m/Y') ?? 'NULL' }}
                                                </td>
                                            @endif
                                            <td>
                                                @if ($roleUser['name'] !== 'deleted')
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.users.edit', $user->id) }}">
                                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                                <span class="ri-edit-box-line"></span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('admin.users.show', $user->id) }}">
                                                            <button class="btn btn-sm btn-info edit-item-btn">
                                                                <span class="ri-folder-user-line"></span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('admin.users.destroy', $user->id) }}"
                                                           class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                            <span class="ri-delete-bin-7-line"></span>
                                                        </a>
                                                    </div>
                                                @else
                                                    {{ optional($user->deleted_at)->format('d/m/Y') ?? 'NULL' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end">
                                {{ $users->appends(request()->query())->links() }}
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

    @if ($roleUser['name'] !== 'deleted')
        <!-- Modal import -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Users từ Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <a href="{{ asset('storage/csv/users_import_template.xlsx') }}" download
                               class="btn btn-outline-primary btn-sm">Tải Mẫu</a>
                        </div>
                        <form action="{{ route('admin.users.import', $roleUser['name']) }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Chọn File Excel</label>
                                <input type="file" name="file" class="form-control" required>
                                @error('file')
                                <span class="badge bg-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.' . $actorRole . '.index') }}";
        var routeDeleteAll =
            "{{ $roleUser['name'] === 'deleted' ? route('admin.users.forceDelete', ':itemID') : route('admin.users.destroy', ':itemID') }}";
        var routeRestoreUrl = "{{ route('admin.users.restoreDelete', ':itemID') }}";

        $(document).on('change', 'input[name="email_verified"]', function() {
            var userID = $(this).val();
            var isChecked = $(this).is(':checked');

            var updateUrl = "{{ route('admin.users.updateEmailVerified', ':userID') }}".replace(
                ':userID', userID);

            $.ajax({
                type: "PUT",
                url: updateUrl,
                data: {
                    email_verified: isChecked ? userID : ''
                },
            });
        });
        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/restore-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
