@extends('layouts.app')

@section('title', $title)

@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card permission-card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">
                            <i class="ri-add-line me-1"></i> Thêm quyền mới
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#newPermissionForm" aria-expanded="false">
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="newPermissionForm">
                        <div class="card-body">
                            <form action="{{ route('admin.permissions.store') }}" method="POST" class="row g-3">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label">Tên quyền</label>
                                    <input type="text" name="name" class="form-control mb-2"
                                           placeholder="Nhập tên quyền..." value="{{ old('name') }}">

                                    @if ($errors->has('name'))
                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="inputEmail4" class="form-label">Mô tả</label>
                                    <input type="text" class="form-control mb-2" placeholder="Nhập mô tả..."
                                           value="{{ old('description') }}" name="description">

                                    @if ($errors->has('description'))
                                        <span class="text-danger">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <div class="">
                                        <button type="reset" class="btn btn-light me-2">
                                            <i class="ri-refresh-line me-1"></i> Nhập lại
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i> Thêm mới
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">{{ $subTitle ?? '' }}</h4>
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
                            <button class="btn btn-sm btn-success h-75">Export dữ liệu</button>
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
                                                        <label for="startDate" class="form-label">Từ ngày</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="created_at" id="dateRequest" data-filter>
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Đến ngày</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="updated_at" id="dateComplete" data-filter>
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
                                <div class="col-md-6">
                                    <label class="form-label">Quyền</label>
                                    <input class="form-control form-control-sm" name="name" type="text"
                                        placeholder="Nhập quyền..." data-advanced-filter>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mô tả</label>
                                    <input class="form-control form-control-sm" name="description" type="text"
                                        placeholder="Nhập mô tả quyền..." data-advanced-filter>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body" id="item_List">
                        <div class="live-preview">
                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Quyền</th>
                                            <th scope="col">Mô tả</th>
                                            <th scope="col">Ngày tạo</th>
                                            <th scope="col">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permissions as $guardName => $groupedPermissions)
                                            <td class="fw-bold" colspan="5">Module {{ Str::ucfirst($guardName) }}</td>
                                            @foreach ($groupedPermissions as $permission)
                                                <tr>
                                                    <td class="fw-medium">{{ $loop->iteration }}</td>
                                                    <td>{{ $permission->name }}</td>
                                                    <td>{{ $permission->description }}</td>
                                                    <td>{{ $permission->created_at }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.permissions.edit', $permission) }}">
                                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                                <span class="ri-edit-box-line"></span>
                                                            </button></a>


                                                        <a href="{{ route('admin.permissions.destroy', $permission->id) }}"
                                                            class="sweet-confirm btn btn-sm btn-danger ">
                                                            <span class="ri-delete-bin-7-line"></span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                            <div class="row justify-content-end mt-3">
                                {{ $permissions->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endSection
@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.permissions.index') }}";
        $(document).on('click', '#resetFilter', function() {
            handleSearchFilter('');
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
