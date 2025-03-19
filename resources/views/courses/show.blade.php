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
                        <h3 class="text-white mb-1">
                            {{ $course->name }}
                        </h3>
                        <div class="hstack gap-3 flex-wrap mt-3 text-white">
                            <div>
                                <i class="ri-map-pin-user-line me-1"></i>
                                Người hướng dẫn : {{ $course->user->name ?? '' }}
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
                                        class="d-none d-md-inline-block">Danh sách học viên</span>
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
                                                        {{ $item['answer'] }}
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

            <div class="tab-pane " id="listStudent" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Danh sách học viên</h5>
                            </div>
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">

                                            <div class="accordion" id="accordionWithicon">
                                                <div class="row mb-2">
                                                    <div class="col-12 col-sm-6 col-md-3">
                                                        <div class="card text-center h-75">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Tổng số học viên</h5>
                                                                <p class="card-text fs-4">
                                                                    {{ $userCounts->total_students ?? 0 }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6 col-md-3">
                                                        <div class="card text-center h-75">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Số hoc viên hoàn thành</h5>
                                                                <p class="card-text fs-4 text-success">
                                                                    {{ $userCounts->completed_students ?? 0 }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6 col-md-3">
                                                        <div class="card text-center h-75">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Số hoc viên đang hoàn thành</h5>
                                                                <p class="card-text fs-4 text-warning">
                                                                    {{ $userCounts->in_progress_students ?? 0 }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6 col-md-3">
                                                        <div class="card text-center h-75">
                                                            <div class="card-body">
                                                                <h5 class="card-title">Số hoc viên chưa hoàn thành
                                                                </h5>
                                                                <p class="card-text fs-4 text-danger">
                                                                    {{ $userCounts->not_started_students ?? 0 }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="listjs-table" id="customerList">
                                                <div class="table-responsive table-card mt-3 mb-1">
                                                    <table class="table align-middle table-nowrap" id="customerTable">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>STT</th>
                                                                <th>Họ và tên</th>
                                                                <th>Email</th>
                                                                <th>Trạng thái</th>
                                                                <th>Bài học gần đây</th>
                                                                <th>Tiến độ khóa học</th>
                                                                <th>Nội dung đánh giá</th>
                                                                <th>Thời gian tham gia khóa học</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="list">

                                                            @foreach ($courseUsers as $courseUser)
                                                                <tr>
                                                                    <td>{{ $loop->index + 1 }}</td>
                                                                    <td>{{ $courseUser->user->name }}</td>
                                                                    <td>{{ $courseUser->user->email }}</td>
                                                                    <td>{{ $courseUser->completed_at ? 'Hoàn thành' : 'Đang học' }}
                                                                    </td>
                                                                    <td>

                                                                        @if (!empty($recentLessons[$courseUser->user_id]))
                                                                            @php
                                                                                $recentLesson = $recentLessons[
                                                                                    $courseUser->user_id
                                                                                ]->first();
                                                                            @endphp
                                                                            <span
                                                                                class="badge bg-info">{{ $recentLesson->lesson_title }}</span>
                                                                        @else
                                                                            <span class="text-muted">Chưa bắt đầu</span>
                                                                        @endif

                                                                    </td>
                                                                    <td>{{ $courseUser->progress_percent }}%</td>
                                                                    <td>
                                                                        <button class="btn btn-primary"
                                                                            onclick="showRating({{ $course->id }},
                                                                        {{ $courseUser->id }})">
                                                                            Xem đánh giá
                                                                        </button>
                                                                    </td>
                                                                    <td>{{ $courseUser->created_at }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                {{ $courseUsers->links() }}
                                            </div>

                                            <div class="d-flex">
                                                <div class="col-xl-6">
                                                    <div class="card card-height-100">
                                                        <div class="card-header align-items-center d-flex">
                                                            <h4 class="card-title mb-0 flex-grow-1">
                                                                Đánh giá khoá học
                                                            </h4>
                                                            <div class="dateRangePicker"
                                                                data-filter="topRatingCourseMely">
                                                            </div>
                                                        </div><!-- end card header -->

                                                        <div class="card-body">
                                                            <div id="rating-pie-chart" dir="ltr"></div>
                                                        </div>
                                                    </div> <!-- .card-->
                                                </div> <!-- .col-->

                                                <div class="col-xl-6">
                                                    <div class="card card-height-100">
                                                        <div class="card-header align-items-center d-flex">
                                                            <h4 class="card-title mb-0 flex-grow-1">
                                                                Tiến độ học tập của học viên
                                                            </h4>
                                                        </div><!-- end card header -->

                                                        <div class="card-body">
                                                            <div id="progress-pie-chart" dir="ltr"></div>
                                                        </div>
                                                    </div> <!-- .card-->
                                                </div> <!-- .col-->
                                            </div>

                                            <div>
                                                <div class="col-xxl-11">
                                                    <div class="">
                                                        <div class="card-header border-0 align-items-center d-flex">
                                                            <h4 class="card-title mb-0 flex-grow-1">Tiến độ hoàn thành theo
                                                                chương</h4>
                                                        </div>
                                                        <div id="chapterProgressChart" class="apex-charts"
                                                            dir="ltr"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="col-xxl-12">
                                                    <div class="card">
                                                        <div class="card-header border-0 align-items-center d-flex">
                                                            <h4 class="card-title mb-0 flex-grow-1">Doanh thu theo tháng
                                                            </h4>
                                                        </div>
                                                        <div id="monthlyRevenueChart" class="apex-charts" dir="ltr">
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

            let series = [];
            let labels = [];

            ratingData.forEach(item => {
                let rating = item.rating !== null && item.rating !== undefined ? item.rating : "Không có đánh giá";
                series.push(parseFloat(item.total));
                labels.push(rating + " sao");
            });

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
                    type: "line",
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
                            currency: "VND"
                        })
                    }
                },
                tooltip: {
                    y: {
                        formatter: value => value.toLocaleString("vi-VN", {
                            style: "currency",
                            currency: "VND"
                        })
                    }
                },
                colors: ["#00E396"]
            };

            chartRevenue = new ApexCharts(chartContainer, options);
            chartRevenue.render();
        }

        // Chuyển dữ liệu từ Blade PHP sang JavaScript
        let monthlyRevenueData = @json($monthlyRevenue);
        renderRevenueChart(monthlyRevenueData);
    </script>
@endpush
