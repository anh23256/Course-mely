@extends('layouts.app')

@section('content')
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="{{ asset('assets/images/profile-bg.jpg') }}" alt="" class="profile-wid-img" />
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    <img src="{{ Auth::user()->avatar ?? '' }}" alt="user-img" class="img-thumbnail rounded-circle" />
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
                                    <div class="card-body">
                                        <div class="search-box ms-2">
                                            <input type="text" name="search_full" id="searchFull"
                                                class="form-control search" placeholder="Tìm kiếm theo ngày và mô tả..."
                                                data-search value="{{ request()->input('search_full') ?? '' }}">
                                            <button id="search-full" class="ri-search-line search-icon m-0 p-0 border-0"
                                                style="background: none;"></button>
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
                                                                    <a
                                                                        href="{{ route('admin.wallets.show', $systemFund->id) }}">
                                                                        <button class="btn btn-sm btn-info edit-item-btn">
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
                                                <span class="text-primary text-decoration-underline" id="load-more">Xem
                                                    thêm</span>
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
        $(document).ready(function() {
            let limitPage = 10;

            $(document).on('click', '#load-more', function() {
                limitPage += 10;

                handleAjax({
                    page: limitPage
                });
            });

            $(document).on('change', '#searchFull', function() {
                handleAjax({
                    search: $(this).val()
                })
            });
        });

        function handleAjax(data) {
            $.ajax({
                url: "{{ route('admin.wallets.index') }}",
                type: "GET",
                data: data,
                dataType: "json",
                beforeSend: function() {
                    $("#load-more").text("Đang tải...");
                },
                success: function(response) {
                    $("#transaction-container").html(response.systemFunds);

                    if (data.search) {
                        $("#load-more").hide();
                    } else {
                        $("#load-more").show();
                    }
                },
            });
        }
    </script>
@endpush
