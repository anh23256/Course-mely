@extends('layouts.app')

@push('page-css')
    <style>
        .row.mb-4 {
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 1%;
            margin-bottom: 1%px;
            align-items: center
        }

        .row.mb-3:last-child {
            border-bottom: none;
        }

        .col-md-4, .col-md-5 {
            font-weight: bold;
            color: #555;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Chi tiết người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">
                                <a href="#">{{ $title ?? '' }}</a>
                            </li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header align-items-center justify-content-center d-flex">
                <h4 class="mb-3">Thông tin giao dịch: <span
                        class="text-danger">{{ $transaction->transaction_code }}</span></h4>
            </div><!-- end card header -->

            <div class="card-body px-5">
                <div class="row justify-content-between">
                    <div class="col-6">
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Mã khóa học:</strong></div>
                            <div class="col-md-8">{{ $transaction->invoice->course->code }}
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Tên khóa học:</strong></div>
                            <div class="col-md-8">{{ $transaction->invoice->course->name }}
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Giảng viên:</strong></div>
                            <div class="col-md-8">
                                {{ $transaction->invoice->course->user->name }}
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Giá khóa học:</strong></div>
                            <div class="col-md-8">
                                {{ number_format($transaction->invoice->course->price ?? 0) }}
                                VND
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="col-md-4"><strong>Đường dẫn khóa học:</strong></div>
                            <div class="col-md-8">{{ $transaction->invoice->course->slug }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Mã người mua:</strong></div>
                            <div class="col-md-8">{{ $transaction->user->code }}</div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Người mua:</strong></div>
                            <div class="col-md-8">{{ $transaction->user->name }}</div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Email người mua:</strong></div>
                            <div class="col-md-8">{{ $transaction->user->email }}</div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Số người mua:</strong></div>
                            <div class="col-md-8">
                                {{ $transaction->invoice->course->user->profile->phone ?? 'Chưa có thông tin' }}
                            </div>
                        </div>
                        <div class="mb-4 d-flex">
                            <div class="col-6 d-flex">
                                <div class="col-md-5"><strong>Coupon sử dụng:</strong></div>
                                <div class="col-md-7">
                                    {{ $transaction->invoice->coupon_code ?? 'Không dùng mã giảm giá' }}
                                </div>
                            </div>
                            <div class="col-6 d-flex">
                                <div class="col-md-4"><strong>Số tiền giảm:</strong></div>
                                <div class="col-md-8">
                                    {{ $transaction->invoice->coupon_discount ?? '0 VND' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-items-around ms-4 my-3">
                        <div class="col-4 mb-4 d-flex">
                            <div class="col-md-4"><strong>Tổng tiền:</strong></div>
                            <div class="col-md-8 text-danger fw-bold">
                                {{ number_format($transaction->amount ?? 0) }} VND</div>
                        </div>
                        <div class="col-4 mb-4 d-flex">
                            <div class="col-md-4"><strong>Trạng thái:</strong></div>
                            <div class="col-md-8">
                                @if ($transaction->status === 'Giao dịch thành công')
                                    <span class="badge bg-success">
                                        {{ $transaction->status }}
                                    </span>
                                @elseif($transaction->status === 'Chờ xử lý')
                                    <span class="badge bg-warning">
                                        {{ $transaction->status }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        {{ $transaction->status }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 mb-4 d-flex">
                            <div class="col-md-4"><strong>Thời gian thực hiện:</strong></div>
                            <div class="col-md-8">{{ $transaction->created_at }}</div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.transactions.index') }}" type="button" class="btn btn-success add-btn">
                    Quay lại</a>
            </div>
            <!--end row-->
        </div>
    </div>
@endsection
