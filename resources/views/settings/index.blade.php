@extends('layouts.app')
@section('title', $title)
@push('page-css')
    <style>
        .pdf-container {
            width: 100%;
            height: 200px;
            max-width: 400px;
            margin: auto;
            text-align: center;
            overflow: hidden;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .pdf-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        canvas {
            width: 100% !important;
            height: auto !important;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Danh sách settings</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{ route('admin.settings.index') }}">Danh sách
                                    settings</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <!-- social-customer -->
        <div class="row mb-2 ">
            @foreach ($templateCertificates as $templateCertificate)
                <div class="col-12 col-sm-6 col-md-3 cursor-pointer">
                    <div class="card text-center">
                        <div class="pdf-container">
                            <div class="card-body">
                                <picture>
                                    <source srcset="{{ $templateCertificate['url_image_template'] }}"
                                        type="image/svg+xml" />
                                    <img src="image source" class="img-fluid" alt="image desc" />
                                </picture>
                                <input type="radio" name="template_certificate" class="mt-3 template_certificate"
                                    value="{{ $templateCertificate['id'] }}" @checked($templateCertificate['status'] == 1)>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Danh sách settings</h4>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <a href="{{ route('admin.settings.create') }}">
                                            <button type="button" class="btn btn-primary add-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Thêm mới
                                            </button>
                                        </a>
                                        <button class="btn btn-danger" id="deleteSelected">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <input type="text" name="searchsetting" class="form-control search"
                                                id="search-options" placeholder="Tìm kiếm..."
                                                value="{{ old('searchsetting') }}">
                                            <button class="ri-search-line search-icon m-0 p-0 border-0"
                                                style="background: none;"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">
                                                <input type="checkbox" id="checkAll">
                                            </th>
                                            <th>ID</th>
                                            <th>Tiêu đề</th>
                                            <th>Key</th>
                                            <th>Value</th>
                                            <th>Ngày tạo</th>
                                            <th>Ngày cập nhật</th>
                                            <th>Hành Động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($settings as $setting)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" id="checkAll" type="checkbox"
                                                            name="settingId" value="{{ $setting->id }}">
                                                    </div>
                                                </th>
                                                
                                                <td class="id">
                                                    <a class="fw-medium link-primary">#{{ $setting->id }}</a>
                                                </td>
                                                    
                                                <td>{{ $setting->label ?? '' }}</td>
                                                <td class="customer_name">{{ $setting->key ?? '' }}</td>
                                                
                                                <td>
                                                    @if ($setting->type === 'image' && $setting->value)
                                                        <img src="{{ asset('storage/' . $setting->value) }}" alt="setting image"
                                                             style="height: 50px; border-radius: 4px;">
                                                    @else
                                                        {{ \Str::limit($setting->value, 50) }}
                                                    @endif
                                                </td>
                                                <td>{{ $setting->created_at != null ? date_format($setting->created_at, 'd/m/Y') : 'NULL' }}
                                                </td>
                                                <td>{{ $setting->updated_at != null ? date_format($setting->updated_at, 'd/m/Y') : 'NULL' }}
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.settings.edit', $setting->id) }}">
                                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                                <span class="ri-edit-box-line"></span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('admin.settings.destroy', $setting->id) }}"
                                                            class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                            <span class="ri-delete-bin-7-line"></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end">
                                {{ $settings->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <!-- end col -->
        </div>
    @endsection

    @push('page-scripts')
        <script src="{{ asset('assets/libs/list.pagination.js/list.pagination.min.js') }}"></script>
        <script>
            $('#checkAll').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('input[name="settingId"]').prop('checked', isChecked);
            });

            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $("#deleteSelected").click(function(event) {
                    event.preventDefault();

                    var selectedsettings = [];

                    $('input[name="settingId"]:checked').each(function() {
                        selectedsettings.push($(this).val());
                    });

                    if (selectedsettings.length == 0) {
                        Swal.fire({
                            title: 'Chọn ít nhất 1 settings để xóa',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    let deleteUrl = "{{ route('admin.settings.destroy', ':settingID') }}".replace(':settingID',
                        selectedsettings.join(','));

                    Swal.fire({
                        title: "Bạn có muốn xóa ?",
                        text: "Bạn sẽ không thể khôi phục dữ liệu khi xoá!!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Đồng ý!!",
                        cancelButtonText: "Huỷ!!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "DELETE",
                                url: deleteUrl,
                                success: function(data) {
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            title: 'Thao tác thành công!',
                                            text: data.message,
                                            icon: 'success'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                location.reload();
                                            }
                                        });
                                    } else if (data.status === 'error') {
                                        Swal.fire({
                                            title: "Thao tác thất bại!",
                                            text: data.message,
                                            icon: 'error'
                                        });
                                    }
                                },
                                error: function(data) {
                                    console.log('Error:', data);
                                    Swal.fire({
                                        title: "Thao tác thất bại!",
                                        text: data.responseJSON.message,
                                        icon: 'error'
                                    });
                                }
                            });
                        }
                    });
                });
                $(document).on('click', '.template_certificate', function() {
                    let templateId = $(this).val();

                    if (!templateId) return;

                    $.ajax({
                        url: "/admin/settings/certificates/" + templateId,
                        type: 'PUT'
                    });
                });
            });
        </script>
    @endpush
