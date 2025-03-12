@extends('layouts.app')

@section('title', 'Quản lý vai trò')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">{{ $subTitle ?? '' }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.qa-systems.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Tiêu đề</label>
                                        <input type="text" class="form-control mb-3" id="title" name="title"
                                               placeholder="Nhập tiêu đề" value="{{ old('title') }}">
                                        @error('title')
                                        <span class="text-danger mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Mô tả</label>
                                        <textarea placeholder="Nhập mô tả" name="description"
                                                  class="form-control mb-3">{{ old('description') }}</textarea>
                                        @error('description')
                                        <span class="text-danger mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Câu hỏi</label>
                                        <input type="text" class="form-control mb-3" id="question" name="question"
                                               placeholder="Nhập câu hỏi" value="{{ old('question') }}">
                                        @error('question')
                                        <span class="text-danger mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <label class="form-label" for="name">Lựa chọn</label>
                                            <button type="button" class="btn btn-sm btn-primary" id="add-option">Thêm
                                                lựa chọn
                                            </button>
                                        </div>
                                        <div id="options-container">
                                            @if(old('options'))
                                                @foreach(old('options') as $index => $option)
                                                    <div class="input-group mb-3 option-item">
                                                        <span class="input-group-text">{{ $index + 1 }}</span>
                                                        <input type="text" class="form-control" name="options[]" value="{{ $option }}" placeholder="Nhập lựa chọn">
                                                        <button type="button" class="btn btn-danger remove-option">Xóa</button>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group mb-3 option-item">
                                                    <span class="input-group-text">1</span>
                                                    <input type="text" class="form-control " name="options[]" placeholder="Nhập lựa chọn">
                                                </div>
                                                <div class="input-group mb-3 option-item">
                                                    <span class="input-group-text">2</span>
                                                    <input type="text" class="form-control" name="options[]" placeholder="Nhập lựa chọn">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Loại câu hỏi</label>
                                        <select name="answer_type" class="form-control mb-3">
                                            <option {{ old('type') == 'multiple' ? 'selected' : '' }} value="multiple">
                                                Chọn nhiều
                                            </option>
                                            <option {{ old('type') == 'single' ? 'selected' : '' }} value="single">Chọn
                                                một
                                            </option>
                                        </select>
                                        @error('answer_type')
                                        <span class="text-danger mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Trạng thái</label>
                                        <select name="status" class="form-control mb-3">
                                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Kích hoạt
                                            </option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Không kích
                                                hoạt
                                            </option>
                                        </select>
                                        @error('status')
                                        <span class="text-danger mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mt-4 action-buttons">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i> Lưu câu hỏi
                                        </button>
                                        <button type="reset" class="btn btn-light">
                                            <i class="ri-refresh-line me-1"></i> Nhập lại
                                        </button>
                                        <a href="{{ route('admin.qa-systems.index') }}" class="btn btn-secondary">
                                            <i class="ri-arrow-left-line me-1"></i> Quay lại
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endSection

@push('page-scripts')
    <script>
        $(document).ready(function () {
            let optionCount = 2;

            $('#add-option').click(function () {
                optionCount++;
                let optionHtml = `
                <div class="input-group mb-2 option-item">
                    <span class="input-group-text">${optionCount}</span>
                    <input type="text" class="form-control" name="options[]" placeholder="Nhập lựa chọn">
                    <button type="button" class="btn btn-danger remove-option">Xóa</button>
                </div>`;
                $('#options-container').append(optionHtml);
            });

            $(document).on('click', '.remove-option', function () {
                let button = $(this);

                Swal.fire({
                    title: "Bạn có chắc chắn?",
                    text: "Xoá lựa chọn này!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Đồng ý!",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.closest('.option-item').remove();
                        optionCount--;
                        updateOptionNumbers();
                    }
                });
            });

            function updateOptionNumbers() {
                $('#options-container .option-item').each(function (index) {
                    $(this).find('.input-group-text').text(index + 1);
                });
            }
        });
    </script>

@endpush
