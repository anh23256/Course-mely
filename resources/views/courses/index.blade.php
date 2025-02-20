@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $subTitle ?? '' }}</h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-danger">Import dữ liệu</button>
                            <a class="btn btn-sm btn-success"
                                href="{{ route('admin.courses.exportFile') }}">Export dữ liệu</a>
                            <button class="btn btn-sm btn-primary" id="toggleAdvancedSearch">
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
                                                    dụng
                                                </button>
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
                                    <label class="form-label">Mã khóa học</label>
                                    <input class="form-control form-control-sm" name="code" type="text"
                                        value="{{ request()->input('code') ?? '' }}" placeholder="Nhập mã khóa học..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tên khóa học</label>
                                    <input class="form-control form-control-sm" name="name" type="text"
                                        value="{{ request()->input('name') ?? '' }}" placeholder="Nhập tên khóa học..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Giảng viên</label>
                                    <input class="form-control form-control-sm" name="user_name" type="text"
                                        value="{{ request()->input('user_name') ?? '' }}" placeholder="Nhập giảng viên..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label for="statusItem" class="form-label">Mức độ</label>
                                    <select class="form-select form-select-sm" name="level" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn mức độ</option>
                                        <option @selected(request()->input('level') === 'beginner') value="beginner">Cơ
                                            bản
                                        </option>
                                        <option @selected(request()->input('level') === 'intermediate') value="intermediate">
                                            Trung cấp
                                        </option>
                                        <option @selected(request()->input('level') === 'advanced') value="advanced">
                                            Nâng cao
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-3 mt-2">
                                    <label for="statusItem" class="form-label">Trạng thái</label>
                                    <select class="form-select form-select-sm" name="status" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn trạng thái</option>
                                        <option @selected(request()->input('status') === 'draft') value="draft">Bản
                                            nháp
                                        </option>
                                        <option @selected(request()->input('status') === 'pending') value="draft">Đang
                                            chờ duyệt
                                        </option>
                                        <option @selected(request()->input('status') === 'approved') value="approved">
                                            Được phê duyệt
                                        </option>
                                        <option @selected(request()->input('status') === 'rejected') value="rejected">Bị
                                            từ chối
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

                    <div class="card-body" id="item_List">
                        <div class="listjs-table" id="customerList">
                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th>STT</th>
                                            <th>Ảnh bìa</th>
                                            <th>Tên khóa học</th>
                                            <th>Danh mục</th>
                                            <th>Giảng viên</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
                                        @foreach ($courses as $course)
                                            <tr>
                                                <td class="id">{{ $loop->index + 1 }}</td>
                                                <td class="phone">
                                                    @if ($course->thumbnail)
                                                        <img src="{{ $course->thumbnail }}" alt=""
                                                            width="100px">
                                                    @else
                                                        <p>Không có ảnh</p>
                                                    @endif
                                                </td>
                                                <td class="id">{{ $course->name }}</td>
                                                <td class="id">{{ $course->category->name ?? '' }}</td>
                                                <td class="id">{{ $course->user->name ?? '' }}</td>

                                                @if ($course->status == 'approved')
                                                    <td class="status"><span class="badge bg-success-subtle text-success">
                                                            Được phê duyệt
                                                        </span></td>
                                                @elseif ($course->status == 'rejected')
                                                    <td class="status"><span class="badge bg-danger-subtle text-danger">
                                                            Bị từ chối
                                                        </span></td>
                                                @elseif ($course->status == 'pending')
                                                    <td class="status"><span class="badge bg-warning-subtle text-warning">
                                                            Đang chờ duyệt
                                                        </span></td>
                                                @else
                                                    <td class="status"><span class="badge bg-danger-subtle text-danger">
                                                            Bản nháp
                                                        </span></td>
                                                @endif

                                                <td>
                                                    <div class="edit">
                                                        <a href="{{ route('admin.courses.show', $course->id) }}">
                                                            <button class="btn btn-sm btn-info edit-item-btn">
                                                                <span class="ri-eye-line"></span>
                                                            </button>
                                                        </a>
                                                    </div>
                                                </td>


                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $courses->appends(request()->query())->links() }}
                        </div>
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
        var routeUrlFilter = "{{ route('admin.courses.index') }}";
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
