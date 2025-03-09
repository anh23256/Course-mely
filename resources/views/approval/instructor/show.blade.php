@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="profile-foreground position-relative mx-n4 mt-n4">
            <div class="profile-wid-bg">
                <img src="{{ asset('assets/images/profile-bg.jpg') }}" alt="" class="profile-wid-img" />
            </div>
        </div>
        <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
            <div class="row g-4">
                <div class="col-md-auto">
                    <div style="width: 100px; height: 100px;" class="avatar-lg rounded-circle ">
                        <img src="{{ $approval->user->avatar }}" alt="" class="h-100 object-fit-cover rounded">
                    </div>
                </div>
                <!--end col-->
                <div class="col">
                    <div class="p-2">
                        <h3 class="text-white mb-1">{{ $approval->user->name ?? '' }}</h3>
                        <p class="text-white text-opacity-75">Owner & Founder</p>
                        <div class="hstack text-white-50 gap-1">
                            <div class="me-2"><i
                                    class="ri-map-pin-user-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                                {{ $approval->user->profile->address ?? 'Chưa có thông tin' }}
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-12 col-lg-auto order-last order-lg-0">
                    <div class="row text text-white-50 text-center">
                        <div class="col-lg-6 col-4">
                            <div class="p-2">
                                <h4 class="text-white mb-1">24.3K</h4>
                                <p class="fs-14 mb-0">Followers</p>
                            </div>
                        </div>
                        <div class="col-lg-6 col-4">
                            <div class="p-2">
                                <h4 class="text-white mb-1">1.3K</h4>
                                <p class="fs-14 mb-0">Following</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->

            </div>
            <!--end row-->
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div>
                    <div class="d-flex profile-wrapper">
                        <!-- Nav tabs -->
                        <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                    <i class="ri-airplay-fill d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Tổng quan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#qa" role="tab">
                                    <i class="ri-price-tag-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">QA System</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#certificates" role="tab">
                                    <i class="ri-folder-4-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Chứng chỉ</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#test-case" role="tab">
                                    <i class="ri-folder-4-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Tiêu chí</span>
                                </a>
                            </li>
                        </ul>
                        <div class="flex-shrink-0">
                            @if ($approval->status === 'pending')
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.approvals.instructors.approve', $approval->id) }}"
                                        method="POST" id="approveForm">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-primary approve " type="button">Phê duyệt</button>
                                    </form>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        Từ chối
                                    </button>

                                    <div id="rejectModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Từ chối người hướng
                                                        dẫn</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form id="rejectForm"
                                                    action="{{ route('admin.approvals.instructors.reject', $approval->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="rejectReason" class="form-label">Lý do từ
                                                                chối</label>
                                                            <textarea placeholder="Nhập lý do từ chối..." class="form-control" id="rejectNote" name="note" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light"
                                                            data-bs-dismiss="modal">Huỷ
                                                        </button>
                                                        <button type="button" class="btn btn-primary"
                                                            id="submitRejectForm">Xác nhận
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($approval->status === 'rejected')
                                <button type="button" class="btn btn-danger ">
                                    Người hướng dẫn không đủ điều kiện
                                </button>
                            @else
                                <button type="button" class="btn btn-success ">
                                    Người hướng dẫn đã được phê duyệt
                                </button>
                            @endif

                        </div>
                    </div>
                    <div class="tab-content pt-4 text-muted">
                        <div class="tab-pane active" id="overview-tab" role="tabpanel">
                            <div class="row">
                                <div class="col-xxl-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-5">Mức độ hoàn thiện hồ sơ</h5>
                                            <div class="progress animated-progress custom-progress progress-label">
                                                <div class="progress-bar bg-danger" role="progressbar"
                                                    style="width: {{ $score }}%"
                                                    aria-valuenow="{{ $score }}" aria-valuemin="0"
                                                    aria-valuemax="100" id="progressInstructorStyle">
                                                    <div class="label" id="progressInstructor">{{ $score }}%</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Thông tin cá nhân</h5>
                                            <div class="table-responsive">
                                                <table class="table table-borderless mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Họ tên:</th>
                                                            <td class="text-muted">
                                                                {{ $approval->user->name ?? 'Chưa có thông tin' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">SĐT:</th>
                                                            <td class="text-muted">
                                                                {{ $approval->user->profile->phone ?? 'Chưa có thông tin' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">E-mail:</th>
                                                            <td class="text-muted">
                                                                {{ $approval->user->email ?? 'Chưa có thông tin' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Exp:</th>
                                                            <td class="text-muted">
                                                                {{ $approval->user->profile->experience ?? 'Chưa có thông tin' }}
                                                                năm kinh nghiệm
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Địa chỉ:</th>
                                                            <td class="text-muted">
                                                                {{ $approval->user->profile->address ?? 'Chưa có thông tin' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Ngày tạo</th>
                                                            <td class="text-muted">
                                                                {{ $approval->created_at->format('d/m/Y') }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div><!-- end card body -->
                                    </div><!-- end card -->
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Mạng xã hội</h5>
                                            <div class="d-flex flex-wrap gap-2">
                                                @php
                                                    $socials = json_decode($approval->user->profile->bio ?? '[]', true);
                                                    $socials = is_array($socials) ? $socials : [];
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
                                                @if (!empty($socials))
                                                    @foreach ($socials as $key => $url)
                                                        @if (array_key_exists($key, $icon) && $url)
                                                            <div>
                                                                <a href="{{ $url }}" class="avatar-xs d-block"
                                                                    target="_blank">
                                                                    <span
                                                                        class="avatar-title rounded-circle fs-16 bg-body text-body">
                                                                        <i class="{{ $icon[$key] }}"></i>
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <div class="avatar-xs">
                                                        <span class="avatar-title rounded-circle fs-16 bg-body text-body">
                                                            <i class="ri-global-fill"></i>
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div><!-- end card body -->
                                    </div><!-- end card -->

                                </div>
                                <!--end col-->
                                <div class="col-xxl-9">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Giới thiệu bản thân</h5>
                                            <p>
                                                {{ $approval->user->profile->about_me ?? '' }}
                                            </p>
                                        </div>
                                        <!--end card-body-->
                                    </div><!-- end card -->

                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Kỹ năng</h5>
                                            <div class="d-flex flex-wrap gap-2 fs-15">
                                                <a href="javascript:void(0);"
                                                    class="badge bg-primary-subtle text-primary">Photoshop</a>
                                                <a href="javascript:void(0);"
                                                    class="badge bg-primary-subtle text-primary">illustrator</a>
                                                <a href="javascript:void(0);"
                                                    class="badge bg-primary-subtle text-primary">HTML</a>
                                                <a href="javascript:void(0);"
                                                    class="badge bg-primary-subtle text-primary">CSS</a>
                                                <a href="javascript:void(0);"
                                                    class="badge bg-primary-subtle text-primary">Javascript</a>
                                                <a href="javascript:void(0);"
                                                    class="badge bg-primary-subtle text-primary">Php</a>
                                                <a href="javascript:void(0);"
                                                    class="badge bg-primary-subtle text-primary">Python</a>
                                            </div>
                                        </div><!-- end card body -->
                                    </div><!-- end card -->
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane fade" id="qa" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    @php
                                        $qaSystems = json_decode($approval->user->profile->qa_systems ?? '[]', true);
                                        $qaSystems = is_array($qaSystems) ? $qaSystems : [];
                                    @endphp
                                    <div class="accordion" id="default-accordion-example">
                                        @if (!empty($qaSystems))
                                            @foreach ($qaSystems as $index => $qaSystem)
                                                <div class="accordion-item mb-3">
                                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                                        <button class="accordion-button" type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse{{ $index }}"
                                                            aria-expanded="false"
                                                            aria-controls="collapse{{ $index }}">
                                                            {{ $qaSystem['question'] }}
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $index }}"
                                                        class="accordion-collapse collapse"
                                                        aria-labelledby="heading{{ $index }}"
                                                        data-bs-parent="#default-accordion-example">
                                                        <div class="accordion-body">
                                                            @if (count($qaSystem['selected_options']) > 1)
                                                                @foreach ($qaSystem['options'] as $optionIndex => $option)
                                                                    <div class="form-check  mb-3">
                                                                        <input type="checkbox"
                                                                            name="option[{{ $loop->parent->index }}][]"
                                                                            value="{{ $optionIndex }}"
                                                                            @if (in_array($optionIndex, $qaSystem['selected_options'])) checked @endif
                                                                            @if (!in_array($optionIndex, $qaSystem['selected_options'])) disabled @endif
                                                                            id="checkbox-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-input">
                                                                        <label
                                                                            for="checkbox-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-label">{{ $option }}</label>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                @foreach ($qaSystem['options'] as $optionIndex => $option)
                                                                    <div class="form-check mb-3">
                                                                        <input type="radio"
                                                                            name="option[{{ $loop->parent->index }}]"
                                                                            value="{{ $optionIndex }}"
                                                                            @if (in_array($optionIndex, $qaSystem['selected_options'])) checked @endif
                                                                            @if (!in_array($optionIndex, $qaSystem['selected_options'])) disabled @endif
                                                                            id="radio-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-input">
                                                                        <label
                                                                            for="radio-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-label">{{ $option }}</label>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                </div>
                            </div>
                            <!--end card-->
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane fade" id="certificates" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <h5 class="card-title flex-grow-1 mb-0">Danh sách chứng chỉ</h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="table-responsive">
                                                <table class="table table-borderless align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <td>#</td>
                                                            <th scope="col">File Name</th>
                                                            <th scope="col">Type</th>
                                                            <th scope="col">Size</th>
                                                            <th scope="col">Upload Date</th>
                                                            <th scope="col">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $certificates = json_decode(
                                                                $approval->user->profile->certificates ?? '[]',
                                                                true,
                                                            );
                                                            $certificates = is_array($certificates)
                                                                ? $certificates
                                                                : [];
                                                        @endphp
                                                        @if (!empty($certificates))
                                                            @foreach ($certificates as $certificate)
                                                                @php
                                                                    $fileExtension = pathinfo(
                                                                        $certificate,
                                                                        PATHINFO_EXTENSION,
                                                                    );
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="avatar-sm">
                                                                                @if ($fileExtension === 'pdf')
                                                                                    <iframe src="{{ $certificate }}"
                                                                                        width="100%"
                                                                                        height="400px"></iframe>
                                                                                @else
                                                                                    <img class="w-100"
                                                                                        src="{{ $certificate }}"
                                                                                        alt="File Image">
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        @if ($fileExtension === 'pdf')
                                                                            PDF File
                                                                        @else
                                                                            Image File
                                                                        @endif
                                                                    </td>
                                                                    <td>4.57 MB</td>
                                                                    <td>12 Dec 2021</td>
                                                                    <td>
                                                                        <div class="dropdown">
                                                                            <a href="javascript:void(0);"
                                                                                class="btn btn-light btn-icon"
                                                                                id="dropdownMenuLink15"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="true">
                                                                                <i class="ri-equalizer-fill"></i>
                                                                            </a>
                                                                            <ul class="dropdown-menu dropdown-menu-end"
                                                                                aria-labelledby="dropdownMenuLink15">
                                                                                <li>
                                                                                    <a class="dropdown-item"
                                                                                        href="javascript:void(0);">
                                                                                        <i
                                                                                            class="ri-eye-fill me-2 align-middle text-muted"></i>
                                                                                        View
                                                                                    </a>
                                                                                </li>
                                                                                <li>
                                                                                    <a class="dropdown-item"
                                                                                        href="{{ $certificate }}"
                                                                                        download>
                                                                                        <i
                                                                                            class="ri-download-2-fill me-2 align-middle text-muted"></i>
                                                                                        Download
                                                                                    </a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="test-case" role="tabpanel">
                            <div class="tab-pane" id="test-case" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Danh sách tiêu chí kiểm duyệt giảng viên</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="accordion" id="criteriaAccordionInstructor">
                                                    
                                                    <div class="accordion-item">
                                                        
                                                        <div id="criteriaCollapseOne"
                                                            class="accordion-collapse collapse show"
                                                            aria-labelledby="criteriaHeadingOne"
                                                            data-bs-parent="#criteriaAccordion">
                                                            <div class="accordion-body">
                                                                <div class="row" id="criteria_instructor">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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

@push('page-scripts')
    <script>
        $(document).ready(function() {
            $(".approve").click(function(event) {
                event.preventDefault();

                Swal.fire({
                    title: "Phê duyệt người hướng dẫn ?",
                    text: "Bạn có chắc chắn muốn phê duyệt người hướng dẫn này?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Phê duyệt",
                    cancelButtonText: "Huỷ"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#approveForm").submit();
                    }
                });
            });

            $('#submitRejectForm').on('click', function() {
                const note = $('#rejectNote').val();

                if (note.trim() === '') {
                    Swal.fire({
                        text: "Vui lòng nhập lý do từ chối.",
                        icon: 'warning'
                    });
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: $('#rejectForm').attr('action'),
                    data: {
                        _method: 'PUT',
                        note,
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Thao tác thành công!',
                            text: 'Lý do từ chối đã được ghi nhận.',
                            icon: 'success'
                        }).then(() => {
                            $('#rejectModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(error) {
                        Swal.fire({
                            title: 'Thao tác thất bại!',
                            text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                            icon: 'error'
                        });
                    }
                });
            });
        });

        function getCriteriaApprovalInstructor() {
            $.ajax({
                type: 'GET',
                url: "http://127.0.0.1:8000/api/get-validate-instructor/" + "{{ $approval->user->code }}",
                beforeSend: function () {
                    $("#criteria_instructor")
                        .html(`
                <span class="col-md-12 mt-2 text-center">
                    <i class='bx bx-loader bx-spin' style="color: gray; font-size: 20px;"></i>
                    Đang tải...
                </span>
            `);
                },
                success: function (response) {
                    console.log(response);
                    if (response.data) {
                        let progress = parseFloat(response.data.progress).toFixed(2) ?? 0;
                        $('#progressInstructor').html(progress + '%');
                        $('#progressInstructorStyle').css('width', progress + '%');
                        $('#progressInstructorStyle').attr('aria-valuenow', progress);

                        function renderCriteria(containerId, criteria) {
                            let container = $(containerId);
                            container.empty();

                            if (!criteria.status) {
                                criteria.errors.forEach(error => {
                                    container.append(`
                                <span class="col-md-6 mt-2">
                                    <i class='bx bx-x-circle' style="color: red;"></i> ${error}
                                </span>
                            `);
                                });
                            }
                            criteria.pass.forEach(pas => {
                                container.append(`
                            <span class="col-md-6 mt-2">
                                <i class='bx bx-check-circle' style="color: green;"></i> ${pas}
                            </span>
                        `);
                            });
                        }

                        let completion = response.data;
                        renderCriteria("#criteria_instructor", completion);
                    }
                }
            });
        }
        getCriteriaApprovalInstructor();
    </script>
@endpush
