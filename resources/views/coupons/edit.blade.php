@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@php
    $title = 'Cập nhật coupon';
@endphp
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 ps-2">Quản lí coupon</h4>

                <div class="page-title-right pe-3">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active"><a href="{{ route('admin.coupons.index') }}">Danh sách coupon</a>
                        </li>
                        <li class="breadcrumb-item active"><a href="{{ route('admin.coupons.create') }}">Cập nhật coupon</a>
                        </li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Cập nhật coupon</h4>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="d-flex">
                                    <div class="mb-3 col-6 pe-3">
                                        <label class="form-label">Tên mã giảm giá</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ $coupon->name }}" placeholder="Nhập tên mã giảm giá">
                                    </div>
                                    <div class="mb-3 col-3 pe-3">
                                        <label class="form-label">Ngày bắt đầu</label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ $coupon->start_date }}">
                                    </div>
                                    <div class="mb-3 col-3">
                                        <label class="form-label">Ngày kết thúc</label>
                                        <input type="date" name="expire_date" class="form-control"
                                            value="{{ $coupon->expire_date }}">
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="mb-3 col-6 pe-3">
                                        <label class="form-label">Mã giảm giá</label>
                                        <input type="text" name="code" class="form-control" id="couponCode"
                                            value="{{ $coupon->code }}" placeholder="Nhập mã giảm giá">
                                        <div id="suggestCode" class="mt-2" style="display: none;"></div>
                                    </div>
                                    <div class="mb-3 col-6 ">
                                        <label class="form-label">Loại giảm giá</label>
                                        <select name="discount_type" id="discount_type" class="form-control"
                                            value="{{ $coupon->discount_type }}">
                                            <option value="fixed"
                                                <?= $coupon->discount_type == 'fixed' ? 'selected' : '' ?>>Cố định</option>
                                            <option value="percentage"
                                                <?= $coupon->discount_type == 'percentage' ? 'selected' : '' ?>>Phần trăm
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="mb-3 col-3 pe-3">
                                        <label class="form-label">Số lượng sử dụng</label>
                                        <input type="int" name="max_usage" class="form-control"
                                            value="{{ $coupon->max_usage }}" placeholder="Nhập số lượng sử dụng">
                                    </div>
                                    <div class="mb-3 col-3 pe-3">
                                        <label class="form-label">Trạng thái</label>
                                        <select name="status" class="form-select" value="{{ old('status') }}">
                                            <option value="1" <?= $coupon->status == 1 ? 'selected' : '' ?>>Hoạt động
                                            </option>
                                            <option value="0" <?= $coupon->status == 0 ? 'selected' : '' ?>>Không hoạt
                                                động</option>
                                        </select>
                                        @error('status')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-3 pe-3 discount-fields">
                                        <label class="form-label">Giá trị giảm giá</label>
                                        <input type="number" name="discount_value" class="form-control"
                                            value="{{ $coupon->discount_value }}" placeholder="Nhập giá trị giảm giá">
                                    </div>
                                    <div class="mb-3 col-3 pe-3" id="discount_max_value_field">
                                        <label class="form-label">Giảm giá tối đa</label>
                                        <input type="number" name="discount_max_value" class="form-control"
                                            id="opendisabled" value="{{ $coupon->discount_max_value }}"
                                            placeholder="Nhập giá trị giảm giá tối đa"
                                            <?= $coupon->discount_type == 'fixed' ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" value="{{ $coupon->description }}" placeholder="Nhập mô tả"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Cập nhật</button>
                                <button type="reset" class="btn btn-info waves-effect waves-light"
                                    onclick="resetFilters()">Reset
                                </button>
                                <a href="{{ route('admin.coupons.index') }}" class="btn btn-dark">Danh sách</a>
                            </form>
                        </div>
                    </div><!-- end card -->
                </div>
                <!-- end col -->
            </div>
            <!-- end col -->
        </div>


    </div>
@endsection
@push('page-scripts')
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard-analytics.init.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Ban đầu ẩn các trường giá trị giảm giá và giảm giá tối đa
            // Khi thay đổi lựa chọn loại giảm giá
            $("#discount_type").change(function() {
                var discountType = $(this).val(); // Lấy giá trị loại giảm giá

                if (discountType === "fixed") {
                    $(".discount-fields").show();
                    $("#discount_max_value_field").show();
                    $("#opendisabled").prop("readonly", true);
                    $("#discount_max_value_field input").val(0);
                } else if (discountType === "percentage") {
                    $("#discount_max_value_field").show();
                    $(".discount-fields").show();
                    $("#discount_max_value_field").show();
                    $("#opendisabled").prop("readonly", false);
                } else {
                    $(".discount-fields").hide();
                    $("#discount_max_value_field").hide();
                }
            });
            $("form").submit(function() {
                var discountType = $("#discount_type").val();
                if (discountType === "fixed") {
                    // Nếu loại giảm giá là fixed, đảm bảo giá trị input discount_max_value là 0
                    $("#discount_max_value_field input").val(0);
                }
            });

            $('#suggestCode').hide();

            $(document).on('change', '#couponCode', function() {
                let data = $(this).val();

                $.ajax({
                    url: "{{ route('admin.coupons.suggestCode') }}",
                    type: 'GET',
                    data: {
                        code: data
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.length > 0) {
                            let suggestionsHtml =
                                '<div class="suggestion-container mt-2">Gợi ý: ';
                            response.forEach(function(code) {
                                suggestionsHtml += `<span class="suggestion-item p-2 text-primary" 
                                style="font-size: 0.8em !important; cursor: pointer;">
                                ${code}
                            </span>`;
                            });
                            suggestionsHtml += '</div>';

                            $('#suggestCode').html(suggestionsHtml).show();
                        } else {
                            $('#suggestCode').hide();
                        }
                    }
                });
            });

            $(document).on('click', '.suggestion-item', function() {
                $('#couponCode').val($(this).text().trim());
                $('#suggestCode').hide();
            });
        });
    </script>
@endpush
