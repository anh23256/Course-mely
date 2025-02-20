@extends('layouts.app')
@section('title', $title)
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Giao dịch thanh toán</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.transactions.index') }}">{{ $subTitle }}</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- social-customer -->
        <div class="row mb-2">
            <div class="col-12 col-sm-6 col-md-4">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Tổng số giao dịch</h5>
                        <p class="card-text fs-4">{{ $countTransactions->total_transactions ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Số giao dịch mua khóa học</h5>
                        <p class="card-text fs-4 text-success">{{ $countTransactions->invoice_transactions ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Số giao dịch rút tiền</h5>
                        <p class="card-text fs-4 text-warning">{{ $countTransactions->withdrawal_transactions ?? 0 }}</p>
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
                        <h4 class="card-title mb-0">Danh sách giao dịch thanh toán</h4>
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
                            
                            <a class="btn btn-sm btn-success h-75" href="{{ route('admin.transactions.export') }}">Export dữ liệu</a>
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
                                                        value="{{ request()->input('amount_min') ?? 10000 }}" oninput="updateRange()" data-filter>
                                                    <input type="range" class="form-range w-50" id="amountMaxRange"
                                                        name="amount_max" min="50000000" max="99990000" step="10000"
                                                        value="{{ request()->input('amount_max') ?? 99990000 }}" oninput="updateRange()" data-filter>
                                                </div>
                                            </li>
                                            <div class="row">
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="startDate" class="form-label">Ngày yêu cầu</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="created_at" id="dateRequest" data-filter
                                                            value="{{ request()->input('created_at') ?? '' }}">
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Ngày xác nhận</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="updated_at" id="dateComplete" data-filter
                                                            value="{{ request()->input('updated_at') ?? '' }}">
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
                                <div class="col-md-4">
                                    <label class="form-label">Người thực hiện giao dịch</label>
                                    <input class="form-control form-control-sm" name="user_transaction" type="text"
                                        placeholder="Nhập tên người thực hiện giao dịch..."
                                        value="{{ request()->input('user_transaction') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="col-md-4">
                                    <label for="statusTransaction" class="form-label">Trạng thái</label>
                                    <select class="form-select form-select-sm" name="status" id="statusTransaction"
                                        data-advanced-filter>
                                        <option value="">Chọn trạng thái</option>
                                        <option value="completed" @selected(request()->input('status') === 'completed')>
                                            Hoàn thành
                                        </option>
                                        <option value="pending" @selected(request()->input('status') === 'pending')>Đang
                                            xử lý
                                        </option>
                                        <option value="failed" @selected(request()->input('status') === 'failed')>Thất
                                            bại
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="typeTransaction" class="form-label">Loại giao dịch</label>
                                    <select class="form-select form-select-sm" name="type" id="typeTransaction"
                                        data-advanced-filter>
                                        <option value="">Chọn loại giao dịch</option>
                                        <option value="invoice" @selected(request()->input('type') === 'invoice')>Mua
                                            bán
                                        </option>
                                        <option value="withdrawal" @selected(request()->input('type') === 'withdrawal')>
                                            Rút tiền
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
                                            <th>Mã giao dịch</th>
                                            <th>Người thực hiện giao dịch</th>
                                            <th>Email</th>
                                            <th>Số tiền</th>
                                            <th>Loại giao dịch</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo giao dịch</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($transactions as $transaction)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>{{ $transaction->transaction_code ?? 'Không có thông tin' }}</td>
                                                <td><span
                                                        class="text-primary fw-bold">{{ $transaction->invoice->user->name ?? 'Không có thông tin'}}</span>
                                                </td>
                                                <td>{{ $transaction->invoice->user->email ?? 'Không có thông tin' }}</td>
                                                <td>{{ number_format($transaction->amount) ?? 0 }} VND</td>
                                                <td>
                                                    @if ($transaction->type === 'invoice')
                                                        <span class="badge bg-success w-50">
                                                            Mua bán
                                                        </span>
                                                    @elseif($transaction->type === 'withdrawal')
                                                        <span class="badge bg-info w-50">
                                                            Rút tiền
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($transaction->status === 'Giao dịch thành công')
                                                        <span class="badge bg-success w-75">
                                                            Hoàn thành
                                                        </span>
                                                    @elseif($transaction->status === 'Chờ xử lý')
                                                        <span class="badge bg-warning w-75">
                                                            Đang xử lý
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger w-75">
                                                            Thất bại
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $transaction->created_at ?? '' }}
                                                </td>
                                                <td>
                                                    <a
                                                        href="{{ route('admin.transactions.show', $transaction->id) }}">
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
                                {{ $transactions->appends(request()->query())->links() }}
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
        var routeUrlFilter = "{{ route('admin.transactions.index') }}";

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
@endpush
