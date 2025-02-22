@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="profile-foreground position-relative mx-n4 mt-n4">
            <div class="profile-wid-bg">
                <img src="{{ asset('assets/images/profile-bg.jpg') }}" alt="" class="profile-wid-img"/>
            </div>
        </div>
        <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
            <div class="row g-4">
                <div class="col-md-auto">
                    <div class="avatar-md">
                        <div class="avatar-title bg-white rounded-circle">
                            <img src="{{ $approval->course->thumbnail }}" alt=""
                                 class="rounded-circle img-fluid h-100 object-fit-cover">
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2">
                        <h3 class="text-white mb-1">
                            {{ $approval->course->name }}
                            @if ($approval->status === 'pending')
                                <span class="badge badge-label bg-warning ">
                                    <i class="mdi mdi-circle-medium"></i> Chờ phê duyệt
                                </span>
                            @elseif($approval->status === 'approved')
                                <span class="badge badge-label bg-success"><i class="mdi mdi-circle-medium"></i> Đã
                                    duyệt</span>
                            @else
                                <span class="badge badge-label bg-danger"><i class="mdi mdi-circle-medium"></i>Đã từ
                                    chối</span>
                            @endif
                        </h3>
                        <div class="hstack gap-3 flex-wrap mt-3 text-white">
                            <div>
                                <i class="ri-map-pin-user-line me-1"></i>
                                Người hướng dẫn : {{ $approval->course->user->name ?? '' }}
                            </div>
                            <div class="vr"></div>
                            <div>
                                <i class="ri-building-line align-bottom me-1"></i>
                                Danh mục : {{ $approval->course->category->name ?? '' }}
                            </div>
                            <div class="vr"></div>
                            <div>Ngày tạo : <span class="fw-medium">{{ $approval->course->created_at ?? '' }}</span>
                            </div>
                            <div class="vr"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-auto order-last order-lg-0">
                    @if ($approval->status === 'pending')
                        <div class="d-flex gap-1">
                            <form action="{{ route('admin.approvals.courses.approve', $approval->id) }}" method="POST"
                                  id="approveForm">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-primary approve" type="button">Phê duyệt</button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">
                                Từ chối
                            </button>
                        </div>
                        <div id="rejectModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel"
                             aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="myModalLabel">Từ chối khoá học</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <form id="rejectForm"
                                          action="{{ route('admin.approvals.courses.reject', $approval->id) }}"
                                          method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="rejectReason" class="form-label">Lý do từ
                                                    chối</label>
                                                <textarea placeholder="Nhập lý do từ chối..." class="form-control"
                                                          id="rejectNote" name="note" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Huỷ
                                            </button>
                                            <button type="button" class="btn btn-primary" id="submitRejectForm">Xác nhận
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @elseif($approval->status === 'rejected')
                        <button type="button" class="btn btn-danger ">
                            Khoá học không đủ điều kiện
                        </button>
                    @else
                        <button type="button" class="btn btn-success ">
                            Khoá học đã được phê duyệt
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div>
                    <div class="d-flex profile-wrapper">
                        <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                    <i class="ri-airplay-fill d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Tổng quan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#curriculum" role="tab">
                                    <i class="ri-price-tag-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Chương trình giảng dạy</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#test-case" role="tab">
                                    <i class="ri-folder-4-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Tiêu chí</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-content pt-4 text-muted">
            <div class="tab-pane active" id="overview-tab" role="tabpanel">
                <div class="row">
                    <div class="col-xxl-9">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Mô tả</h5>
                                <p class="text-muted mb-4">{{ $approval->course->description }}</p>
                                <div>
                                    <h5 class="mb-3">Yêu cầu</h5>
                                    <ul class="text-muted vstack gap-2">
                                        @php
                                            $requirements = json_decode($approval->course->requirements, true);
                                            $requirements = is_array($requirements) ? $requirements : [];
                                        @endphp

                                        @if (!empty($requirements))
                                            @foreach ($requirements as $requirement)
                                                <li>
                                                    {{ $requirement }}
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="mb-3">Lợi ích</h5>
                                    <ul class="text-muted vstack gap-2">
                                        @php
                                            $benefits = json_decode($approval->course->benefits, true);
                                            $benefits = is_array($benefits) ? $benefits : [];
                                        @endphp
                                        @if (!empty($benefits))
                                            @foreach ($benefits as $benefit)
                                                <li>
                                                    {{ $benefit }}
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>

                                <div>
                                    <h5 class="mb-3">Câu hỏi thường gặp</h5>
                                    @php
                                        $qa = json_decode($approval->course->qa, true);
                                        $qa = is_array($qa) ? $qa : [];
                                    @endphp
                                    <div class="accordion" id="default-accordion-example">
                                        @if (!empty($qa))
                                            @foreach ($qa as $index => $item)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="heading{{ $index + 1 }}">
                                                        <button
                                                            class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapse{{ $index + 1 }}"
                                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                                            aria-controls="collapse{{ $index + 1 }}">
                                                            {{ $item['question'] }}
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $index + 1 }}"
                                                         class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                                         aria-labelledby="heading{{ $index + 1 }}"
                                                         data-bs-parent="#default-accordion-example">
                                                        <div class="accordion-body">
                                                            {{ $item['answers'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-content-center">
                                <h5 class="mb-0">
                                    Tổng quan khoá học
                                </h5>
                                <div>
                                    <div style="width: 80px;"
                                         class="progress animated-progress custom-progress progress-label">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 30%"
                                             aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                                            <div class="label">30%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table mb-0">
                                        <tbody>
                                        <tr>
                                            <td class="fw-medium">Thời lượng</td>
                                            <td><span class="badge bg-success-subtle text-success">
                                               {{ gmdate("H:i:s", $totalDuration) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Chương học</td>
                                            <td>{{ $approval->approvable->chapters->count() ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Bài học</td>
                                            <td> {{  $approval->approvable->chapters->sum(fn ($chapter) => $chapter->lessons->count()) ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Trình độ</td>
                                            <td>{{ $approval->course->level ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium">Giá</td>
                                            <td>{{ number_format($approval->course->price ?? 0) ?? '' }}</td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane " id="curriculum" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Danh sách chương trình giảng dạy</h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="accordionWithicon">
                                    @foreach ($approval->course->chapters as $chapterIndex => $chapter)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingChapter{{ $chapterIndex }}">
                                                <button
                                                    class="accordion-button {{ $chapterIndex == 0 ? '' : 'collapsed' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseChapter{{ $chapterIndex }}"
                                                    aria-expanded="{{ $chapterIndex == 0 ? 'true' : 'false' }}"
                                                    aria-controls="collapseChapter{{ $chapterIndex }}">
                                                    <span class="fw-bold">Chương {{ $chapterIndex + 1 }}</span>:
                                                    {{ $chapter->title }}
                                                </button>
                                            </h2>
                                            <div id="collapseChapter{{ $chapterIndex }}"
                                                 class="accordion-collapse collapse {{ $chapterIndex == 0 ? 'show' : '' }}"
                                                 aria-labelledby="headingChapter{{ $chapterIndex }}"
                                                 data-bs-parent="#accordionWithicon">
                                                <div class="accordion-body">
                                                    <div class="accordion" id="accordionLessons{{ $chapterIndex }}">
                                                        @foreach ($chapter->lessons->sortBy('order') as $lessonIndex => $lesson)
                                                            <div class="accordion-item">
                                                                <h2 class="accordion-header"
                                                                    id="headingLesson{{ $chapterIndex }}{{ $lessonIndex }}">
                                                                    <button
                                                                        class="accordion-button {{ $lessonIndex == 0 ? '' : 'collapsed' }}"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#collapseLesson{{ $chapterIndex }}{{ $lessonIndex }}"
                                                                        aria-expanded="{{ $lessonIndex == 0 ? 'true' : 'false' }}"
                                                                        aria-controls="collapseLesson{{ $chapterIndex }}{{ $lessonIndex }}">
                                                                        <div
                                                                            class="d-flex w-100 justify-content-between align-items-center">
                                                                            <span
                                                                                class="fw-bold d-flex align-items-center">
                                                                                @if ($lesson->type === 'video')
                                                                                    <i class="ri-video-line me-2"></i>
                                                                                @elseif($lesson->type === 'document')
                                                                                    <i class="ri-file-text-line me-2"></i>
                                                                                @elseif($lesson->type === 'coding')
                                                                                    <i
                                                                                        class="ri-code-s-slash-fill me-2"></i>
                                                                                @elseif($lesson->type === 'quiz')
                                                                                    <i
                                                                                        class="ri-questionnaire-fill me-2"></i>
                                                                                @endif
                                                                                Bài học {{ $lessonIndex + 1 }}:
                                                                                {{ $lesson->title }}
                                                                            </span>
                                                                            @if ($lesson->type === 'video')
                                                                                <span class="ms-3">10h</span>
                                                                            @endif
                                                                        </div>
                                                                    </button>
                                                                </h2>
                                                                <div
                                                                    id="collapseLesson{{ $chapterIndex }}{{ $lessonIndex }}"
                                                                    class="accordion-collapse collapse {{ $lessonIndex == 0 ? 'show' : '' }}"
                                                                    aria-labelledby="headingLesson{{ $chapterIndex }}{{ $lessonIndex }}">
                                                                    <div class="accordion-body">
                                                                        @if ($lesson->type === 'video')
                                                                            <mux-player
                                                                                playback-id="EcHgOK9coz5K4rjSwOkoE7Y7O01201YMIC200RI6lNxnhs"
                                                                                metadata-video-title="Test VOD"
                                                                                metadata-viewer-user-id="user-id-007"
                                                                                style="height: 300px; width: 100%;"></mux-player>
                                                                        @endif
                                                                        <div
                                                                            style="margin-top: auto; display: flex; justify-content: flex-end;">
                                                                            <a class="btn btn-primary" href="#">Xem
                                                                                bài học</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="test-case" role="tabpanel"></div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player"></script>
    <script>
        $(document).ready(function () {
            $(".approve").click(function (event) {
                event.preventDefault();

                Swal.fire({
                    title: "Phê duyệt khoá học ?",
                    text: "Bạn có chắc chắn muốn phê duyệt khoá học này?",
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

            $('#submitRejectForm').on('click', function () {
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
                    success: function (response) {
                        Swal.fire({
                            title: 'Thao tác thành công!',
                            text: 'Lý do từ chối đã được ghi nhận.',
                            icon: 'success'
                        }).then(() => {
                            $('#rejectModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function (error) {
                        Swal.fire({
                            title: 'Thao tác thất bại!',
                            text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                            icon: 'error'
                        });
                    }
                });
            });
        });
    </script>
@endpush
