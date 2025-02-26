@extends('layouts.app')

@push('page-css')
    <style>
        .row.mb-3 {
            border-bottom: 1px solid #f0f0f0;
            /* Đường kẻ phân cách mỗi dòng */
            padding-bottom: 1%;
            /* Khoảng cách dưới mỗi dòng */
            margin-bottom: 1%px;
            /* Khoảng cách giữa các dòng */
        }

        .row.mb-3:last-child {
            border-bottom: none;
            /* Loại bỏ đường kẻ cuối */
        }

        .col-md-3 {
            font-weight: bold;
            /* Làm nổi bật nhãn (label) */
            color: #555;
            /* Màu chữ nhãn nhã nhặn hơn */
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Chi tiết coupon</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{route('admin.coupons.index')}}">Danh sách coupon</a></li>
                            <li class="breadcrumb-item active"><a href="{{route('admin.coupons.show', $coupon->id)}}">Chi tiết coupon</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Chi tiết coupon <span
                        class="text-danger">{{ $coupon->name }}</span></h4>
            </div><!-- end card header -->

            <div class="card-body px-5">
                <div class="row">
                    {{-- <div class="col-4">
                        <div class="p-2 text-center">
                            <div class="mx-auto mb-3">
                                <img src="{{ $coupon->avatar }}" alt="Hình avatar" class="img-fluid rounded-circle">
                            </div>
                            <h6 class="mb-1">Avatar</h6>
                        </div>
                    </div> --}}

                    <div class="col-6">
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>ID:</strong></div>
                            <div class="col-md-9">{{ $coupon->id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Mã giảm giá:</strong></div>
                            <div class="col-md-9">{{ $coupon->code }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Tên mã giảm giá:</strong></div>
                            <div class="col-md-9">{{ $coupon->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Loại mã giảm giá:</strong></div>
                            <div class="col-md-9">{{ $coupon->discount_type }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Giá trị giảm giá:</strong></div>
                            <div class="col-md-9">{{ number_format($coupon->discount_value) }}
                                {{ $coupon->discount_type == 'fixed' ? 'VND' : '%' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Giảm giá tối đa:</strong></div>
                            <div class="col-md-9">{{ number_format($coupon->discount_max_value) }} VND</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Ngày bắt đầu:</strong></div>
                            <div class="col-md-9">{{ $coupon->start_date }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Ngày kết thúc:</strong></div>
                            <div class="col-md-9">{{ $coupon->expire_date }}</div>
                        </div>
                        <div class="text-start mt-5">
                            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Quay lại</a>
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-warning">
                                Chỉnh sửa
                            </a>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Trạng thái:</strong></div>
                            <div class="col-md-9">
                                @if ($coupon->status)
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        InActive
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Số lượt sử dụng:</strong></div>
                            <div class="col-md-9"> 
                                {{$coupon->used_count}}                                                   
                              </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Người tạo:</strong></div>
                            <div class="col-md-9"> 
                                {{$coupon->user_id}}                                                   
                              </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Mô tả:</strong></div>
                            <div class="col-md-9"> 
                                {{$coupon->description}}                                                   
                              </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Ngày tạo:</strong></div>
                            <div class="col-md-9">{{ $coupon->created_at }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Ngày cập nhật:</strong></div>
                            <div class="col-md-9">{{ $coupon->updated_at }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
