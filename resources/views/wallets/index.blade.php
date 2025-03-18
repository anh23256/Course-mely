@extends('layouts.app')

@section('content')
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="{{ asset('assets/images/profile-bg.jpg') }}" alt="" class="profile-wid-img"/>
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    <img src="{{ Auth::user()->avatar ?? asset('assets/images/default-avatar.png') }}" alt="user-img"
                         class="img-thumbnail rounded-circle"/>
                </div>
            </div>
            <!--end col-->
            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1">
                        {{ Str::ucfirst(Auth::user()->name) ?? '' }}
                    </h3>
                    <p class="text-white text-opacity-75">
                        {{ Auth::check() && Auth::user()->roles->count() > 0 ? (Auth::user()->roles->first()->name == 'super_admin' ? 'Chủ sở hữu & Người sáng lập' : 'Nhân viên') : '' }}
                    </p>
                    <div class="hstack text-white-50 gap-1">
                        <div class="me-2"><i
                                class="ri-map-pin-user-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>{{ Auth::user()->profile->address ?? 'Chưa có thông tin' }}
                        </div>
                        <div>
                            <i
                                class="ri-phone-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>{{ Auth::user()->profile->phone ?? 'Chưa có thông tin' }}
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->
            <div class="col-12 col-lg-auto order-last order-lg-0 mx-5">
                <div class="row text text-white-50 text-center">
                    <div class="col-lg-12 col-12">
                        <div class="p-2">
                            <h5 class="text-white mb-1">{{ number_format($balanceSystem->balance ?? 0) }} VND</h5>
                            <p class="fs-14 mb-0">Số dư ví</p>
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div class="tab-content pt-4 text-muted">
                    <div class="tab-pane active">
                        <div class="row">
                            <div>
                                <div class="card">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0">Lịch sử giao dịch</h5>
                                            <div class="search-box position-relative">
                                                <input type="text" name="search_full" id="searchFull"
                                                       class="form-control search rounded-pill border-0 shadow-sm px-4 py-2"
                                                       placeholder="Tìm kiếm theo ngày và mô tả..."
                                                       value="{{ request()->input('search_full') ?? '' }}">
                                                <i class="ri-search-line position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body border-bottom pb-0">
                                        <div class="row g-3">
                                            <div class="col-sm-6 col-md-3">
                                                <div class="card bg-success bg-opacity-10 border-0">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div
                                                                    class="avatar-sm rounded bg-success bg-opacity-25 p-2">
                                                                    <i class="ri-arrow-up-circle-line fs-3 text-success"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">{{ number_format($totalIncome ?? 0) }}
                                                                    VND</h5>
                                                                <p class="mb-0 text-muted fs-12">Tổng thu</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-3">
                                                <div class="card bg-danger bg-opacity-10 border-0">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div
                                                                    class="avatar-sm rounded bg-danger bg-opacity-25 p-2">
                                                                    <i class="ri-arrow-down-circle-line fs-3 text-danger"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">{{ number_format($totalExpense ?? 0) }}
                                                                    VND</h5>
                                                                <p class="mb-0 text-muted fs-12">Tổng chi</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-3">
                                                <div class="card bg-info bg-opacity-10 border-0">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div
                                                                    class="avatar-sm rounded bg-info bg-opacity-25 p-2">
                                                                    <i class="ri-exchange-line fs-3 text-info"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">{{ $totalTransactions ?? 0 }}</h5>
                                                                <p class="mb-0 text-muted fs-12">Tổng giao dịch</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-md-3">
                                                <div class="card bg-primary bg-opacity-10 border-0">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div
                                                                    class="avatar-sm rounded bg-primary bg-opacity-25 p-2">
                                                                    <i class="ri-calendar-line fs-3 text-primary"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</h5>
                                                                <p class="mb-0 text-muted fs-12">Ngày hiện tại</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body" id="transaction-container">
                                        @php
                                            $currentDay = null;
                                        @endphp

                                        @foreach ($systemFunds as $systemFund)
                                            @if ($currentDay !== $systemFund->day)
                                                @php
                                                    $currentDay = $systemFund->day;
                                                @endphp
                                                <div class="col-12">
                                                    <div class="bg-light p-3 rounded">
                                                        <h5 class="mb-0 text-center text-primary">📅 Ngày:
                                                            {{ date('d/m/Y', strtotime($currentDay)) }}</h5>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="col-12 col-md-12">
                                                <div class="card shadow-sm mb-2">
                                                    <div class="card-body">
                                                        <div>
                                                            <div class="d-flex">
                                                                <div class="col-11">
                                                                    <h5 class="card-title fs-15 text-secondary mb-3">
                                                                        Biến động số dư</h5>
                                                                </div>
                                                                <div class="col-1 text-center">
                                                                    <span class="text-muted">
                                                                        {{ $systemFund->created_at ? \Carbon\Carbon::parse($systemFund->created_at)->format('H:i:s') : '00:00:00' }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-11">
                                                                    <h6 class="mb-1 {{ $systemFund->type == 'withdrawal' ? 'text-danger' : 'text-success' }}">
                                                                        {{ $systemFund->type == 'withdrawal' ? '-' : '+' }}
                                                                        {{ number_format($systemFund->type == 'commission_received' ? $systemFund->retained_amount ?? 0 : $systemFund->total_amount ?? 0) }}
                                                                        VND
                                                                    </h6>
                                                                    <p class="text-muted mb-0">
                                                                        {{ $systemFund->description ?? 'Không có mô tả' }}
                                                                    </p>
                                                                </div>
                                                                <div class="col-1 text-center">
                                                                    <a href="{{ route('admin.wallets.show', $systemFund->id) }}">
                                                                        <button
                                                                            class="btn btn-sm btn-info edit-item-btn">
                                                                            <span class="ri-eye-line"></span>
                                                                        </button>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="col-12 col-md-12">
                                            <div class="d-flex mt-4 justify-content-center">
                                                <button id="load-more" class="btn btn-sm btn-primary px-4 rounded-pill">
                                                    <i class="ri-refresh-line me-1"></i> Xem thêm
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end row-->
                            </div>
                            <!--end card-body-->
                        </div><!-- end card -->
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
        </div>
        <!--end tab-content-->
    </div>
@endsection

@push('page-scripts')
    <script>
        $(document).ready(function () {
            let limitPage = 10;

            $(document).on('click', '#load-more', function () {
                limitPage += 10;
                $(this).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang tải...');

                handleAjax({
                    page: limitPage
                });
            });

            let searchTimeout;
            $(document).on('input', '#searchFull', function () {
                clearTimeout(searchTimeout);
                const searchValue = $(this).val();

                searchTimeout = setTimeout(function () {
                    handleAjax({
                        search: searchValue
                    });
                }, 500);
            });
        });

        function handleAjax(data) {
            $.ajax({
                url: "{{ route('admin.wallets.index') }}",
                type: "GET",
                data: data,
                dataType: "json",
                beforeSend: function () {
                    if (!data.search) {
                        $("#load-more").html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang tải...');
                    }
                },
                success: function (response) {
                    $("#transaction-container").html(response.systemFunds);

                    console.log(response);
                    
                    if (data.search && data.search.length > 0) {
                        $("#load-more").hide();
                    } else {
                        $("#load-more").show().html('<i class="ri-refresh-line me-1"></i> Xem thêm');
                    }
                },
                error: function () {
                    $("#load-more").html('<i class="ri-refresh-line me-1"></i> Thử lại');
                }
            });
        }
    </script>
@endpush
