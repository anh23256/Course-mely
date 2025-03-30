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

        <!-- social-customer -->
        <div class="row mb-2">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Tổng số yêu cầu</h5>
                        <p class="card-text fs-4">{{ $approvalCount->total_approval ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Yêu cầu đã kiểm duyệt</h5>
                        <p class="card-text fs-4 text-success">{{ $approvalCount->approved_approval ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Yêu cầu chờ xử lý</h5>
                        <p class="card-text fs-4 text-warning">{{ $approvalCount->pending_approval ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Yêu cầu bị từ chối</h5>
                        <p class="card-text fs-4 text-danger">{{ $approvalCount->rejected_approval ?? 0 }}</p>
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
                                            <div class="row">
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="approval_start_date" class="form-label">Ngày bắt đầu
                                                            kiểm duyệt</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="approval_start_date" id="approval_start_date"
                                                            data-filter
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
                                    <label class="form-label">Tên giảng viên</label>
                                    <input class="form-control form-control-sm" name="membershipPlan_instructor_name"
                                        type="text" placeholder="Nhập tên đăng kí..."
                                        value="{{ request()->input('membershipPlan_instructor_name') ?? '' }}"
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email</label>
                                    <input class="form-control form-control-sm" name="membershipPlan_instructor_email"
                                        type="text" placeholder="Nhập tên giảng viên..."
                                        value="{{ request()->input('membershipPlan_instructor_email') ?? '' }}"
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
                        <div class="listjs-table" id="customerList">
                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên gói</th>
                                            <th>Giảng viên</th>
                                            <th>Email</th>
                                            <th>Người kiểm duyệt</th>
                                            <th>Giá</th>
                                            <th>Thời hạn</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày yêu cầu</th>
                                            <th>Ngày kiểm duyệt</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($approvals as $approval)
                                            <tr>
                                                <td>{{ $loop->iteration ?? '' }}</td>
                                                <td>{{ $approval->membershipPlan->name ?? '' }}</td>
                                                <td>{{ $approval->membershipPlan->instructor->name ?? '' }}</td>
                                                <td>{{ $approval->membershipPlan->instructor->email ?? '' }}</td>
                                                <td>{{ $approval->approver->name ?? '' }}</td>
                                                <td>{{ number_format($approval->membershipPlan->price, 0, ',', '.') }}đ
                                                </td>
                                                <td>{{ $approval->membershipPlan->duration_months ?? '' }} tháng</td>
                                                <td>
                                                    @if ($approval->status === 'pending')
                                                        <span class="badge bg-warning">Chờ duyệt</span>
                                                    @elseif ($approval->status === 'approved')
                                                        <span class="badge bg-success">Đã duyệt</span>
                                                    @elseif ($approval->status === 'rejected')
                                                        <span class="badge bg-danger">Từ chối</span>
                                                    @endif
                                                </td>
                                                <td>{{ $approval->request_date }}</td>
                                                <td>{{ $approval->approved_at }}  </td>

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
        var routeUrlFilter = "{{ route('admin.approvals.memberships.index') }}";

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

    <script>
        $(document).ready(function() {
            function fetchData() {
                let advancedFormData = $('#advancedSearch form').serialize(); // Lấy dữ liệu form advanced search
                let dropdownFormData = $('#filterDropdown').closest('.dropdown').find('form')
            .serialize(); // Lấy dữ liệu form dropdown filter

                let formData = advancedFormData + '&' + dropdownFormData; // Gộp 2 form thành 1 request

                $.ajax({
                    url: "{{ route('admin.approvals.memberships.index') }}",
                    type: "GET",
                    data: formData,
                    beforeSend: function() {
                        $('#table-container').html(
                        '<div class="text-center">Đang tải...</div>'); // Hiển thị loading
                    },
                    success: function(response) {
                        $('#table-container').html(response.html); // Cập nhật bảng dữ liệu
                    },
                    error: function(xhr) {
                        alert("Đã có lỗi xảy ra: " + xhr.responseText); // Hiển thị lỗi nếu có
                    }
                });
            }

            // Bấm nút "Áp dụng" để lọc dữ liệu
            $('#applyAdvancedFilter, #applyFilter').click(function(e) {
                e.preventDefault();
                fetchData(); // Gọi AJAX để cập nhật dữ liệu
            });

            // Bấm nút "Reset" để xóa bộ lọc
            $('#resetFilter').click(function(e) {
                e.preventDefault();
                $('#advancedSearch form')[0].reset(); // Reset form advanced
                $('#filterDropdown').closest('.dropdown').find('form')[0]
            .reset(); // Reset form dropdown filter
                fetchData(); // Gọi AJAX để lấy dữ liệu mặc định
            });

            // Lọc ngay khi thay đổi input/select
            $('input[data-advanced-filter], select[data-advanced-filter], input[data-filter], select[data-filter]')
                .on("change", function() {
                    fetchData();
                });
        });
    </script>
@endpush
