@extends('layouts.app')

@push('page-css')
    <style>
        :root {
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
            --text-primary: #111827;
            --text-secondary: #4B5563;
            --text-light: #9CA3AF;
            --bg-light: #F9FAFB;
            --border-light: #E5E7EB;
        }

        .transaction-detail {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow: hidden;
            position: relative;
        }

        .transaction-header {
            padding: 18px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .transaction-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            transform: rotate(30deg);
        }

        .transaction-code {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 600;
            margin-left: 12px;
            backdrop-filter: blur(8px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.5px;
        }

        .transaction-body {
            padding: 30px;
        }

        .info-section {
            margin-bottom: 30px;
            border-radius: 14px;
            background: #fff;
            transition: all 0.3s ease;
            height: 100%;
        }

        .info-section:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
            transform: translateY(-5px);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #405189;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
        }

        .section-title i {
            background: #405189;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 18px;
        }

        .info-item {
            display: flex;
            margin-bottom: 20px;
            align-items: center;
            position: relative;
            padding-left: 12px;
        }

        .info-item::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 70%;
            background: #405189;
            border-radius: 4px;
        }

        .info-label {
            width: 40%;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 15px;
        }

        .info-value {
            width: 60%;
            color: var(--text-primary);
            font-weight: 500;
        }

        .info-value.highlighted {
            font-weight: 600;
            color: #405189;
        }

        .badge-custom i {
            margin-right: 5px;
        }

        .summary-section {
            background: linear-gradient(to right, #f8faff, #f0f4ff);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.08);
        }

        .summary-section::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 25%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.4));
            transform: skewX(-15deg);
        }

        .summary-item {
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            height: 100%;
            transition: all 0.3s ease;
        }

        .summary-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .summary-item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 12px;
            margin-right: 18px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .icon-price {
            background: linear-gradient(135deg, #FF6B6B, #FF8080);
            color: white;
        }

        .icon-status {
            background: linear-gradient(135deg, #20BF55, #01BAEF);
            color: white;
        }

        .icon-time {
            background: linear-gradient(135deg, #6366F1, #8B5CF6);
            color: white;
        }

        .summary-item-content {
            flex: 1;
        }

        .summary-item-label {
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .summary-item-value {
            font-weight: 700;
            font-size: 20px;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .summary-item-value.price {
            color: #FF6B6B;
            background: linear-gradient(90deg, #FF6B6B, #FF8080);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }

        .status-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: #D97706;
        }

        .status-failed {
            background-color: rgba(239, 68, 68, 0.1);
            color: #DC2626;
        }

        .course-section,
        .user-section {
            background-color: #fff;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            height: 100%;
        }

        .course-section {
            border-left: 4px solid #405189;
        }

        .user-section {
            border-left: 4px solid #10B981;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .coupon-section {
            margin-top: 25px;
            background-color: rgba(99, 102, 241, 0.03);
            border-radius: 10px;
            padding: 15px;
        }

        .coupon-badge {
            background: linear-gradient(135deg, #00C9FF, #92FE9D);
            color: #057857;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .link-hover {
            position: relative;
            display: inline-block;
            transition: all 0.3s ease;
            color: #405189;
        }

        .link-hover:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #405189;
            visibility: hidden;
            transform: scaleX(0);
            transition: all 0.3s ease;
        }

        .link-hover:hover:after {
            visibility: visible;
            transform: scaleX(1);
        }

        @media (max-width: 991px) {
            .summary-item {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 767px) {
            .transaction-header {
                padding: 20px;
            }

            .section-title {
                font-size: 16px;
            }

            .info-label, .info-value {
                width: 100%;
                display: block;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-label {
                margin-bottom: 5px;
            }

            .summary-item-icon {
                width: 45px;
                height: 45px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.withdrawals.index') }}">{{ $subTitle ?? 'Thông tin giao dịch' }}</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- End page title -->

        <div class="transaction-detail">
            <div class="transaction-header bg-primary bg-gradient bg-opacity-60">
                <div class="d-flex align-items-center">
                    <i class="ri-exchange-dollar-line me-3" style="font-size: 28px;"></i>
                    <h4 class="mb-0 fw-bold text-white">Thông tin giao dịch <span
                            class="transaction-code">{{ $transaction->transaction_code }}</span></h4>
                </div>
            </div>

            <div class="transaction-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="summary-section">
                            <div class="row g-4">
                                <div class="col-lg-4 col-md-4">
                                    <div class="summary-item">
                                        <div class="summary-item-icon icon-price">
                                            <i class="ri-money-dollar-circle-line" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="summary-item-content">
                                            <div class="summary-item-label">Tổng giá trị giao dịch</div>
                                            <div
                                                class="summary-item-value price">{{ number_format($transaction->amount ?? 0) }}
                                                VND
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4">
                                    <div class="summary-item">
                                        <div class="summary-item-icon icon-status">
                                            <i class="ri-checkbox-circle-line" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="summary-item-content">
                                            <div class="summary-item-label">Trạng thái giao dịch</div>
                                            <div class="summary-item-value">
                                                @if ($transaction->status === 'Giao dịch thành công')
                                                    <span class="status-badge status-success">
                                                    <i class="ri-check-line me-1"></i>{{ $transaction->status }}
                                                </span>
                                                @elseif($transaction->status === 'Chờ xử lý')
                                                    <span class="status-badge status-pending">
                                                    <i class="ri-time-line me-1"></i>{{ $transaction->status }}
                                                </span>
                                                @else
                                                    <span class="status-badge status-failed">
                                                    <i class="ri-close-circle-line me-1"></i>{{ $transaction->status }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4">
                                    <div class="summary-item">
                                        <div class="summary-item-icon icon-time">
                                            <i class="ri-calendar-check-line" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="summary-item-content">
                                            <div class="summary-item-label">Thời gian thực hiện</div>
                                            <div
                                                class="summary-item-value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="info-section course-section">
                            <h5 class="section-title">
                                <i class="ri-book-open-line"></i>Thông tin khóa học
                            </h5>
                            <div class="info-item">
                                <div class="info-label">Mã khóa học:</div>
                                <div class="info-value">{{ $transaction->invoice->course->code }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Tên khóa học:</div>
                                <div class="info-value highlighted">{{ $transaction->invoice->course->name }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Giảng viên:</div>
                                <div class="info-value">
                                    {{ $transaction->invoice->course->user->name }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Giá khóa học:</div>
                                <div
                                    class="info-value price">{{ number_format($transaction->invoice->course->price ?? 0) }}
                                    VND
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Đường dẫn khóa học:</div>
                                <div class="info-value">
                                    <a href="{{ config('app.fe_url') }}/courses/{{ $transaction->invoice->course->slug }}" target="_blank"
                                       class="link-hover">
                                        {{ $transaction->invoice->course->slug }}
                                        <i class="ri-external-link-line ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="info-section user-section">
                            <h5 class="section-title">
                                <i class="ri-user-3-line"></i>Thông tin người mua
                            </h5>
                            <div class="info-item">
                                <div class="info-label">Mã người mua:</div>
                                <div class="info-value">{{ $transaction->user->code }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Họ tên:</div>
                                <div class="info-value highlighted">{{ $transaction->user->name }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email:</div>
                                <div class="info-value">{{ $transaction->user->email }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Số điện thoại:</div>
                                <div
                                    class="info-value">{{ $transaction->invoice->course->user->profile->phone ?? 'Chưa có thông tin' }}</div>
                            </div>

                            <div class="coupon-section">
                                <h5 class="mb-3 fw-bold" style="font-size: 16px;">
                                    <i class="ri-coupon-3-line me-2"></i>Thông tin mã giảm giá
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">Mã giảm giá:</div>
                                            <div class="info-value">
                                                @if($transaction->invoice->coupon_code)
                                                    <span
                                                        class="coupon-badge">{{ $transaction->invoice->coupon_code }}</span>
                                                @else
                                                    <span class="text-muted">Không sử dụng</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <div class="info-label">Số tiền giảm:</div>
                                            <div
                                                class="info-value fw-bold">{{ $transaction->invoice->coupon_discount ?? '0 VND' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-4">
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-primary"> <i
                            class="ri-arrow-left-line me-2"></i>Quay lại danh sách</a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="ri-printer-line me-2"></i> In thông tin
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
