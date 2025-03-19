@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        .invoice-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .invoice-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .invoice-title {
            color: #333;
            font-weight: 700;
            font-size: 1.75rem;
        }

        .invoice-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .invoice-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .invoice-info-label {
            font-weight: 600;
            color: #6c757d;
        }

        .invoice-info-value {
            font-weight: 500;
            color: #212529;
        }

        .course-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .course-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .course-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }

        .course-details {
            display: flex;
            align-items: center;
        }

        .course-info {
            flex-grow: 1;
        }

        .course-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .course-instructor {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .invoice-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-weight: 500;
            color: #6c757d;
        }

        .summary-value {
            font-weight: 600;
            color: #212529;
        }

        .summary-total {
            font-weight: 700;
            color: #007bff;
            font-size: 1.2rem;
        }

        .payment-status {
            background-color: #42d262;
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            display: inline-block;
        }
    </style>
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
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.invoices.index') }}">{{ $subTitle ?? '' }}</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice details -->
        <div class="row">
            <div class="col-lg-12">
                <div class="invoice-container">
                    <div class="invoice-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h1 class="invoice-title">Hóa đơn thanh toán</h1>
                                <p class="text-muted mb-0">Mã hóa đơn: <strong>{{ $invoice->code }}</strong></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="payment-status">Đã thanh toán</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Thông tin khách hàng</h5>
                                <p class="mb-1"><strong>Tên khách hàng:</strong> {{ $invoice->user->name }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $invoice->user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="invoice-info">
                                <div class="invoice-info-item">
                                    <span class="invoice-info-label">Ngày mua:</span>
                                    <span
                                        class="invoice-info-value">{{ \Illuminate\Support\Carbon::parse($invoice->created_at)->locale('vi')->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="invoice-info-item">
                                    <span class="invoice-info-label">Phương thức thanh toán:</span>
                                    <span class="invoice-info-value">VNPAY</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 mb-4">
                        <h5 class="fw-bold mb-3">Khóa học đã mua</h5>
                        <div class="course-card">
                            <div class="course-details">
                                <img src="{{ $invoice->course->thumbnail }}" class="course-image" alt="Thumbnail"/>
                                <div class="course-info">
                                    <div class="course-name">{{ $invoice->course->name }}</div>
                                    <div class="course-instructor">
                                        <i class="far fa-user me-1"></i> {{ $invoice->course->user->name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-summary">
                        <div class="summary-row">
                            <span class="summary-label">Tổng cộng</span>
                            <span class="summary-value">{{ number_format(round($invoice->course->price_sale, 2) ?? $invoice->course->price, 0, ',', '.') }} VND</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Giảm giá</span>
                            <span
                                class="summary-value">{{ number_format($invoice->coupon_discount, 0, ',', '.') }} VND</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Tổng tiền</span>
                            <span
                                class="text-primary summary-total">{{ number_format($invoice->final_amount, 0, ',', '.') }} VND</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
