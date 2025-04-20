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
                    <div class="avatar-md">
                        <div class="avatar-title bg-white rounded-circle">
                            <img src="{{ $course->thumbnail }}" alt=""
                                class="rounded-circle img-fluid h-100 object-fit-cover">
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="text-white mb-1 d-flex align-items-center gap-2">
                                {{ $course->name }}

                                @switch($course->status)
                                    @case('draft')
                                        <span class="badge badge-label bg-dark">
                                            <i class="mdi mdi-circle-medium"></i> Bản nháp
                                        </span>
                                    @break

                                    @case('pending')
                                        <span class="badge badge-label bg-warning">
                                            <i class="mdi mdi-circle-medium"></i> Chờ phê duyệt
                                        </span>
                                    @break

                                    @case('approved')
                                        <span class="badge badge-label bg-success">
                                            <i class="mdi mdi-circle-medium"></i> Đã duyệt
                                        </span>
                                    @break

                                    @case('modify_request')
                                        <span class="badge badge-label bg-warning">
                                            <i class="mdi mdi-circle-medium"></i> Chờ chỉnh sửa nội dung
                                        </span>
                                    @break

                                    @default
                                        <span class="badge badge-label bg-danger">
                                            <i class="mdi mdi-circle-medium"></i> Đã từ chối
                                        </span>
                                @endswitch
                            </h3>

                            <!-- Các nút hành động -->
                            <div class="d-flex gap-2">
                                @if ($course->status == 'approved')
                                    <!-- Nút từ chối -->
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectCourseModal">
                                        Từ chối
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Modal từ chối khóa học -->
                        <div id="rejectCourseModal" class="modal fade" tabindex="-1"
                            aria-labelledby="rejectCourseModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="rejectCourseModalLabel">Từ chối khóa học</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form id="rejectCourseForm" action="{{ route('admin.courses.reject', $course->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="rejectCourseReason" class="form-label">Lý do từ chối</label>
                                                <textarea placeholder="Nhập lý do từ chối..." class="form-control" id="rejectCourseReason" name="note"
                                                    rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light"
                                                data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-primary">Xác nhận</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="hstack gap-3 flex-wrap mt-3 text-white">
                            <div>
                                <i class="ri-map-pin-user-line me-1"></i>
                                Giảng viên : {{ $course->user->name ?? '' }}
                            </div>
                            <div class="vr"></div>
                            <div>
                                <i class="ri-building-line align-bottom me-1"></i>
                                Danh mục : {{ $course->category->name ?? '' }}
                            </div>
                            <div class="vr"></div>
                            <div>Ngày tạo : <span class="fw-medium">{{ $course->created_at ?? '' }}</span>
                            </div>
                            <div class="vr"></div>
                        </div>
                    </div>

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
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#listStudent" role="tab">
                                    <i class="ri-price-tag-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Thống kê</span>
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
                                <p class="text-muted mb-4">{{ $course->description }}</p>
                                <div>
                                    <h5 class="mb-3">Yêu cầu</h5>
                                    <ul class="text-muted vstack gap-2">
                                        @php
                                            $requirements = json_decode($course->requirements, true);
                                        @endphp
                                        @foreach ($requirements as $requirement)
                                            <li>
                                                {{ $requirement }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="mb-3">Lợi ích</h5>
                                    <ul class="text-muted vstack gap-2">
                                        @php
                                            $benefits = json_decode($course->benefits, true);
                                        @endphp
                                        @foreach ($benefits as $benefit)
                                            <li>
                                                {{ $benefit }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div>
                                    <h5 class="mb-3">Câu hỏi thường gặp</h5>
                                    @php
                                        $qa = json_decode($course->qa, true);
                                    @endphp
                                    <div class="accordion" id="default-accordion-example">
                                        @foreach ($qa as $index => $item)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading{{ $index + 1 }}">
                                                    <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}"
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
                                                        {{ $item['answer'] ?? null }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
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
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-medium">Chương học</td>
                                                <td>
                                                    {{ $course->chapters->count() ?? '' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-medium">Bài học</td>
                                                <td>
                                                    {{ $course->chapters()->with('lessons')->count() ?? '' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-medium">Trình độ</td>
                                                <td>{{ $course->level ?? '' }}</td>
                                            </tr>
                                            @if ($course->is_free == 1)
                                                <tr>
                                                    <td class="fw-medium">Miễn phí</td>
                                                    <td><span class="badge bg-success-subtle text-success">Có</span>
                                                    </td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td class="fw-medium">Giá</td>
                                                    <td>{{ number_format($course->price) ?? '' }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="fw-medium">Ngày chấp nhận</td>
                                                <td>{!! $course->accepted ?? '<span class="badge bg-success-subtle text-danger">Chưa kiểm duyệt</span>' !!}</td>
                                            </tr>
                                            @if ($course->status === 'approved')
                                                <tr>
                                                    <td class="fw-medium">Khóa học phổ biến</td>
                                                    <td>
                                                        <div class="form-check form-switch form-switch-warning">
                                                            <input class="form-check-input popular-course-toggle"
                                                                type="checkbox" role="switch" name="popular_course"
                                                                value="{{ $course->id }}"
                                                                data-course-id="{{ $course->id }}"
                                                                data-status="{{ $course->status }}"
                                                                @checked($course->is_popular != null)>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a class="btn btn-primary" href="{{ route('admin.courses.index') }}">Quay lại danh sách</a>
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
                                    @foreach ($course->chapters as $chapterIndex => $chapter)
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
                                                                                <span
                                                                                    class="ms-3">{{ gmdate('i:s', $lesson->lessonable->duration) }}</span>
                                                                            @endif
                                                                        </div>
                                                                    </button>
                                                                </h2>
                                                                <div id="collapseLesson{{ $chapterIndex }}{{ $lessonIndex }}"
                                                                    class="accordion-collapse collapse {{ $lessonIndex == 0 ? 'show' : '' }}"
                                                                    aria-labelledby="headingLesson{{ $chapterIndex }}{{ $lessonIndex }}">
                                                                    <div class="accordion-body">
                                                                        @if ($lesson->type === 'video' && isset($videos[$lesson->id]))
                                                                            @foreach ($videos[$lesson->id] as $video)
                                                                                @if (!empty($video['mux_playback_id']))
                                                                                    <mux-player
                                                                                        playback-id="{{ $video['mux_playback_id'] }}"
                                                                                        metadata-video-title="{{ $video['title'] ?? 'Không có tiêu đề' }}"
                                                                                        style="height: 300px; width: 100%;">
                                                                                    </mux-player>
                                                                                @else
                                                                                    <p>Không có video để phát.</p>
                                                                                @endif
                                                                            @endforeach
                                                                        @endif
                                                                        @if ($lesson->type === 'document' && isset($documents[$lesson->id]))
                                                                            <ul>
                                                                                @foreach ($documents[$lesson->id] as $document)
                                                                                    <li>
                                                                                        <a href="#"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#documentModal{{ $document->id }}">
                                                                                            {{ $document->title }}
                                                                                        </a>

                                                                                        <!-- Modal -->
                                                                                        <div class="modal fade"
                                                                                            id="documentModal{{ $document->id }}"
                                                                                            tabindex="-1"
                                                                                            aria-labelledby="documentModalLabel{{ $document->id }}"
                                                                                            aria-hidden="true">
                                                                                            <div
                                                                                                class="modal-dialog modal-lg">
                                                                                                <div class="modal-content">
                                                                                                    <div
                                                                                                        class="modal-header">
                                                                                                        <h5 class="modal-title"
                                                                                                            id="documentModalLabel{{ $document->id }}">
                                                                                                            {{ $document->title }}
                                                                                                        </h5>
                                                                                                        <button
                                                                                                            type="button"
                                                                                                            class="btn-close"
                                                                                                            data-bs-dismiss="modal"
                                                                                                            aria-label="Close"></button>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="modal-body">
                                                                                                        @if (Str::endsWith($document->file_path, '.pdf'))
                                                                                                            <iframe
                                                                                                                src="{{ asset($document->file_path) }}"
                                                                                                                width="100%"
                                                                                                                height="500px"></iframe>
                                                                                                        @else
                                                                                                            <p>Không thể xem
                                                                                                                trước tệp
                                                                                                                này, <a
                                                                                                                    href="{{ asset($document->file_path) }}"
                                                                                                                    target="_blank">Tải
                                                                                                                    xuống
                                                                                                                    tại
                                                                                                                    đây</a>
                                                                                                            </p>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        @endif

                                                                        @if ($lesson->type === 'quiz' && isset($quizzes[$lesson->id]))
                                                                            <div class="quiz-section">


                                                                                @foreach ($quizzes[$lesson->id] as $quiz)
                                                                                    @foreach ($quiz->questions as $question)
                                                                                        <div class="question-container">
                                                                                            {{ $question->question }}
                                                                                        </div>
                                                                                        @if ($question->answers->isNotEmpty())
                                                                                            @foreach ($question->answers as $answer)
                                                                                                <div class="form-check">
                                                                                                    <input
                                                                                                        class="form-check-input"
                                                                                                        type="{{ $question->answer_type == 'multiple_choice' ? 'checkbox' : 'radio' }}"
                                                                                                        name="question_{{ $question->id }}{{ $question->answer_type == 'multiple_choice' ? '[]' : '' }}"
                                                                                                        id="answer_{{ $answer->id }}"
                                                                                                        {{ $answer->is_correct ? 'checked' : '' }}
                                                                                                        disabled>
                                                                                                    <label
                                                                                                        class="form-check-label"
                                                                                                        for="answer_{{ $answer->id }}">
                                                                                                        {{ $answer->answer }}
                                                                                                    </label>
                                                                                                </div>
                                                                                            @endforeach
                                                                                        @else
                                                                                            <p class="no-answer">Chưa có
                                                                                                đáp án cho câu hỏi này.</p>
                                                                                        @endif
                                                                                    @endforeach
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                        <div
                                                                            style="margin-top: auto; display: flex; justify-content: flex-end;">
                                                                            <a class="btn btn-primary" href="">Xem
                                                                                bài
                                                                                học</a>
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

            <div class="tab-pane dashboard-section" id="listStudent" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0 fw-bold text-dark">Danh sách học viên</h5>
                            </div>
                            <div class="card-body p-3">

                                <!-- Thêm class metrics-grid -->
                                <div class="row mb-4 g-3 metrics-grid">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="card metric-card border-0 shadow-sm text-center">
                                            <div class="card-body p-3">
                                                <h6 class="text-muted">Tổng số học viên</h6>
                                                <p class="fs-4 fw-bold text-dark">
                                                    {{ $userCounts->total_students ?? 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="card metric-card border-0 shadow-sm text-center">
                                            <div class="card-body p-3">
                                                <h6 class="text-muted">Hoàn thành</h6>
                                                <p class="fs-4 fw-bold text-success">
                                                    {{ $userCounts->completed_students ?? 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="card metric-card border-0 shadow-sm text-center">
                                            <div class="card-body p-3">
                                                <h6 class="text-muted">Đang học</h6>
                                                <p class="fs-4 fw-bold text-warning">
                                                    {{ $userCounts->in_progress_students ?? 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="card metric-card border-0 shadow-sm text-center">
                                            <div class="card-body p-3">
                                                <h6 class="text-muted">Chưa bắt đầu</h6>
                                                <p class="fs-4 fw-bold text-danger">
                                                    {{ $userCounts->not_started_students ?? 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row chart-grid g-4">
                                    <div class="col-xl-6">
                                        <div class="card chart-container border-0 shadow-sm h-100">
                                            <div class="card-header bg-light d-flex align-items-center">
                                                <h5 class="mb-0 fw-bold flex-grow-1">Đánh giá khóa học</h5>
                                                <div class="dateRangePicker" data-filter="topRatingCourseMely"></div>
                                            </div>
                                            <div class="card-body p-4">
                                                <div id="rating-pie-chart" class="apex-charts w-100" dir="ltr">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="card chart-container border-0 shadow-sm h-100">
                                            <div class="card-header bg-light d-flex align-items-center">
                                                <h5 class="mb-0 fw-bold flex-grow-1">Tiến độ học tập</h5>
                                            </div>
                                            <div class="card-body p-4">
                                                <div id="progress-pie-chart" class="apex-charts w-100" dir="ltr">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Các biểu đồ tiến độ -->
                                <div class="mt-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light d-flex align-items-center">
                                            <h5 class="mb-0 fw-bold flex-grow-1">Tiến độ hoàn thành theo chương</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div id="chapterProgressChart" class="apex-charts w-100" dir="ltr">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="card border-0 shadow-sm">
                                        <div
                                            class="card-header bg-light d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0 fw-bold">Doanh thu theo tháng</h5>

                                            <!-- Dropdown chọn năm -->
                                            <div>
                                                <select id="yearFilter" class="form-select form-select-sm w-auto">
                                                    @for ($year = now()->year; $year >= 2020; $year--)
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="card-body p-4">
                                            <div id="monthlyRevenueChart" class="apex-charts w-100" dir="ltr"></div>
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

    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModalLabel">Đánh giá của học viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Rating:</strong> <span id="ratingValue"></span></p>
                    <p><strong>Nội dung:</strong></p>
                    <p id="ratingContent"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player"></script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>

    <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard-ecommerce.init.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/pages/dashboard-projects.init.js') }}"></script> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}" />
    <script src="{{ asset('assets/js/pages/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/daterangepicker.min.js') }}"></script>

    <script>
        function showRating(courseId, userId) {
            fetch(`/courses/${courseId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Chưa có đánh giá');
                    }
                    return response.json();
                })
                .then(data => {
                    let rating = data.rating !== null && data.rating !== undefined ? data.rating : "N/A";
                    let content = data.content ? data.content : "Không có nội dung đánh giá";

                    document.getElementById('ratingValue').textContent = rating + ' ⭐';
                    document.getElementById('ratingContent').textContent = content;
                    var ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
                    ratingModal.show();
                })
                .catch(error => {
                    document.getElementById('ratingValue').textContent = 'N/A';
                    document.getElementById('ratingContent').textContent = error.message;
                    var ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
                    ratingModal.show();
                });
        }

        function updatePieChart(ratingData) {
            let pieChartContainer = document.querySelector("#rating-pie-chart");

            if (typeof pieChart !== "undefined" && pieChart) {
                pieChart.destroy();
                pieChart = undefined;
            }

            pieChartContainer.innerHTML = "";

            if (!ratingData || ratingData.length === 0) {
                pieChartContainer.innerHTML = `
        <div style="text-align: center; padding: 20px; color: #999;">
            <p><i class="fas fa-exclamation-circle"></i> Không có đánh giá nào</p>
        </div>`;
                return;
            }

            let series = ratingData.map(item => parseFloat(item.total));
            let labels = ratingData.map(item => `${item.rating} sao`);

            let pieOptions = {
                series: series,
                chart: {
                    type: "pie",
                    height: 350
                },
                labels: labels,
                colors: ["#00C853", "#FFEB3B", "#FF9800", "#FF5722", "#D50000"], // Màu sắc cho từng mức sao
                legend: {
                    position: "bottom"
                }
            };

            pieChart = new ApexCharts(pieChartContainer, pieOptions);
            pieChart.render();
        }

        // Dữ liệu từ PHP truyền sang JavaScript
        let ratingData = @json($ratingsData) || [];

        updatePieChart(ratingData);


        document.addEventListener("DOMContentLoaded", function() {
            let progressData = {
                completed: {{ $userCounts->completed_students }},
                inProgress: {{ $userCounts->in_progress_students }},
                notStarted: {{ $userCounts->not_started_students }}
            };

            let chartOptions = {
                series: [progressData.completed, progressData.inProgress, progressData.notStarted],
                chart: {
                    type: "pie",
                    height: 350
                },
                labels: ["Hoàn thành", "Đang học", "Chưa bắt đầu"],
                colors: ["#00C853", "#FF9800", "#D50000"],
                legend: {
                    position: "bottom"
                }
            };

            let chart = new ApexCharts(document.querySelector("#progress-pie-chart"), chartOptions);
            chart.render();
        });


        let chartProgress;

        function renderProgressChart(data) {
            let chartContainer = document.querySelector("#chapterProgressChart");

            // Hủy biểu đồ cũ nếu đã tồn tại
            if (chartProgress) {
                chartProgress.destroy();
            }

            // Nếu không có dữ liệu, hiển thị thông báo
            if (data.length == 0) {
                chartContainer.innerHTML = `<p style="text-align: center; color: #999;">Không có dữ liệu</p>`;
                return;
            }

            // Sửa đoạn này: Hiển thị số chương thay vì tên chương
            let categories = data.map((item, index) => `Chương ${index + 1}`);
            let progresses = data.map(item => item.avg_progress);

            let options = {
                chart: {
                    height: 400,
                    type: "bar",
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: "Tiến độ (%)",
                    data: progresses
                }],
                xaxis: {
                    categories: categories,
                    min: 0, // Giá trị nhỏ nhất của trục X
                    max: 100, // Giá trị lớn nhất của trục X
                    tickAmount: 10, // Chia X thành 10 đoạn: 0, 10, 20, ..., 100
                    labels: {
                        formatter: value => value + "%" // Hiển thị dạng 10%, 20%...
                    }
                },
                yaxis: {
                    labels: {
                        formatter: value => value
                    }
                },
                tooltip: {
                    y: {
                        formatter: value => value + "%"
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        columnWidth: "60%"
                    }
                },
                colors: ["#FF9800"]
            };

            chartProgress = new ApexCharts(chartContainer, options);
            chartProgress.render();
        }

        // Chuyển dữ liệu từ Blade PHP sang JavaScript
        let chapterProgressData = @json($chapterProgressStats);

        renderProgressChart(chapterProgressData);

        let chartRevenue;

        function renderRevenueChart(data) {
            let chartContainer = document.querySelector("#monthlyRevenueChart");

            if (chartRevenue) {
                chartRevenue.destroy();
            }

            if (!data.length) {
                chartContainer.innerHTML = `<p style="text-align: center; color: #999;">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.map(item => `${item.month}/${item.year}`);
            let revenues = data.map(item => item.total_revenue);

            let options = {
                chart: {
                    height: 350,
                    type: "area",
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: "Doanh thu (VND)",
                    data: revenues
                }],
                xaxis: {
                    categories: categories
                },
                yaxis: {
                    labels: {
                        formatter: value => value.toLocaleString("vi-VN", {
                            style: "currency",
                            currency: "VND",
                            minimumFractionDigits: 0
                        })
                    }
                },
                tooltip: {
                    y: {
                        formatter: value => value.toLocaleString("vi-VN", {
                            style: "currency",
                            currency: "VND",
                            minimumFractionDigits: 0
                        })
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: value => value.toLocaleString("vi-VN", {
                        style: "currency",
                        currency: "VND",
                        minimumFractionDigits: 0
                    })
                },
                stroke: {
                    curve: 'smooth'
                },
                colors: ["#00E396"]
            };

            chartRevenue = new ApexCharts(chartContainer, options);
            chartRevenue.render();
        }

        document.addEventListener("DOMContentLoaded", function() {
            const yearFilter = document.getElementById("yearFilter");

            function fetchRevenueData(year) {
                $.ajax({
                    url: "{{ route('admin.courses.renueveCourse', $course->id) }}",
                    method: 'GET',
                    data: {
                        year: year
                    },
                    dataType: 'json',
                    success: function(data) {
                        renderRevenueChart(data);
                    },
                    error: function(xhr, status, error) {
                        console.error("Lỗi khi tải dữ liệu doanh thu:", error);
                    }
                });
            }

            yearFilter.addEventListener("change", function() {
                fetchRevenueData(this.value);
            });

            fetchRevenueData(yearFilter.value);
        });
    </script>
    <script>
        $(document).ready(function() {
            // Kiểm duyệt khóa học
            $(".approveCourse").click(function(event) {
                event.preventDefault();

                Swal.fire({
                    title: "Kiểm duyệt khóa học?",
                    text: "Bạn có chắc chắn muốn phê duyệt khóa học này?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    confirmButtonText: "Phê duyệt",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#approveCourseForm").submit();
                    }
                });
            });

            // Xác nhận từ chối khóa học
            $('#submitRejectCourseForm').on('click', function() {
                const note = $('#rejectCourseReason').val();

                if (note.trim() === '') {
                    Swal.fire({
                        text: "Vui lòng nhập lý do từ chối.",
                        icon: 'warning'
                    });
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: $('#rejectCourseForm').attr('action'),
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
                            $('#rejectCourseModal').modal('hide');
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
    </script>
    <script>
        $(document).on('change', '.popular-course-toggle', function() {
            var courseID = $(this).data('course-id'); // Lấy ID khóa học
            var courseStatus = $(this).data('status'); // Lấy trạng thái khóa học
            var isChecked = $(this).is(':checked'); // Kiểm tra trạng thái checkbox
            // Kiểm tra nếu khóa học bị từ chối, disable checkbox và không cho cập nhật
            if (courseStatus === 'rejected') {
                $(this).prop('checked', !isChecked); // Đảo lại trạng thái checkbox
                Toastify({
                    text: "Khóa học đã bị từ chối, không thể cập nhật.",
                    backgroundColor: "red",
                    duration: 3000,
                    close: true
                }).showToast();
                return; // Dừng function, không gửi AJAX
            }
            var updateUrl = "{{ route('admin.courses.updatePopular', ':courseID') }}".replace(':courseID',
                courseID);

            $.ajax({
                type: "PUT",
                url: updateUrl,
                data: {
                    is_popular: isChecked ? 1 : 0, // Gửi giá trị 1 nếu checked, 0 nếu không
                    _token: "{{ csrf_token() }}" // Thêm CSRF token để tránh lỗi 419
                },
                success: function(response) {
                    Toastify({
                        text: "Cập nhật khóa học phổ biến thành công",
                        backgroundColor: "green",
                        duration: 3000,
                        close: true
                    }).showToast();
                },
                error: function(xhr) {
                    Toastify({
                        text: response.message,
                        backgroundColor: "red",
                        duration: 3000,
                        close: true
                    }).showToast();
                }
            });
        });
    </script>
@endpush
