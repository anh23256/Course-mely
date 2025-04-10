@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Cập nhật setting</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{ route('admin.settings.index') }}">Danh sách
                                    settings</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.settings.edit', $setting->id) }}">Cập
                                    nhật setting</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Cập nhật setting <span
                        class="text-danger">{{ $setting->key }}</span></h4>
                @if (session()->has('success') && session()->get('success') == true)
                    <span class="badge bg-primary text-end">Thao tác thành công</span>
                @endif
                @if (session()->has('error') && session()->get('error') != null)
                    <span class="badge bg-danger text-end">Thao tác không thành công</span>
                @endif
            </div><!-- end card header -->

            <div class="card-body">
                <div class="live-preview">

                    <form action="{{ route('admin.settings.update', $setting->id) }}" method="POST"
                        enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-md-12 position-relative">
                            <label for="key" class="form-label">Key settings</label>
                            <input type="text" class="form-control mb-2" name="key" id="key"
                                placeholder="Nhập key settings" value="{{ old('key', $setting->key) }}" autocomplete="off">
                            @error('key')
                                <span class="text-danger mt-2">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="label" class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control mb-2" name="label" placeholder="Nhập nhãn hiển thị"
                                value="{{ old('label', $setting->label) }}">
                        </div>

                        <div class="col-md-12">
                            <label for="type" class="form-label">Loại setting</label>
                            <select name="type" id="setting-type" class="form-control mb-2" required>
                                <option value="text" {{ old('type', $setting->type) === 'text' ? 'selected' : '' }}>Text
                                </option>
                                <option value="textarea" {{ old('type', $setting->type) === 'textarea' ? 'selected' : '' }}>
                                    Textarea</option>
                                <option value="image" {{ old('type', $setting->type) === 'image' ? 'selected' : '' }}>
                                    Image</option>
                            </select>
                        </div>

                        <div class="col-md-12 {{ old('type', $setting->type) === 'text' ? '' : 'd-none' }}"
                            id="value-text">
                            <label for="inputValue" class="form-label">Giá trị</label>
                            <input type="text" class="form-control mb-2" name="value" id="inputValue"
                                placeholder="Nhập giá trị" value="{{ old('value', $setting->value) }}">
                        </div>

                        <div class="col-md-12 {{ old('type', $setting->type) === 'textarea' ? '' : 'd-none' }}"
                            id="value-textarea">
                            <label class="form-label">Giá trị</label>
                            <textarea class="form-control mb-2" name="value" rows="3" placeholder="Nhập nội dung">{{ old('value', $setting->value) }}</textarea>
                        </div>

                        @if (isset($setting) && $setting->type === 'image' && $setting->value)
                            <div class="col-md-12">
                                <label class="form-label">Ảnh hiện tại</label>
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $setting->value) }}" class="mt-2"
                                        style="max-height: 150px;">
                                </div>
                            </div>
                        @endif

                        <div class="col-md-12 d-none" id="value-image">
                            <label class="form-label">Thay ảnh mới</label>
                            <input type="file" class="form-control mb-2" name="value" >
                        </div>


                        <div class="col-12 text-end">
                            <a class="btn btn-success" href="{{ route('admin.settings.index') }}">Quay lại</a>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
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
