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

                    <!-- Tìm kiếm nâng cao -->
                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Tên khóa học</label>
                                <input class="form-control form-control-sm" name="course_name_approved" type="text"
                                    value="{{ request()->input('course_name_approved') ?? '' }}" placeholder="Nhập tiêu đề..."
                                    data-advanced-filter>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sắp xếp theo tổng tiền</label>
                                <select class="form-control form-control-sm" name="sort_total_spent" data-advanced-filter>
                                    <option value="">-- Chọn sắp xếp --</option>
                                    <option value="asc" {{ request('sort_total_spent') == 'asc' ? 'selected' : '' }}>Từ thấp đến cao</option>
                                    <option value="desc" {{ request('sort_total_spent') == 'desc' ? 'selected' : '' }}>Từ cao đến thấp</option>
                                </select>
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

                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">
                                                <input type="checkbox" id="checkAll">
                                            </th>
                                            <th>STT</th>
                                            <th>Tên</th>
                                            <th>Email</th>
                                            <th>Khóa học</th>
                                            <th>Tổng tiền đã chi</th>
                                            <th>Số lần đăng ký membership</th>
                                            <th>Ngày đăng ký membership gần nhất</th>

                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($memberships as $user)
                                            @foreach ($user->invoices as $invoice)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="checkItem" value="">
                                                    </td>
                                                    <td class="id">{{ $loop->parent->iteration }}</td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $invoice->course->name }}</td>
                                                    <td>{{ number_format($invoice->total_spent, 0, ',', '.') }} VNĐ</td>
                                                    <td>{{ $invoice->total_registrations }} lần</td>
                                                    <td>{{ \Carbon\Carbon::parse($invoice->latest_membership)->format('d/m/Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div class="row justify-content-end">
                            {{ $memberships->appends(request()->query())->links() }}
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
        var routeUrlFilter = "{{ route('admin.memberships.index') }}";

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
