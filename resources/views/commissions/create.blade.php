@extends('layouts.app')


@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="">{{ $subTitle }}</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">{{ $subTitle }}</h4>
                    </div>

                    <form action="{{ route('admin.commissions.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row">
                                    <div class="col-xxl-12 col-md-12">
                                        <div>
                                            <label for="placeholderInput" class="form-label">Cấp độ</label>
                                            <select class="form-select mb-3" aria-label="Default select example"
                                                    name="difficulty_level">
                                                <option value="">Chọn cấp độ</option>
                                                <option value="easy">Dễ</option>
                                                <option value="medium">Trung bình</option>
                                                <option value="difficult">Khó</option>
                                                <option value="very_difficult">Rất khó</option>
                                            </select>
                                        </div>
                                        @if ($errors->has('difficulty_level'))
                                            <span class="text-danger">{{ $errors->first('difficulty_level') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="system_percentage" class="form-label">Phần trăm của hệ thống</label>
                                        <select id="system_percentage" class="form-select mb-3" aria-label="Default select example" name="system_percentage">
                                            <option value="">Chọn phần trăm</option>
                                            <option value="10">10%</option>
                                            <option value="20">20%</option>
                                            <option value="30">30%</option>
                                            <option value="40">40%</option>
                                            <option value="50">50%</option>
                                            <option value="60">60%</option>
                                            <option value="70">70%</option>
                                            <option value="80">80%</option>
                                            <option value="90">90%</option>
                                        </select>
                                        @if ($errors->has('system_percentage'))
                                            <span class="text-danger">{{ $errors->first('system_percentage') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label for="instructor_percentage" class="form-label">Phần trăm của Giảng viên</label>
                                        <select id="instructor_percentage" class="form-select mb-3" aria-label="Default select example" name="instructor_percentage">
                                            <option value="">Chọn phần trăm</option>
                                            <option value="10">10%</option>
                                            <option value="20">20%</option>
                                            <option value="30">30%</option>
                                            <option value="40">40%</option>
                                            <option value="50">50%</option>
                                            <option value="60">60%</option>
                                            <option value="70">70%</option>
                                            <option value="80">80%</option>
                                            <option value="90">90%</option>
                                        </select>
                                        @if ($errors->has('instructor_percentage'))
                                            <span class="text-danger">{{ $errors->first('instructor_percentage') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">Thêm
                                        mới
                                    </button>
                                    <button type="reset" class="btn btn-info">Nhập lại</button>
                                    <a href="{{ route('admin.commissions.index') }}" type="button"
                                       class="btn btn-dark waves-effect waves-lightm ">Quay lại</a>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        $('#system_percentage').on('change', function() {
            var systemPercentage = parseInt($(this).val());
            if (systemPercentage) {
                var instructorPercentage = 100 - systemPercentage;
                $('#instructor_percentage').val(instructorPercentage);
            }
        });

        $('#instructor_percentage').on('change', function() {
            var instructorPercentage = parseInt($(this).val());
            if (instructorPercentage) {
                var systemPercentage = 100 - instructorPercentage;
                $('#system_percentage').val(systemPercentage);
            }
        });
    </script>
@endpush
