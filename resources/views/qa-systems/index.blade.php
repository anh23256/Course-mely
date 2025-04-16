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
                            <a href="{{ route('admin.qa-systems.create') }}" class="btn btn-primary btn-sm">
                                <i class="ri-add-line align-bottom me-1"></i> Thêm mới
                            </a>
                            <button class="btn btn-danger btn-sm" id="deleteSelected" disabled>
                                <i class="ri-delete-bin-2-line me-1"></i> Xóa
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#importModal">Import dữ liệu</button>

                            <a href="{{ route('admin.qa-systems.exportFile') }}" class="btn btn-sm btn-success h-75">Export
                                dữ
                                liệu</a>
                            <button class="btn btn-sm btn-primary h-75" id="toggleAdvancedSearch">
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
                                        <div class="row">
                                            <li class="col-6">
                                                <div class="mb-2">
                                                    <label for="startDate" class="form-label">Ngày bắt đầu</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                           name="startDate" id="dateRequest" data-filter
                                                           value="{{ request()->input('startDate') ?? '' }}">
                                                </div>
                                            </li>
                                            <li class="col-6">
                                                <div class="mb-2">
                                                    <label for="endDate" class="form-label">Ngày kết thúc</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                           name="endDate" id="dateComplete" data-filter
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
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- end card header -->

                    <!-- Tìm kiếm nâng cao -->
                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Tiêu đề</label>
                                <input class="form-control form-control-sm" name="title" type="text"
                                       placeholder="Nhập tiêu đề..." value="{{ request()->input('title') ?? '' }}"
                                       data-advanced-filter>
                            </div>
                            <div class="col-md-4">
                                <label for="answer_type" class="form-label">Loại câu hỏi</label>
                                <select class="form-select form-select-sm" name="answer_type" id="answer_type"
                                        data-advanced-filter>
                                    <option value="">Chọn loại câu hỏi</option>
                                    <option value="single" @selected(request()->input('answer_type') === 'single')>
                                        Chọn một
                                    </option>
                                    <option value="multiple" @selected(request()->input('answer_type') === 'multiple')>Đang
                                        Chọn nhiều
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="statusItem" class="form-label">Trạng thái</label>
                                <select class="form-select form-select-sm" name="status" id="statusItem"
                                        data-advanced-filter>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="0" @selected(request()->input('status') === '0')>
                                        Không hoạt động
                                    </option>
                                    <option value="1" @selected(request()->input('status') === '1')>
                                        Hoạt động
                                    </option>
                                </select>
                            </div>
                            <div class="mt-3 text-end">
                                <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="input-group" style="max-width: 300px;">
                                <input type="text" name="search_full" class="form-control form-control-sm"
                                       placeholder="Tìm kiếm nhanh..." data-search>
                                <button id="search-full" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-search-line"></i>
                                </button>
                            </div>
                            <div class="text-muted small">
                                Hiển thị {{ $qaSystems->count() }} / {{ $qaSystems->total() }} câu hỏi
                            </div>
                        </div>
                        <div class="listjs-table" id="customerList">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th width="40px">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                            </div>
                                        </th>
                                        <th width="50px">#</th>
                                        <th>Tiêu đề</th>
                                        <th width="120px">Loại câu hỏi</th>
                                        <th width="120px">Trạng thái</th>
                                        <th width="120px">Ngày tạo</th>
                                        <th width="100px">Hành động</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($qaSystems as $qaSystem)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox" type="checkbox"
                                                           name="itemID" value="{{ $qaSystem->id }}">
                                                </div>
                                            </td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $qaSystem->title ?? 'N/A' }}</td>
                                            <td>
                                                @if ($qaSystem->answer_type === 'multiple')
                                                    <span class="badge bg-info text-dark">
                                                        <i class="ri-checkbox-multiple-line me-1"></i> Chọn nhiều
                                                    </span>
                                                @else
                                                    <span class="badge bg-primary">
                                                        <i class="ri-radio-button-line me-1"></i> Chọn một
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($qaSystem->status === 1)
                                                    <span class="badge bg-success">
                                                        <i class="ri-checkbox-circle-line me-1"></i> Hoạt động
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="ri-close-circle-line me-1"></i> Không hoạt động
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $qaSystem->created_at->format('d/m/Y') ?? 'N/A' }}</td>
                                            <td>
                                                <div class="hstack gap-2">
                                                    <a href="{{ route('admin.qa-systems.edit', $qaSystem->id) }}"
                                                       class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Chỉnh sửa">
                                                        <i class="ri-edit-2-line"></i>
                                                    </a>
                                                    <a href="{{ route('admin.qa-systems.destroy', $qaSystem->id) }}"
                                                       class="sweet-confirm btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Xóa">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-folder-info-line fs-3 d-block mb-2"></i>
                                                    Không tìm thấy dữ liệu
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                {{ $qaSystems->appends(request()->query())->links() }}
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

    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Dữ Liệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importForm" action="{{ route('admin.qa-systems.import') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Chọn file để import:</label>
                            <input type="file" class="form-control" name="file" id="file"
                                   accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">
                                Định dạng hỗ trợ: Excel (.xlsx, .xls) hoặc CSV (.csv)
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-success">
                                <i class="ri-upload-2-line me-1"></i> Tiến hành Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.qa-systems.index') }}";
        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
