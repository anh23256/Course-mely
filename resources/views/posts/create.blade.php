@extends('layouts.app')

@push('page-css')
    <link rel="stylesheet" href="{{ asset('vendor/laraberg/css/laraberg.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $subTitle ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Thông tin bài viết
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Tiêu đề</label>
                                <input type="name" class="form-control mb-2" placeholder="Nhập tiêu đề..."
                                       value="{{ old('title') }}" name="title">
                                @error('title')
                                <span class="text-danger mt-2">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Hình ảnh</label>
                                <input type="file" name="thumbnail" id="imageInput" accept="image/*"
                                       class="form-control">
                                <img class="mt-2" id="imagePreview"
                                     style="display: none; max-width: 100%; max-height: 300px;">
                                @error('thumbnail')
                                <span class="text-danger mt-2">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="d-flex justify-content-between mb-2">
                                    <label class="form-label">Mô tả bài viết</label>
                                    <button type="button" class="btn btn-sm btn-primary" id="openAiModal"
                                            data-bs-toggle="modal" data-bs-target="#aiModal">
                                        Sử dụng AI
                                    </button>
                                </div>
                                <textarea id="ckeditor-classic" name="description" class="form-control" id="" cols="30"
                                          rows="10">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="formGroupExampleInput">Nội dung</label>
                                <textarea class="mb-3" id="laraberg" name="content"
                                          hidden>{{ old('content') }}</textarea>
                                @error('content')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Tuỳ chỉnh
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Ngày xuất bản</label>
                                <div class="input-group">
                                    <input type="datetime-local" name="published_at" id="datepicker-publish-input"
                                           class="form-control" placeholder="yy/mm/dd hh:mm" data-provider="flatpickr"
                                           data-date-format="Y/m/d" data-enable-time value="{{ old('published_at') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Danh mục
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <select class="select2-categories form-control" name="category_id"
                                        data-placeholder="Chọn danh mục">
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ in_array($category->id, (array) old('category_id', [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Tags
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <select class="select2-tags form-control" name="tags[]" data-placeholder="Chọn tags"
                                        multiple="multiple">
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->name }}"
                                            {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary ">Xuất bản</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal AI-->
    <div class="modal fade" id="aiModal" tabindex="-1" aria-labelledby="aiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aiModalLabel">Gợi ý từ AI <span id="ai-title"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Chọn một trong các tùy chọn sau:</p>
                    <ul class="list-group cursor-pointer" id="ai-options">
                        <li class="list-group-item ai-option" data-type="text">Văn bản </li>
                        <li class="list-group-item ai-option" data-type="image">Hình ảnh</li>
                        <li class="list-group-item ai-option" data-type="audio">Giọng nói</li>
                    </ul>
                    <textarea class="bg-dark form-control text-white rounded resize-horizontal"
                              style="display: none;margin-top: 15px; height: 250px;"
                              id="ai-content"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary isClosed" data-bs-dismiss="modal"  id="isClosed" >Đóng</button>
                    <button type="button" class="btn btn-primary" id="aiConfirmBtn">Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://unpkg.com/react@17.0.2/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@17.0.2/umd/react-dom.production.min.js"></script>

    <script src="{{ asset('vendor/laraberg/js/laraberg.js') }}"></script>
    <script>
        $(document).ready(function () {
            let editorInstance;

            Laraberg.init('laraberg');

            $('#imageInput').on('change', function (e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function () {
                        $('#imagePreview').attr('src', reader.result).show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
                }
            });

            $('form').on('submit', function () {
                var content = Laraberg.getContent('laraberg');
                $('textarea[name="content"]').val(content);
            });

            $('.select2-categories').select2({
                placeholder: 'Chọn danh mục'
            });

            $('.select2-tags').select2({
                tags: true,
                tokenSeparators: [','],
                placeholder: 'Chọn thẻ đính kèm'
            });

            ClassicEditor.create($('#ckeditor-classic')[0])
                .then(editor => {
                    editorInstance = editor;
                    editor.ui.view.editable.element.style.height = "200px";
                })
                .catch(console.error);

            $('#openAiModal').click(function () {
                $('#ai-options').show();
                $('#ai-content').hide();
                $('#aiConfirmBtn').prop('disabled', true);
            });

            $('.ai-option').click(function () {
                $('.ai-option').removeClass('bg-primary text-white').prop('disabled', false);
                $(this).addClass('bg-primary text-white');
                $('.ai-option').not(this).prop('disabled', true);

                const aiType = $(this).data('type');
                const title = $('input[name="title"]').val();
                if (title.trim() === '') {
                    Swal.fire({
                        title: 'Vui lòng nhập tiêu đề bài viết để sử dụng trợ lý AI!',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });

                    $('#aiModal').modal('hide');

                    return;
                }

                if (aiType === 'text') {
                    const prompt = 'Viết một đoạn văn ngắn (200 ký tự) về bài viết với tiêu đề: ' + title;
                    fetchAIContent(aiType, prompt);
                }
            });

            function fetchAIContent(type, prompt) {
                $('#ai-content').html('Đang chờ AI...').show();

                $.ajax({
                    url: 'http://127.0.0.1:8000/api/v1/ai/generate-text',
                    method: 'POST',
                    data: {
                        type,
                        title: prompt
                    },
                    success: function (response) {
                        const aiText = response.data;
                        let index = 0;
                        $('#ai-content').html('');
                        const interval = setInterval(function () {
                            if (index < aiText.length) {
                                $('#ai-content').append(aiText.charAt(index));
                                index++;
                            } else {
                                clearInterval(interval);
                                $('#aiConfirmBtn').prop('disabled', false);
                                $('.ai-option').prop('disabled', false);
                            }
                        }, 50);
                    },
                    error: function () {
                        $('#ai-content').html('<p>Không thể tải dữ liệu từ AI, vui lòng thử lại!</p>');
                        $('.ai-option').prop('disabled', false);
                        $('#isClosed').prop('disabled', false);
                    }
                });
            }

            function resetAiOptions() {
                $('.ai-option').removeClass('bg-primary text-white').prop('disabled', false);
            }

            $('#aiConfirmBtn').click(function () {
                const aiText = $('#ai-content').text();
                if (editorInstance) {
                    editorInstance.setData(aiText);
                }
                $('#aiModal').modal('hide');
                resetAiOptions();
            });

            $('#aiModal').on('hidden.bs.modal', function () {
                resetAiOptions();
            });
        });
    </script>
@endpush
