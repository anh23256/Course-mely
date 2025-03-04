@extends('layouts.app')

@push('page-css')
    <style>
        .row.mb-3 {
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 1%;
            margin-bottom: 1% px;
        }

        .row.mb-3:last-child {
            border-bottom: none;
        }

        .col-md-3 {
            font-weight: bold;
            color: #555;
        }
    </style>
@endpush

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
                    <img src="{{ Auth::user()->avatar ?? '' }}" alt="user-img" class="img-thumbnail rounded-circle"/>
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
            <div class="col-12 col-lg-auto order-last order-lg-0">
                <div class="row text text-white-50 text-center">
                    <div class="col-lg-6 col-4">
                        <div class="p-2">
                            <h5 class="text-white mb-1">{{ number_format($wallet->balance ?? 0) }}</h5>
                            <p class="fs-14 mb-0">Số dư ví</p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-4">
                        <div class="p-2 w-100">
                            <span class="badge d-flex justify-content-center fs-14 bg-warning px-3 py-2 w-100">
                                Rút tiền
                            </span>
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
                                        <h5 class="text-center mb-5">Thông tin giao dịch</h5>
                                        <div class="row justify-content-between">

                                            <div class="col-6">
                                                @if($systemFund->type === 'commission_received')
                                                    <div class="row mb-3">
                                                        <div class="col-md-3"><strong>Mã khóa học:</strong></div>
                                                        <div class="col-md-9">{{ $systemFund->course->code }}</div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-3"><strong>Tên khóa học:</strong></div>
                                                        <div class="col-md-9">{{ $systemFund->course->name }}</div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-3"><strong>Giảng viên:</strong></div>
                                                        <div
                                                            class="col-md-9">{{ $systemFund->course->user->name }}</div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-3"><strong>Đường dẫn khóa học:</strong></div>
                                                        <div class="col-md-9">{{ $systemFund->course->slug }}</div>
                                                    </div>
                                                @endif
                                                <div class="row mb-3">
                                                    <div class="col-md-3"><strong>Tổng tiền:</strong></div>
                                                    <div class="col-md-9">
                                                        {{ number_format($systemFund->total_amount ?? 0) }} VND
                                                    </div>
                                                </div>
                                                @if ($systemFund->type === 'commission_received')
                                                    <div class="row mb-3">
                                                        <div class="col-md-3"><strong>Hoa hồng:</strong></div>
                                                        <div class="col-md-9">
                                                            {{ number_format($systemFund->retained_amount ?? 0) }} VND
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="row mb-3">
                                                    <div class="col-md-3"><strong>Loại giao dịch:</strong></div>
                                                    <div class="col-md-9">
                                                        @if ($systemFund->type === 'commission_received')
                                                            <span class="badge bg-danger">
                                                                Tiền hoa hồng
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning">
                                                              Thanh toán tiền
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-3"><strong>Thời gian thực hiện:</strong></div>
                                                    <div class="col-md-9">{{ $systemFund->created_at }}</div>
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                <div class="row mb-3">
                                                    @if($systemFund->type === 'commission_received')
                                                        <div class="col-md-3"><strong>Mã người mua:</strong></div>
                                                    @else
                                                        <div class="col-md-3"><strong>Mã người giao dịch:</strong></div>
                                                    @endif
                                                    <div class="col-md-9">{{ $systemFund->user->code }}</div>
                                                </div>
                                                <div class="row mb-3">
                                                    @if($systemFund->type === 'commission_received')
                                                        <div class="col-md-3"><strong>Tên người mua:</strong></div>
                                                    @else
                                                        <div class="col-md-3"><strong>Tên người giao dịch:</strong>
                                                        </div>
                                                    @endif
                                                    <div class="col-md-9">{{ $systemFund->user->name }}</div>
                                                </div>
                                                <div class="row mb-3">
                                                    @if($systemFund->type === 'commission_received')
                                                        <div class="col-md-3"><strong>Email người mua:</strong>
                                                        </div>
                                                    @else
                                                        <div class="col-md-3"><strong>Email giao dịch:</strong></div>
                                                    @endif
                                                    <div class="col-md-9">{{ $systemFund->user->email }}</div>
                                                </div>
                                                @if($systemFund->type === 'commission_received')
                                                    <div class="row mb-3">
                                                        <div class="col-md-3"><strong>Số người mua:</strong></div>
                                                        <div class="col-md-9">
                                                            {{ $systemFund->course->user->profile->phone ?? 'Chưa có thông tin' }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.wallets.index') }}" type="button"
                                           class="btn btn-success add-btn">
                                            Quay lại</a>
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
    </div>
@endsection
