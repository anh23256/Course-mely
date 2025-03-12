@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        #couponCode {
            text-transform: uppercase;
        }

        #couponCode::placeholder {
            text-transform: none;
        }
    </style>
@endpush
@php
    $title = 'Thêm mới coupon';
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ $subTitle ?? '' }}</h4>
                    </div>
                    <div class="card-body">
                        <div>
                            <form action="{{ route('admin.coupons.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="d-flex">
                                    <div class="mb-3 col-6 pe-3">
                                        <label class="form-label">Tên mã giảm giá</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name') }}" placeholder="Nhập tên mã giảm giá">
                                        @error('name')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-3 pe-3">
                                        <label class="form-label">Ngày bắt đầu</label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ old('start_date') }}">
                                        @error('start_date')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-3">
                                        <label class="form-label">Ngày kết thúc</label>
                                        <input type="date" name="expire_date" class="form-control"
                                            value="{{ old('expire_date') }}">
                                        @error('expire_date')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="mb-3 col-6 pe-3">
                                        <label class="form-label">Mã giảm giá</label>
                                        <input type="text" name="code" class="form-control" id="couponCode"
                                            value="{{ old('code') }}" placeholder="Nhập mã giảm giá">
                                        <div id="suggestCode" class="mt-2" style="display: none;"></div>
                                        @error('code')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-6 ">
                                        <label class="form-label">Loại giảm giá</label>
                                        <select name="discount_type" id="discount_type" class="form-control"
                                            value="{{ old('discount_type') }}">
                                            <option value="">Chọn loại giảm giá</option>
                                            <option value="fixed">Cố định</option>
                                            <option value="percentage">Phần trăm</option>
                                        </select>
                                        @error('discount_type')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="mb-3 col-3 pe-3">
                                        <label class="form-label">Số lượng sử dụng</label>
                                        <input type="int" name="used_count" class="form-control"
                                            value="{{ old('used_count') }}" placeholder="Nhập số lượng sử dụng">
                                        @error('used_count')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-3 pe-3">
                                        <label class="form-label">Trạng thái</label>
                                        <select name="status" class="form-select" value="{{ old('status') }}">
                                            <option value="" selected>Chọn trạng thái</option>
                                            <option value="1">Hoạt động</option>
                                            <option value="0">Không hoạt động</option>
                                        </select>
                                        @error('status')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-3 pe-3 discount-fields" style="display: none;">
                                        <label class="form-label">Giá trị giảm giá</label>
                                        <input type="number" name="discount_value" class="form-control"
                                            value="{{ old('discount_value') }}" placeholder="Nhập giá trị giảm giá">
                                        @error('discount_value')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-3 pe-3" id="discount_max_value_field" style="display: none;">
                                        <label class="form-label">Giảm giá tối đa</label>
                                        <input type="number" name="discount_max_value" class="form-control"
                                            value="{{ old('discount_max_value') }}"
                                            placeholder="Nhập giá trị giảm giá tối đa">
                                        @error('discount_max_value')
                                            <div class="text-danger mt-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" value="{{ old('description') }}" placeholder="Nhập mô tả"></textarea>
                                    @error('description')
                                        <div class="text-danger mt-3">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-1"></i> Lưu mã giảm giá
                                </button>
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
            $(".discount-fields").hide();
            $("#discount_max_value_field").hide();
            // Khi thay đổi lựa chọn loại giảm giá
            $("#discount_type").change(function() {
                var discountType = $(this).val(); // Lấy giá trị loại giảm giá

                if (discountType === "fixed") {
                    $(".discount-fields").show();
                    $("#discount_max_value_field").hide();
                } else if (discountType === "percentage") {
                    $("#discount_max_value_field").show();
                    $(".discount-fields").show();
                } else {
                    $(".discount-fields").hide();
                    $("#discount_max_value_field").hide();
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
