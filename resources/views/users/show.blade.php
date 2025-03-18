@extends('layouts.app')

@push('page-css')
    <style>
        .user-info-row {
            padding: 12px 0;
            border-bottom: 1px solid #eaeaea;
            transition: background-color 0.2s;
        }

        .user-info-row:last-child {
            border-bottom: none;
        }

        .user-info-row:hover {
            background-color: #f9f9f9;
        }

        .user-info-label {
            font-weight: 600;
            color: #495057;
        }

        .user-info-value {
            color: #212529;
        }

        .user-avatar-container {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }

        .user-avatar {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .user-card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .user-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
        }

        .user-name {
            font-weight: 600;
        }

        .action-buttons {
            margin-top: 2rem;
        }

        .action-buttons .btn {
            padding: 0.5rem 1.5rem;
            margin-right: 0.75rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row ">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Chi tiết người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="mdi mdi-home-outline me-1"></i>Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.' . (session('nameRouteUser')['role_name'] ?? 'clients') . '.index') }}">Danh sách người dùng</a></li>
                            <li class="breadcrumb-item active">Chi tiết người dùng</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="card user-card">
            <div class="card-header user-card-header py-3">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        Thông tin chi tiết: <span class="text-primary user-name">{{ $user->name }}</span>
                        @if ($user->status === 'active')
                            <span class="badge bg-success ms-2">Active</span>
                        @elseif($user->status === 'inactive')
                            <span class="badge bg-warning ms-2">Inactive</span>
                        @else
                            <span class="badge bg-danger ms-2">Block</span>
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
                    <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                        <div class="user-avatar-container text-center">
                            <div class="mb-4">
                                <img src="{{ $user->avatar }}" alt="Avatar của {{ $user->name }}" class="img-fluid rounded-circle user-avatar">
                            </div>
                            <h6 class="mb-2">{{ $user->name }}</h6>
                            <p class="text-muted mb-0">{{ $user->code }}</p>

                            <div class="mt-4">
                                <div class="d-flex justify-content-center mb-2">
                                    <span class="badge rounded-pill {{ $user->email_verified_at ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }} px-3 py-2">
                                        <i class="mdi {{ $user->email_verified_at ? 'mdi-check-circle' : 'mdi-alert-circle' }} me-1"></i>
                                        {{ $user->email_verified_at ? 'Email đã xác minh' : 'Email chưa xác minh' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9 col-md-8">
                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Mã người dùng:</div>
                            <div class="col-md-9 user-info-value">{{ $user->code }}</div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Họ và tên:</div>
                            <div class="col-md-9 user-info-value">{{ $user->name }}</div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Email:</div>
                            <div class="col-md-9 user-info-value">{{ $user->email }}</div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Số điện thoại:</div>
                            <div class="col-md-9 user-info-value">
                                @if(!empty($user->profile->phone))
                                    <i class="mdi mdi-phone me-1 text-muted"></i> {{ $user->profile->phone }}
                                @else
                                    <span class="text-muted fst-italic">Chưa có thông tin</span>
                                @endif
                            </div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Địa chỉ:</div>
                            <div class="col-md-9 user-info-value">
                                @if(!empty($user->profile->address))
                                    <i class="mdi mdi-map-marker me-1 text-muted"></i> {{ $user->profile->address }}
                                @else
                                    <span class="text-muted fst-italic">Chưa có thông tin</span>
                                @endif
                            </div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Kinh nghiệm:</div>
                            <div class="col-md-9 user-info-value">
                                @if(!empty($user->profile->experience))
                                    {{ $user->profile->experience }}
                                @else
                                    <span class="text-muted fst-italic">Chưa có thông tin</span>
                                @endif
                            </div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Tiểu sử:</div>
                            <div class="col-md-9 user-info-value">
                                @if(!empty($user->profile->bio))
                                    <div class="bio-content">
                                        {{ json_decode($user->profile->bio) }}
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Chưa có thông tin</span>
                                @endif
                            </div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Thời gian tạo:</div>
                            <div class="col-md-9 user-info-value">
                                <i class="mdi mdi-calendar me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i:s') }}
                            </div>
                        </div>

                        <div class="row user-info-row">
                            <div class="col-md-3 user-info-label">Cập nhật lần cuối:</div>
                            <div class="col-md-9 user-info-value">
                                <i class="mdi mdi-update me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i:s') }}
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="{{ route('admin.' . (session('nameRouteUser')['role_name'] ?? 'clients') . '.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>Quay lại
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">
                                <i class="mdi mdi-pencil me-1"></i>Chỉnh sửa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
