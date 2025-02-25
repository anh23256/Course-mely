@extends('layouts.app')

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
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                data-bs-target="#importModal">Import dữ liệu</button>

                        </div>


                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-filter-2-line"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                <div class="container">
                                    <li>
                                        <select class="form-select form-select-sm mb-2">
                                            <option value="">Tất cả trạng thái</option>
                                            <option value="active">Hoạt động</option>
                                            <option value="inactive">Không hoạt động</option>
                                            <option value="banned">Bị khóa</option>
                                        </select>
                                    </li>
                                    <li>
                                        <div class="mb-2">
                                            <label for="startDate" class="form-label">Từ ngày</label>
                                            <input type="date" class="form-control form-control-sm" id="startDate">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mb-2">
                                            <label for="endDate" class="form-label">Đến ngày</label>
                                            <input type="date" class="form-control form-control-sm" id="endDate">
                                        </div>
                                    </li>
                                    <li>
                                        <button class="btn btn-sm btn-primary w-100">Áp dụng</button>
                                    </li>
                                </div>
                            </ul>
                        </div>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <a href="{{ route('admin.qa-systems.create') }}">
                                            <button type="button" class="btn btn-primary add-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Thêm mới
                                            </button>
                                        </a>
                                        <button class="btn btn-danger" id="deleteSelected">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <form action="{{ route('admin.posts.index') }}" method="GET">
                                                <input type="text" name="searchPost" class="form-control search"
                                                    id="search-options" placeholder="Tìm kiếm..."
                                                    value="{{ request('searchUser') }}">
                                                <button type="submit" class="ri-search-line search-icon m-0 p-0 border-0"
                                                    style="background: none;"></button>
                                            </form>
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
                                            <th>#</th>
                                            <th>Tiêu đề</th>
                                            <th>Loại câu hỏi</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th>Hành Động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($qaSystems as $qaSystem)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" id="checkAll" type="checkbox"
                                                            name="postID" value="{{ $qaSystem->id }}">
                                                    </div>
                                                </th>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $qaSystem->title ?? '' }}</td>
                                                <td>
                                                    @if ($qaSystem->answer_type === 'multiple')
                                                        <span class="badge bg-primary">
                                                            Chọn nhiều
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info">
                                                            Chọn một
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($qaSystem->status === 1)
                                                        <span class="badge bg-success">
                                                            Hoạt động
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            Không hoạt động
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $qaSystem->created_at->format('d/m/Y') ?? '' }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.qa-systems.edit', $qaSystem->id) }}">
                                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                                <span class="ri-edit-box-line"></span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('admin.qa-systems.destroy', $qaSystem->id) }}"
                                                            class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                            <span class="ri-delete-bin-7-line"></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end">
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

                    <form id="importForm" action="{{ route('admin.qa-systems.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Chọn file để import:</label>
                            <input type="file" class="form-control" name="file" id="file"
                                accept=".xlsx,.xls,.csv" required>
                        </div>
                        <button type="submit" class="btn btn-success">Tiến hành Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    
@endpush
