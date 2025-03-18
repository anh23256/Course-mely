@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 fw-bold text-primary">Cập nhật người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none"><i class="ri-dashboard-line me-1"></i>Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.' . (session('nameRouteUser')['role_name'] ?? 'clients') . '.index') }}" class="text-decoration-none">Danh sách người dùng</a></li>
                            <li class="breadcrumb-item active">Cập nhật thông tin</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Status alerts -->
        @if (session()->has('success') && session()->get('success') == true)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-double-line me-2"></i> Cập nhật thông tin người dùng thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session()->has('error') && session()->get('error') != null)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i> Thao tác không thành công! Vui lòng thử lại.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-12">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-soft-primary">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-primary text-white rounded-circle fs-18">
                                        <i class="ri-user-settings-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">Thông tin người dùng: <span class="fw-bold text-dark">{{ $user->name }}</span></h5>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="row g-4">
                            @csrf
                            @method('PUT')

                            <div class="col-lg-3">
                                <div class="card border shadow-none text-center">
                                    <div class="card-body p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <img src="{{ $user->avatar }}" alt="Avatar" id="avatarDisplay" class="img-fluid rounded-circle border border-4 border-white shadow">
                                            <a href="javascript:void(0);" id="triggerAvatarUpload" class="position-absolute bottom-0 end-0 avatar-xs bg-primary text-white rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Thay đổi avatar">
                                                <i class="ri-camera-line avatar-title text-white fs-16 d-flex align-items-center justify-content-center h-100"></i>
                                            </a>
                                        </div>

                                        <h5 class="mb-1 text-truncate">{{ $user->name }}</h5>
                                        <p class="text-muted mb-3">{{ $user->roles->first()->name }}</p>

                                        <div class="d-flex gap-2 justify-content-center mb-3">
                                            <div class="badge rounded-pill bg-soft-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'warning' : 'danger') }} px-3">
                                                <span class="text-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'warning' : 'danger') }}">
                                                    <i class="ri-checkbox-circle-fill me-1"></i>
                                                    {{ Str::ucfirst($user->status) }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="hstack gap-2 justify-content-center">
                                            <button type="button" class="btn btn-soft-danger btn-sm" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                                <i class="ri-lock-password-line me-1"></i> Reset mật khẩu
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-9">
                                <div class="card border shadow-none mb-0">
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="fullname" placeholder="Nhập họ và tên" value="{{ $user->name }}">
                                                    <label for="fullname"><i class="ri-user-3-line text-muted me-1"></i> Họ và tên</label>
                                                    @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="inputEmail4" placeholder="Nhập email" value="{{ $user->email }}">
                                                    <label for="inputEmail4"><i class="ri-mail-line text-muted me-1"></i> Email</label>
                                                    @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <select name="status" class="form-select @error('status') is-invalid @enderror" id="userStatus">
                                                        <option value="">Chọn trạng thái</option>
                                                        <option @selected($user->status === 'active') value="active">Active</option>
                                                        <option @selected($user->status === 'inactive') value="inactive">Inactive</option>
                                                        <option @selected($user->status === 'blocked') value="blocked">Blocked</option>
                                                    </select>
                                                    <label for="userStatus"><i class="ri-checkbox-circle-line text-muted me-1"></i> Trạng thái tài khoản</label>
                                                    @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <select name="role" class="form-select @error('role') is-invalid @enderror" id="userRole">
                                                        <option value="">Chọn vai trò</option>
                                                        @foreach ($roles as $role)
                                                            <option @selected($user->roles->first()->name == $role) value="{{ $role }}">{{ Str::ucfirst($role) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="userRole"><i class="ri-shield-user-line text-muted me-1"></i> Vai trò người dùng</label>
                                                    @error('role')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title mb-0">Xác thực email</h6>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="form-check form-switch form-switch-success">
                                                            <input class="form-check-input" type="checkbox" role="switch" name="email_verified" id="email_verified" value="1" @checked($user->email_verified_at != null)>
                                                            <label class="form-check-label" for="email_verified">{{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hidden file input -->
                                            <input type="file" name="avatar" id="imageInput" accept="image/*" class="d-none">
                                            @error('avatar')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="hstack gap-2 justify-content-end">
                                    <a class="btn btn-light" href="{{ route('admin.' . (session('nameRouteUser')['role_name'] ?? 'clients') . '.index') }}">
                                        <i class="ri-arrow-left-line align-bottom me-1"></i> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line align-bottom me-1"></i> Cập nhật thông tin
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-soft-danger p-3">
                    <h5 class="modal-title">Reset mật khẩu người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn reset mật khẩu cho người dùng <strong>{{ $user->name }}</strong>?</p>
                    <p class="text-muted mb-0">Hành động này sẽ gửi email đến người dùng với hướng dẫn đặt lại mật khẩu.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="button" class="btn btn-danger">Xác nhận reset</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('imageInput');
            const avatarDisplay = document.getElementById('avatarDisplay');
            const triggerAvatarUpload = document.getElementById('triggerAvatarUpload');

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            triggerAvatarUpload.addEventListener('click', () => {
                imageInput.click();
            });

            imageInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = () => {
                        avatarDisplay.src = reader.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            const emailVerifiedSwitch = document.getElementById('email_verified');
            emailVerifiedSwitch.addEventListener('change', function() {
                const label = this.nextElementSibling;
                label.textContent = this.checked ? 'Đã xác thực' : 'Chưa xác thực';
            });
        });
    </script>

    <script src="{{ asset('assets/libs/particles.js/particles.js') }}"></script>
    <script src="{{ asset('assets/js/pages/particles.app.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/passowrd-create.init.js') }}"></script>
@endpush
