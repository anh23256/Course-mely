@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Thông tin cá nhân</h4>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xxl-12">
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Thành công!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Lỗi!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Cập nhật thông tin cá nhân</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center">
                                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                            <img src="{{ auth()->user()->avatar }}" alt="Ảnh đại diện"
                                                 class="rounded-circle avatar-xl img-thumbnail" id="profileImage">
                                            <div
                                                class="avatar-xs p-0 rounded-circle profile-photo-edit position-absolute end-0 bottom-0">
                                                <span class="avatar-title rounded-circle bg-light text-body"
                                                      id="avatarEditButton">
                                                    <i class="ri-camera-fill"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <h5 class="fs-16 mb-1">{{ auth()->user()->name }}</h5>
                                        <p class="text-muted mb-0">Quản trị viên</p>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="flex-shrink-0">
                                                <i class="ri-secure-payment-line text-muted fs-24"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="card-title mb-1">Bảo mật</h5>
                                                <p class="text-muted mb-0">Cập nhật mật khẩu định kỳ để tăng tính bảo
                                                    mật</p>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center mb-4">
                                            <div class="flex-shrink-0">
                                                <i class="ri-mail-line text-muted fs-24"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="card-title mb-1">Email</h5>
                                                <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="ri-calendar-2-line text-muted fs-24"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="card-title mb-1">Ngày tham gia</h5>
                                                <p class="text-muted mb-0">{{ auth()->user()->created_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <form action="" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="file" id="profile-img-file-input" name="avatar" class="d-none"
                                           accept="image/*">

                                    <div class="card border">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="fullname" class="form-label">Họ và tên</label>
                                                <input type="text" class="form-control" id="fullname" name="name"
                                                       value="{{ auth()->user()->name }}"
                                                       placeholder="Nhập họ và tên của bạn">
                                                @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email"
                                                       value="{{ auth()->user()->email }}" readonly disabled>
                                                <small class="form-text text-muted">Email không thể thay đổi.</small>
                                            </div>

                                            @error('avatar')
                                            <div class="mt-2">
                                                <span class="text-danger">{{ $message }}</span>
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="card mt-4 border">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Thay đổi mật khẩu</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Mật khẩu hiện
                                                    tại</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5"
                                                           id="current_password"
                                                           name="current_password" placeholder="Nhập mật khẩu hiện tại">
                                                    <button
                                                        class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                        type="button"><i class="ri-eye-fill align-middle"></i></button>
                                                </div>
                                                @error('current_password')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5" id="new_password"
                                                           name="password" placeholder="Nhập mật khẩu mới">
                                                    <button
                                                        class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                        type="button"><i class="ri-eye-fill align-middle"></i></button>
                                                </div>
                                                @error('password')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu
                                                    mới</label>
                                                <div class="position-relative auth-pass-inputgroup">
                                                    <input type="password" class="form-control pe-5"
                                                           id="password_confirmation"
                                                           name="password_confirmation"
                                                           placeholder="Xác nhận mật khẩu mới">
                                                    <button
                                                        class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                        type="button"><i class="ri-eye-fill align-middle"></i></button>
                                                </div>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox"
                                                       id="password-change-confirm">
                                                <label class="form-check-label" for="password-change-confirm">
                                                    Để trống nếu bạn không muốn thay đổi mật khẩu
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-light ms-1">Hủy</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileImgInput = document.getElementById('profile-img-file-input');
            profileImgInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById('profileImage').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            document.querySelectorAll('.password-addon').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault(); // Prevent form submission
                    const inputField = this.parentElement.querySelector('input');
                    const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
                    inputField.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('ri-eye-fill');
                    this.querySelector('i').classList.toggle('ri-eye-off-fill');
                });
            });

            document.getElementById('avatarEditButton').addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                document.getElementById('profile-img-file-input').click();
            });
        });
    </script>

    <script src="{{ asset('assets/libs/particles.js/particles.js') }}"></script>
    <script src="{{ asset('assets/js/pages/particles.app.js') }}"></script>
    <script src="{{ asset('assets/js/pages/passowrd-create.init.js') }}"></script>
@endpush
