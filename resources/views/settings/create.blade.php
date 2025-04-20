@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Thêm mới settings</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{ route('admin.settings.index') }}">Danh sách
                                    settings</a></li>
                            <li class="breadcrumb-item active"><a href="{{ route('admin.settings.create') }}">Thêm mới
                                    settings</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Thêm mới settings</h4>
                @if (session()->has('error') && session()->get('error') != null)
                    <span class="badge bg-danger text-end">Thao tác không thành công</span>
                @endif
            </div><!-- end card header -->
            <div class="card-body">
                <div class="live-preview">
                    <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data"
                        class="row g-3">

                        @csrf

                        <div class="col-md-12 position-relative">

                            <label for="key" class="form-label">Key settings</label>
                            <input type="text" class="form-control mb-2" name="key" id="key"
                                placeholder="Nhập key settings" value="{{ old('key') }}" autocomplete="off">
                            <div id="key-suggestions" class="list-group bg-white border shadow-sm rounded w-100"
                                style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;">
                                {{-- Các gợi ý sẽ được render qua JavaScript --}}
                            </div>
                            @error('key')
                                <span class="text-danger mt-2">{{ $message }}</span>
                            @enderror

                        </div>

                        <div class="col-md-12">
                            <label for="label" class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control mb-2" name="label" placeholder="Nhập nhãn hiển thị"
                                value="{{ old('label') }}">
                        </div>

                        <div class="col-md-12">
                            <label for="type" class="form-label">Loại setting</label>
                            <select name="type" id="setting-type" class="form-control mb-2" required>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="image">Image</option>
                            </select>
                        </div>

                        <div class="col-md-12" id="value-text">
                            <label for="inputValue" class="form-label">Giá trị</label>
                            <input type="text" class="form-control mb-2" name="value" id="inputValue"
                                placeholder="Nhập giá trị" value="{{ old('value') }}">
                        </div>

                        <div class="col-md-12 d-none" id="value-textarea">
                            <label class="form-label">Giá trị</label>
                            <textarea class="form-control mb-2" name="value" rows="3" placeholder="Nhập nội dung">{{ old('value') }}</textarea>
                        </div>

                        <div class="col-md-12 d-none" id="value-image">
                            <label class="form-label">Ảnh</label>
                            <input type="file" class="form-control mb-2" name="value">
                        </div>

                        

                        <div class="col-12 text-end">
                            <a class="btn btn-success" href="{{ route('admin.settings.index') }}">Quay lại</a>
                            <button type="submit" class="btn btn-primary">Thêm</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelector = document.getElementById('setting-type');
            const valueText = document.getElementById('value-text');
            const valueTextarea = document.getElementById('value-textarea');
            const valueImage = document.getElementById('value-image');

            function toggleFields() {
                const selected = typeSelector.value;

                valueText.classList.add('d-none');
                valueTextarea.classList.add('d-none');
                valueImage.classList.add('d-none');

                if (selected === 'text') {
                    valueText.classList.remove('d-none');
                } else if (selected === 'textarea') {
                    valueTextarea.classList.remove('d-none');
                } else if (selected === 'image') {
                    valueImage.classList.remove('d-none');
                }
            }

            typeSelector.addEventListener('change', toggleFields);
            toggleFields(); // Gọi khi load lần đầu
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const keyInput = document.getElementById('key');
            const suggestionsBox = document.getElementById('key-suggestions');

            const keySuggestions = [
                'site_name',
                'site_logo',
                'footer_text'
            ];

            keyInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const matches = keySuggestions.filter(key => key.toLowerCase().includes(query));

                suggestionsBox.innerHTML = '';
                if (matches.length && query.length) {
                    matches.forEach(match => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = match;
                        item.addEventListener('click', function() {
                            keyInput.value = match;
                            suggestionsBox.style.display = 'none';
                        });
                        suggestionsBox.appendChild(item);
                    });
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });

            document.addEventListener('click', function(e) {
                if (!keyInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        });
    </script>
@endpush
