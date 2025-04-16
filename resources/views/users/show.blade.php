@extends('layouts.app')

@push('page-css')
    <style>
        .user-card {
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border: none;
            overflow: hidden;
        }

        .user-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
            padding: 1.25rem 1.5rem;
        }

        .user-avatar-container {
            background-color: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            padding: 2rem 1.5rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .user-avatar-container:hover {
            transform: translateY(-5px);
        }

        .user-avatar {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .user-name {
            font-weight: 700;
            margin-top: 1.5rem;
            font-size: 1.25rem;
            color: #333;
        }

        .user-code {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .verification-badge {
            margin-top: 1.25rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .user-info-section {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            height: 100%;
        }

        .user-info-row {
            padding: 1rem 0;
            border-bottom: 1px solid #eaeaea;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
        }

        .user-info-row:last-child {
            border-bottom: none;
        }

        .user-info-row:hover {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding-left: 10px;
        }

        .user-info-label {
            font-weight: 600;
            color: #495057;
        }

        .user-info-value {
            color: #212529;
        }

        .user-info-icon {
            margin-right: 8px;
            color: #6c757d;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }

        .status-badge i {
            margin-right: 0.35rem;
            font-size: 0.8rem;
        }

        .bio-content {
            line-height: 1.6;
            padding: 0.75rem;
            background-color: #f9f9f9;
            border-radius: 8px;
            border-left: 3px solid #6c757d;
        }

        .action-buttons {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            padding: 0.5rem 1.25rem;
            margin-right: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            margin-right: 0.5rem;
        }

        .page-title-box {
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item a {
            display: flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .user-avatar {
                width: 120px;
                height: 120px;
            }

            .user-avatar-container {
                margin-bottom: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Chi tiết người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">
                                    <i class="mdi mdi-home-outline me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a
                                    href="{{ route('admin.' . (session('nameRouteUser')['role_name'] ?? 'clients') . '.index') }}">
                                    Danh sách người dùng
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Chi tiết người dùng</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card user-card">
                    <div class="card-header user-card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">
                                <i class="mdi mdi-account-circle me-1"></i>
                                Thông tin chi tiết:
                                <span class="text-primary">{{ $user->name }}</span>

                                @if ($user->status === 'active')
                                    <span class="status-badge bg-success text-white ms-2">
                                        <i class="mdi mdi-check-circle"></i>Hoạt động
                                    </span>
                                @elseif($user->status === 'inactive')
                                    <span class="status-badge bg-warning text-white ms-2">
                                        <i class="mdi mdi-clock-outline"></i>Chưa kích hoạt
                                    </span>
                                    <span class="status-badge bg-danger text-white ms-2">
                                        <i class="mdi mdi-block-helper"></i>Đã khóa
                                    </span>
                                @endif
                            </h5>

                            <div>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-pencil me-1"></i>Chỉnh sửa
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <!-- User Avatar Column -->
                            <div class="col-lg-3 col-md-4">
                                <div class="user-avatar-container">
                                    <img src="{{ $user->avatar }}" alt="Avatar của {{ $user->name }}"
                                        class="img-fluid rounded-circle user-avatar">
                                    <h5 class="user-name">{{ $user->name }}</h5>
                                    <p class="user-code">{{ $user->code }}</p>

                                    <div
                                        class="verification-badge {{ $user->email_verified_at ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }}">
                                        <i
                                            class="mdi {{ $user->email_verified_at ? 'mdi-check-circle' : 'mdi-alert-circle' }} me-1"></i>
                                        {{ $user->email_verified_at ? 'Đã xác minh' : 'Email chưa xác minh' }}
                                    </div>

                                    <div class="mt-4">
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                            class="btn btn-sm btn-outline-primary w-100 mb-2">
                                            <i class="mdi mdi-pencil me-1"></i>Chỉnh sửa thông tin
                                        </a>

                                        @if ($user->status !== 'active')
                                            <button class="btn btn-sm btn-outline-success w-100">
                                                <i class="mdi mdi-check-circle me-1"></i>Kích hoạt tài khoản
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-warning w-100">
                                                <i class="mdi mdi-lock me-1"></i>Tạm khóa tài khoản
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- User Info Column -->
                            <div class="col-lg-9 col-md-8">
                                <div class="user-info-section">
                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-identifier user-info-icon"></i>Mã người dùng:
                                        </div>
                                        <div class="col-md-9 user-info-value">{{ $user->code }}</div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-account user-info-icon"></i>Họ và tên:
                                        </div>
                                        <div class="col-md-9 user-info-value">{{ $user->name }}</div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-email user-info-icon"></i>Email:
                                        </div>
                                        <div class="col-md-9 user-info-value">{{ $user->email }}</div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-phone user-info-icon"></i>Số điện thoại:
                                        </div>
                                        <div class="col-md-9 user-info-value">
                                            @if (!empty($user->profile->phone))
                                                {{ $user->profile->phone }}
                                            @else
                                                <span class="text-muted fst-italic">Chưa có thông tin</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-map-marker user-info-icon"></i>Địa chỉ:
                                        </div>
                                        <div class="col-md-9 user-info-value">
                                            @if (!empty($user->profile->address))
                                                {{ $user->profile->address }}
                                            @else
                                                <span class="text-muted fst-italic">Chưa có thông tin</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-briefcase user-info-icon"></i>Kinh nghiệm:
                                        </div>
                                        <div class="col-md-9 user-info-value">
                                            @if (!empty($user->profile->experience))
                                                {{ $user->profile->experience }}
                                            @else
                                                <span class="text-muted fst-italic">Chưa có thông tin</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-text-box user-info-icon"></i>Tiểu sử:
                                        </div>
                                        <div class="col-md-9 user-info-value">
                                            @if (!empty($user->profile->bio))
                                                <div>
                                                    @php
                                                        $socials = json_decode($user->profile->bio, true) ?? '';
                                                        $socials = is_array($socials) ? $socials : [];
                                                        $filledSocials = array_filter($socials);
                                                    @endphp
                                                    <div class="card-body">
                                                        @php
                                                            $icon = [
                                                                'facebook' => 'ri-facebook-fill',
                                                                'twitter' => 'ri-twitter-fill',
                                                                'instagram' => 'ri-instagram-fill',
                                                                'linkedin' => 'ri-linkedin-fill',
                                                                'github' => 'ri-github-fill',
                                                                'dribbble' => 'ri-dribbble-fill',
                                                                'youtube' => 'ri-youtube-fill',
                                                                'website' => 'ri-global-fill',
                                                            ];
                                                        @endphp

                                                        @if (!empty($filledSocials))
                                                            <div class="d-flex flex-wrap gap-3">
                                                                @foreach ($socials as $key => $url)
                                                                    @if (array_key_exists($key, $icon) && $url)
                                                                        <a href="{{ $url }}"
                                                                            class="btn btn-soft-primary btn-sm"
                                                                            target="_blank">
                                                                            <i class="{{ $icon[$key] }} me-1"></i>
                                                                            {{ ucfirst($key) }}
                                                                        </a>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="text-center py-3">
                                                                <div class="avatar-sm mx-auto mb-3">
                                                                    <span
                                                                        class="avatar-title rounded-circle bg-light text-body fs-20">
                                                                        <i class="ri-links-line"></i>
                                                                    </span>
                                                                </div>
                                                                <p class="text-muted mb-0">Người dùng chưa thêm liên kết
                                                                    mạng xã hội</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">Chưa có thông tin</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-account-group user-info-icon"></i>Tổng số học viên:
                                        </div>
                                        <div class="col-md-9 user-info-value">
                                            @if ($totalStudents > 0)
                                                {{ $totalStudents }}
                                            @else
                                                <span class="text-muted fst-italic">Chưa có học viên</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-calendar user-info-icon"></i>Thời gian tạo:
                                        </div>
                                        <div class="col-md-9 user-info-value">
                                            {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>

                                    <div class="row user-info-row">
                                        <div class="col-md-3 user-info-label">
                                            <i class="mdi mdi-update user-info-icon"></i>Cập nhật lần cuối:
                                        </div>
                                        <div class="col-md-9 user-info-value">
                                            {{ \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>
                                    @if ($user->hasRole('instructor'))

                                        <div class="mt-4">
                                            <h5 class="mb-3 fw-bold">
                                                📚 Danh sách khóa học của giảng viên <span
                                                    class="text-primary">{{ $user->name }}</span>
                                            </h5>

                                            @if ($courses->count() > 0)
                                                <div class="table-responsive shadow-sm rounded border bg-white">
                                                    <table class="table table-hover mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>STT</th>
                                                                <th>Ảnh</th>
                                                                <th>Tên khóa học</th>
                                                                <th>Số học viên</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($courses as $index => $course)
                                                                <tr>
                                                                    <td>{{ $courses->firstItem() + $index }}</td>
                                                                    <td>
                                                                        <img src="{{ $course->thumbnail ?? '/images/placeholder.png' }}"
                                                                            alt="thumbnail" class="img-thumbnail"
                                                                            style="width: 80px; height: 50px; object-fit: cover;">
                                                                    </td>
                                                                    <td>{{ $course->name }}</td>
                                                                    <td>{{ $course->total_student }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Pagination -->
                                                <div class="mt-3">
                                                    {{ $courses->links() }}
                                                </div>
                                            @else
                                                <div class="alert alert-info mt-3">
                                                    Không có khóa học nào được tạo bởi giảng viên này.
                                                </div>
                                            @endif
                                        </div>




                                    @endif

                                    <div class="action-buttons">
                                        <a href="{{ route('admin.' . (session('nameRouteUser')['role_name'] ?? 'clients') . '.index') }}"
                                            class="btn btn-light">
                                            <i class="mdi mdi-arrow-left"></i>Quay lại
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">
                                            <i class="mdi mdi-pencil"></i>Chỉnh sửa
                                        </a>
                                        <button type="button" class="btn btn-info">
                                            <i class="mdi mdi-history"></i>Lịch sử hoạt động
                                        </button>
                                        <button type="button" class="btn btn-danger">
                                            <i class="mdi mdi-delete"></i>Xóa tài khoản
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
