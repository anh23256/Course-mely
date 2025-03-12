@extends('layouts.app')

@push('page-css')
    <link rel="stylesheet" href="{{ asset('vendor/laraberg/css/laraberg.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        .form-card {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .form-card:hover {
            box-shadow: 0 0 25px rgba(0,0,0,0.1);
        }
        .form-card .card-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 20px;
        }
        .form-card .card-body {
            padding: 20px;
        }
        .card-title {
            font-weight: 600;
            font-size: 16px;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #495057;
        }
        .form-control {
            border-radius: 6px;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }
        .btn-publish {
            padding: 10px 24px;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.3s;
        }
        .select2-container .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            height: 42px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 6px 8px;
        }
        .thumbnail-preview-container {
            background-color: #f8f9fa;
            border: 1px dashed #ced4da;
            border-radius: 6px;
            text-align: center;
            padding: 20px;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .thumbnail-preview-container:hover {
            background-color: #f1f3f5;
        }
        .ai-option {
            cursor: pointer;
            transition: all 0.2s;
            padding: 12px 16px;
        }
        .ai-option:hover {
            background-color: #f8f9fa;
        }
        .ai-badge {
            background: linear-gradient(45deg, #6366F1, #8B5CF6);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
        .ai-content-area {
            background-color: #2b2d3e;
            color: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            height: 250px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            line-height: 1.6;
        }
        .section-divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 15px 0;
        }
    </style>
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

        <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" id="postForm">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card form-card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="ri-article-line me-1"></i> Thông tin bài viết
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="title" class="form-label">Tiêu đề</label>
                                <input type="text" id="title" class="form-control"
                                       placeholder="Nhập tiêu đề bài viết..."
                                       value="{{ old('title') }}" name="title">
                                @error('title')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Hình ảnh đại diện</label>
                                <input type="file" name="thumbnail" id="imageInput" accept="image/*"
                                       class="form-control">
                                <div class="thumbnail-preview-container" id="thumbnailContainer">
                                    <img id="imagePreview" style="display: none; max-width: 100%; max-height: 250px;">
                                    <div id="uploadPlaceholder">
                                        <i class="ri-image-add-line fs-3 mb-2"></i>
                                        <p>Tải lên hình ảnh đại diện cho bài viết</p>
                                    </div>
                                </div>
                                @error('thumbnail')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Mô tả bài viết</label>
                                    <button type="button" class="btn btn-sm btn-primary d-flex align-items-center"
                                            id="openAiModal" data-bs-toggle="modal" data-bs-target="#aiModal">
                                        <i class="ri-robot-line me-1"></i> Sử dụng AI
                                    </button>
                                </div>
                                <textarea id="ckeditor-classic" name="description" class="form-control">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="laraberg" class="form-label">Nội dung bài viết</label>
                                <textarea id="laraberg" name="content" hidden>{{ old('content') }}</textarea>
                                @error('content')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card form-card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="ri-settings-3-line me-1"></i> Xuất bản
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="published">Xuất bản ngay</option>
                                    <option value="draft">Lưu nháp</option>
                                    <option value="scheduled">Hẹn giờ xuất bản</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngày xuất bản</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                                    <input type="datetime-local" name="published_at"
                                           class="form-control"
                                           value="{{ old('published_at') ?? now()->format('Y-m-d\TH:i') }}">
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-publish">
                                    <i class="ri-send-plane-fill me-1"></i> Xuất bản bài viết
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card form-card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="ri-folder-line me-1"></i> Danh mục
                            </h4>
                        </div>
                        <div class="card-body">
                            <select class="select2-categories form-control" name="category_id" data-placeholder="Chọn danh mục">
                                <option></option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ in_array($category->id, (array) old('category_id', [])) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card form-card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="ri-price-tag-3-line me-1"></i> Thẻ
                            </h4>
                        </div>
                        <div class="card-body">
                            <select class="select2-tags form-control" name="tags[]"
                                    data-placeholder="Chọn hoặc tạo thẻ mới" multiple="multiple">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->name }}"
                                        {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-2 text-muted small">
                                <i class="ri-information-line me-1"></i> Nhập tên thẻ và nhấn Enter để tạo thẻ mới
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="aiModal" tabindex="-1" aria-labelledby="aiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aiModalLabel">
                        <i class="ri-robot-line me-1"></i> Trợ lý AI
                        <span class="ai-badge">Beta</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Chọn công cụ AI bạn muốn sử dụng:</p>

                    <div class="row g-3" id="ai-options">
                        <div class="col-md-4">
                            <div class="card ai-option" data-type="text">
                                <div class="card-body p-3 text-center">
                                    <i class="ri-text fs-2 mb-2"></i>
                                    <h6 class="mb-1">Tạo văn bản</h6>
                                    <p class="mb-0 small text-muted">Tạo mô tả tự động</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card ai-option" data-type="image">
                                <div class="card-body p-3 text-center">
                                    <i class="ri-image-line fs-2 mb-2"></i>
                                    <h6 class="mb-1">Hình ảnh</h6>
                                    <p class="mb-0 small text-muted">Gợi ý hình ảnh</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card ai-option" data-type="audio">
                                <div class="card-body p-3 text-center">
                                    <i class="ri-mic-line fs-2 mb-2"></i>
                                    <h6 class="mb-1">Giọng nói</h6>
                                    <p class="mb-0 small text-muted">Chuyển văn bản thành giọng nói</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider my-4"></div>

                    <div id="ai-progress" style="display: none;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>AI đang xử lý...</span>
                        </div>
                    </div>

                    <textarea class="ai-content-area form-control" id="ai-content" style="display: none;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="isClosed">
                        <i class="ri-close-line me-1"></i> Đóng
                    </button>
                    <button type="button" class="btn btn-primary" id="aiConfirmBtn" disabled>
                        <i class="ri-check-line me-1"></i> Áp dụng nội dung
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

            Laraberg.init('laraberg', {
                height: '600px',
                mediaUpload: handleMediaUpload
            });

            function handleMediaUpload(file) {
                return new Promise((resolve) => {
                    resolve({
                        id: new Date().getTime(),
                        url: URL.createObjectURL(file)
                    });
                });
            }

            $('#imageInput').on('change', function (e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function () {
                        $('#imagePreview').attr('src', reader.result).show();
                        $('#uploadPlaceholder').hide();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
                    $('#uploadPlaceholder').show();
                }
            });

            $('#postForm').on('submit', function () {
                var content = Laraberg.getContent();
                $('textarea[name="content"]').val(content);
            });

            $('.select2-categories').select2({
                placeholder: 'Chọn danh mục',
                allowClear: true,
                width: '100%',
                dropdownParent: $('.select2-categories').parent()
            });

            $('.select2-tags').select2({
                tags: true,
                tokenSeparators: [','],
                placeholder: 'Chọn hoặc tạo thẻ mới',
                width: '100%',
                dropdownParent: $('.select2-tags').parent()
            });

            ClassicEditor.create($('#ckeditor-classic')[0], {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo'],
            })
                .then(editor => {
                    editorInstance = editor;
                    editor.ui.view.editable.element.style.height = "200px";
                })
                .catch(console.error);

            $('#openAiModal').click(function () {
                $('#ai-options').show();
                $('#ai-content').hide();
                $('#ai-progress').hide();
                $('#aiConfirmBtn').prop('disabled', true);
            });

            $('.ai-option').click(function () {
                $('.ai-option').removeClass('border-primary').find('.card-body').removeClass('bg-light');
                $(this).addClass('border-primary').find('.card-body').addClass('bg-light');

                const aiType = $(this).data('type');
                const title = $('input[name="title"]').val();

                if (title.trim() === '') {
                    Swal.fire({
                        title: 'Cần nhập tiêu đề',
                        text: 'Vui lòng nhập tiêu đề bài viết để sử dụng trợ lý AI!',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });

                    $('#aiModal').modal('hide');
                    return;
                }

                if (aiType === 'text') {
                    const prompt = 'Viết một đoạn văn ngắn (200-250 ký tự) giới thiệu về bài viết với tiêu đề: ' + title;
                    fetchAIContent(aiType, prompt);
                }
            });

            function fetchAIContent(type, prompt) {
                $('#ai-content').hide();
                $('#ai-progress').show();

                $.ajax({
                    url: 'http://127.0.0.1:8000/api/v1/cloudflare/generate-text',
                    method: 'POST',
                    data: {
                        type,
                        title: prompt
                    },
                    success: function (response) {
                        const aiText = response.data;
                        $('#ai-progress').hide();
                        $('#ai-content').show();

                        let index = 0;
                        $('#ai-content').html('');

                        const interval = setInterval(function () {
                            if (index < aiText.length) {
                                $('#ai-content').append(aiText.charAt(index));
                                index++;
                                $('#ai-content').scrollTop($('#ai-content')[0].scrollHeight);
                            } else {
                                clearInterval(interval);
                                $('#aiConfirmBtn').prop('disabled', false);
                            }
                        }, 25);
                    },
                    error: function (error) {
                        $('#ai-progress').hide();
                        $('#ai-content').show().html('<p class="text-danger">Không thể tải dữ liệu từ AI, vui lòng thử lại sau!</p>');
                        $('#isClosed').prop('disabled', false);
                    }
                });
            }

            function resetAiOptions() {
                $('.ai-option').removeClass('border-primary').find('.card-body').removeClass('bg-light');
            }

            $('#aiConfirmBtn').click(function () {
                const aiText = $('#ai-content').text();
                if (editorInstance) {
                    editorInstance.setData(aiText);
                }
                $('#aiModal').modal('hide');
                resetAiOptions();

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });

            $('#aiModal').on('hidden.bs.modal', function () {
                resetAiOptions();
            });
        });
    </script>
@endpush
