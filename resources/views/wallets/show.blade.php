@extends('layouts.app')

@push('page-css')
    <style>
        .transaction-detail-row {
            padding: 12px 0;
            margin-bottom: 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .transaction-detail-row:last-child {
            border-bottom: none;
        }

        .transaction-label {
            font-weight: 600;
            color: #495057;
        }

        .transaction-value {
            color: #212529;
        }

        .transaction-card {
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }

        .transaction-header {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
            border-bottom: 1px solid #f5f5f5;
        }

        .transaction-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: #0ab39c;
        }

        .transaction-amount {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0ab39c;
        }

        .transaction-amount.negative {
            color: #f06548;
        }

        .transaction-type-badge {
            padding: 8px 15px;
            font-weight: 500;
            border-radius: 5px;
        }

        .transaction-section {
            margin-bottom: 30px;
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
                            <a href="#" class="badge d-flex justify-content-center fs-14 bg-warning px-3 py-2 w-100 text-decoration-none">
                                Rút tiền
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card transaction-card">
                <div class="card-body p-4">
                    <div class="transaction-header">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="m-0">Chi tiết giao dịch</h4>
                            <a href="{{ route('admin.wallets.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="ri-arrow-left-line me-1"></i> Quay lại
                            </a>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex flex-column">
                                    <span class="text-muted fs-13">Số tiền giao dịch</span>
                                    <span class="transaction-amount {{ $systemFund->type !== 'commission_received' ? 'negative' : '' }}">
                                        {{ number_format($systemFund->total_amount ?? 0) }} VND
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <span class="text-muted fs-13 mb-2">Loại giao dịch</span>
                                    @if ($systemFund->type === 'commission_received')
                                        <span class="transaction-type-badge bg-success-subtle text-success">
                                            <i class="ri-arrow-down-circle-line me-1"></i> Tiền hoa hồng
                                        </span>
                                    @else
                                        <span class="transaction-type-badge bg-danger-subtle text-danger">
                                            <i class="ri-arrow-up-circle-line me-1"></i> Thanh toán tiền
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <span class="text-muted fs-13 mb-2">Thời gian giao dịch</span>
                                    <span class="fs-14">
                                        <i class="ri-time-line me-1"></i> {{ \Carbon\Carbon::parse($systemFund->created_at)->format('H:i - d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 transaction-section">
                            <h5 class="mb-3">Thông tin giao dịch</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    @if($systemFund->type === 'commission_received')
                                        <div class="transaction-detail-row row">
                                            <div class="col-md-4 transaction-label">Mã khóa học</div>
                                            <div class="col-md-8 transaction-value">{{ $systemFund->course->code }}</div>
                                        </div>
                                        <div class="transaction-detail-row row">
                                            <div class="col-md-4 transaction-label">Tên khóa học</div>
                                            <div class="col-md-8 transaction-value">{{ $systemFund->course->name }}</div>
                                        </div>
                                        <div class="transaction-detail-row row">
                                            <div class="col-md-4 transaction-label">Giảng viên</div>
                                            <div class="col-md-8 transaction-value">{{ $systemFund->course->user->name }}</div>
                                        </div>
                                        <div class="transaction-detail-row row">
                                            <div class="col-md-4 transaction-label">Đường dẫn</div>
                                            <div class="col-md-8 transaction-value">
                                                <a href="{{ config('app.fe_url') . '/courses/' .$systemFund->course->slug }}" target="_blank" class="text-primary">
                                                    {{ config('app.fe_url') . '/courses/' .$systemFund->course->slug }}
                                                    <i class="ri-external-link-line ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="transaction-detail-row row">
                                        <div class="col-md-4 transaction-label">Tổng tiền</div>
                                        <div class="col-md-8 transaction-value fw-bold">{{ number_format($systemFund->total_amount ?? 0) }} VND</div>
                                    </div>

                                    @if ($systemFund->type === 'commission_received')
                                        <div class="transaction-detail-row row">
                                            <div class="col-md-4 transaction-label">Hoa hồng</div>
                                            <div class="col-md-8 transaction-value text-success fw-bold">{{ number_format($systemFund->retained_amount ?? 0) }} VND</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 transaction-section">
                            <h5 class="mb-3">Thông tin người dùng</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="transaction-detail-row row">
                                        <div class="col-md-4 transaction-label">
                                            @if($systemFund->type === 'commission_received')
                                                Mã người mua
                                            @else
                                                Mã người giao dịch
                                            @endif
                                        </div>
                                        <div class="col-md-8 transaction-value">{{ $systemFund->user->code }}</div>
                                    </div>
                                    <div class="transaction-detail-row row">
                                        <div class="col-md-4 transaction-label">
                                            @if($systemFund->type === 'commission_received')
                                                Tên người mua
                                            @else
                                                Tên người giao dịch
                                            @endif
                                        </div>
                                        <div class="col-md-8 transaction-value">{{ $systemFund->user->name }}</div>
                                    </div>
                                    <div class="transaction-detail-row row">
                                        <div class="col-md-4 transaction-label">
                                            @if($systemFund->type === 'commission_received')
                                                Email người mua
                                            @else
                                                Email giao dịch
                                            @endif
                                        </div>
                                        <div class="col-md-8 transaction-value">{{ $systemFund->user->email }}</div>
                                    </div>
                                    @if($systemFund->type === 'commission_received')
                                        <div class="transaction-detail-row row">
                                            <div class="col-md-4 transaction-label">Số điện thoại</div>
                                            <div class="col-md-8 transaction-value">
                                                {{ $systemFund->course->user->profile->phone ?? 'Chưa có thông tin' }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
