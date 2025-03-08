@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid mb-3">

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
        <!-- List-customer -->
        <div class="row">
            <div class="col-lg-12">
                <div class="container mt-4 p-4 bg-white rounded shadow-sm border">
                    <h1 class="text-center fw-bold">Hóa đơn thanh toán</h1>
                    <p class="text-muted fs-6">Hóa đơn của, <strong>{{ $invoice->user->name }}</strong></p>
                    <p class="text-muted">Đơn hàng đã được thanh toán thành công.</p>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Ngày đặt</th>
                                <th>Phương thức thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $invoice->code }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($invoice->created_at)->locale('vi')->translatedFormat('d F Y') }}
                                </td>
                                <td>VNPAY</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4 class="text-decoration-underline mt-4">Khóa học đã mua:</h4>
                    <table class="table mt-2">
                        <thead>
                            <tr>
                                <th>Tên khóa học</th>
                                <th>Giảng viên</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="{{ $invoice->course->thumbnail }}" style="width: 10%;" class="img-thumbnail img-preview"
                                        alt="Thumnnail" />
                                    {{ $invoice->course->name }}
                                </td>
                                <td>{{ $invoice->course->user->name }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table">
                        <tbody>
                            <tr>
                                <td colspan="2" class="text-first">Tổng cộng</td>
                                <th class="text-end">
                                    {{ number_format(round($invoice->course->price_sale, 2) ?? $invoice->course->price, 0, ',', '.') }}
                                    VND
                                </th>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-first">Giảm giá</td>
                                <th class="text-end">
                                    {{ number_format($invoice->coupon_discount, 0, ',', '.') }} VND
                                </th>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-first border-top fw-bold">Tổng tiền</td>
                                <th class="text-end border-top">
                                    {{ number_format($invoice->final_amount, 0, ',', '.') }} VND
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end List-customer -->
    </div>
@endsection
