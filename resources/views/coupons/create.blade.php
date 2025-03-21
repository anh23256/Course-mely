@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        #couponCode {
            text-transform: uppercase;
        }

        #couponCode::placeholder {
            text-transform: none;
        }

        .form-section {
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #495057;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
        }

        .suggestion-item {
            display: inline-block;
            margin-right: 5px;
            background-color: #f1f5ff;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .suggestion-item:hover {
            background-color: #3b82f6;
            color: white !important;
        }

        .btn-submit {
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.15rem rgba(59, 130, 246, 0.25);
        }
    </style>
@endpush

@php
    $title = 'Thêm mới coupon';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Quản lí mã giảm giá</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Danh sách mã giảm giá</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-primary ">
                        <h4 class="card-title mb-0 text-white">{{ $subTitle ?? 'Thêm mới mã giảm giá' }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.coupons.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-section">
                                <div class="section-title">Thông tin cơ bản</div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tên mã giảm giá <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-tag-outline"></i></span>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name') }}" placeholder="Nhập tên mã giảm giá">
                                        </div>
                                        @error('name')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mã giảm giá <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-barcode"></i></span>
                                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="couponCode"
                                                   value="{{ old('code') }}" placeholder="Nhập mã giảm giá">
                                        </div>
                                        <div id="suggestCode" class="mt-2" style="display: none;"></div>
                                        @error('code')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-calendar-start"></i></span>
                                            <input type="text" name="start_date" class="form-control flatpickr-date @error('start_date') is-invalid @enderror"
                                                   value="{{ old('start_date') }}" placeholder="Chọn ngày bắt đầu">
                                        </div>
                                        @error('start_date')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-calendar-end"></i></span>
                                            <input type="text" name="expire_date" class="form-control flatpickr-date @error('expire_date') is-invalid @enderror"
                                                   value="{{ old('expire_date') }}" placeholder="Chọn ngày kết thúc">
                                        </div>
                                        @error('expire_date')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Số lượng sử dụng <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-numeric"></i></span>
                                            <input type="number" name="max_usage" class="form-control @error('max_usage') is-invalid @enderror"
                                                   value="{{ old('max_usage') }}" placeholder="Nhập số lượng sử dụng">
                                        </div>
                                        @error('max_usage')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-title">Cấu hình giảm giá</div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-percent"></i></span>
                                            <select name="discount_type" id="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                                                <option value="">Chọn loại giảm giá</option>
                                                <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Cố định (VNĐ)</option>
                                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                                            </select>
                                        </div>
                                        @error('discount_type')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3 discount-fields" style="display: none;">
                                        <label class="form-label">Giá trị giảm giá <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-currency-usd"></i></span>
                                            <input type="number" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror"
                                                   value="{{ old('discount_value') }}" placeholder="Nhập giá trị giảm giá">
                                        </div>
                                        @error('discount_value')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3" id="discount_max_value_field" style="display: none;">
                                        <label class="form-label">Giảm giá tối đa <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-currency-usd"></i></span>
                                            <input type="number" name="discount_max_value" class="form-control @error('discount_max_value') is-invalid @enderror"
                                                   value="{{ old('discount_max_value') }}" placeholder="Nhập giá trị giảm giá tối đa">
                                        </div>
                                        @error('discount_max_value')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-toggle-switch"></i></span>
                                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                                <option value="">Chọn trạng thái</option>
                                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                                            </select>
                                        </div>
                                        @error('status')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="section-title">Mô tả</div>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                                                  placeholder="Nhập mô tả về mã giảm giá">{{ old('description') }}</textarea>
                                        @error('description')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <div>
                                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-dark">
                                        <i class="mdi mdi-format-list-bulleted me-1"></i> Danh sách
                                    </a>
                                </div>
                                <div>
                                    <button type="reset" class="btn btn-info waves-effect waves-light" onclick="resetFilters()">
                                        <i class="mdi mdi-refresh me-1"></i> Làm mới
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-submit ms-2">
                                        <i class="mdi mdi-content-save me-1"></i> Lưu mã giảm giá
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('page-scripts')
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!-- Flatpickr -->
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard-analytics.init.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo flatpickr cho date inputs
            $(".flatpickr-date").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true,
                minDate: "today"
            });

            // Hiển thị trường giảm giá dựa vào loại giảm giá đã chọn từ trước
            var selectedDiscountType = "{{ old('discount_type') }}";
            if (selectedDiscountType === "fixed") {
                $(".discount-fields").show();
                $("#discount_max_value_field").hide();
            } else if (selectedDiscountType === "percentage") {
                $(".discount-fields").show();
                $("#discount_max_value_field").show();
            }

            // Khi thay đổi lựa chọn loại giảm giá
            $("#discount_type").change(function() {
                var discountType = $(this).val(); // Lấy giá trị loại giảm giá

                if (discountType === "fixed") {
                    $(".discount-fields").show();
                    $("#discount_max_value_field").hide();
                } else if (discountType === "percentage") {
                    $(".discount-fields").show();
                    $("#discount_max_value_field").show();
                } else {
                    $(".discount-fields").hide();
                    $("#discount_max_value_field").hide();
                }
            });

            // Xử lý gợi ý mã giảm giá
            $('#suggestCode').hide();

            $(document).on('input', '#couponCode', function() {
                let data = $(this).val();

                if (data.length < 3) {
                    $('#suggestCode').hide();
                    return;
                }

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
                                '<div class="suggestion-container mt-2"><i class="mdi mdi-lightbulb-outline text-warning"></i> Gợi ý: ';
                            response.forEach(function(code) {
                                suggestionsHtml += `<span class="suggestion-item p-2 text-primary"
                                            style="font-size: 0.85em !important; cursor: pointer;">
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

        function resetFilters() {
            $('.form-control, .form-select').val('');
            $(".discount-fields").hide();
            $("#discount_max_value_field").hide();
        }
    </script>
@endpush
