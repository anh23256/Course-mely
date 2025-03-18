@extends('layouts.app')

@push('page-css')
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? 'Dashboard' }}</h4>
                    <div class="dateRangePicker btn btn-outline-primary rounded-pill px-3"
                        data-filter="totalRevenueCourseMely"></div>
                </div>
            </div>
        </div>

        <!-- Greeting -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-white p-4 rounded shadow-sm">
                    <h4 class="fs-20 mb-1 text-primary" id="greeting">Xin chào, {{ Auth::user()->name ?? '' }}!</h4>
                    <p class="text-muted mb-0">Chúc bạn một ngày làm việc hiệu quả!</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4">
            <!-- Tổng doanh thu -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #e9f7ef, #d4efdf); border-radius: 12px;">
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Tổng doanh thu</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-success-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="bx bx-dollar-circle text-success"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value"
                                    data-target="totalRevenue">{{ number_format($totalAmount->total_revenue ?? 0, 0, '.', '.') }}</span>
                                <span class="fs-14 text-muted"></span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lợi nhuận đạt được -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #e9f2ff, #d6eaff); border-radius: 12px;">
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Lợi nhuận đạt được</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-info-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="bx bx-dollar-circle text-info"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value"
                                    data-target="totalProfit">{{ number_format($totalAmount->total_profit ?? 0, 0, '.', '.') }}</span>
                                <span class="fs-14 text-muted"></span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #fff3e6, #ffeedb); border-radius: 12px;">
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Tổng khóa học</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-warning-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="las la-book-reader text-warning"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value" data-target="totalCourse">{{ $totalCourse ?? 0 }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #e9e9ff, #dcdbff); border-radius: 12px;">
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Người hướng dẫn</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-primary-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="ri-account-circle-line text-primary"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value" data-target="totalInstructor">{{ $totalInstructor ?? 0 }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Analysis -->
        <div class="row mt-4 g-4">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Biểu đồ tổng quan top 10 danh mục</h4>
                    </div>
                    <div class="card-body">
                        <div id="category-revenue-chart" class="apex-charts"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0 text-white">Tổng quan top 10 danh mục</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_category"><i
                                class='fs-9 bx bx-download'> Excel</i></button>
                    </div>
                    <div class="card-body" style="overflow-x: hidden; max-width: 100%;">
                        <div class="table-responsive table-card" style="overflow-x: hidden;">
                            <table id="table-categories" class="table table-centered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th
                                            style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            Danh mục</th>
                                        <th style="width: 100px;">Khóa học</th>
                                        <th style="width: 100px;">Học viên</th>
                                        <th style="width: 100px;">Giảng viên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categoryStats as $category)
                                        <tr>
                                            <td
                                                style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $category->category_name ?? 'Không xác định' }}
                                            </td>
                                            <td class="text-center">{{ $category->total_courses }}</td>
                                            <td class="text-center">{{ $category->total_enrolled_students }}</td>
                                            <td class="text-center">{{ $category->total_instructors }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Category Analysis -->

        <!-- Revenue Chart -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Doanh thu 2025 CourseMeLy</h4>
                    </div>
                    <!-- end card header -->


                    <div>
                        <div class="row g-0 text-center">
                            <div class="col-6 col-sm-6">
                                <div class="p-3 border border-dashed border-start-0">
                                    <h5 class="mb-1"><span class="counter-value-revenue" data-target="228.89">
                                            {{ number_format($totalAmount->total_revenue ?? 0, 0, '.', '.') }}</span>
                                        VND</h5>
                                    <p class="text-muted mb-0">Doanh thu</p>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-6 col-sm-6">
                                <div class="p-3 border border-dashed border-start-0 border-end-0">
                                    <h5 class="mb-1 text-success"><span class="counter-value-profit"
                                            data-target="10589">{{ number_format($totalAmount->total_profit ?? 0, 0, '.', '.') }}</span>
                                        VND</h5>
                                    <p class="text-muted mb-0">Lợi nhuận</p>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body p-0 pb-2">
                        <div>
                            <div id="projects-overview-chart"
                                data-colors='["--vz-primary", "--vz-warning", "--vz-danger"]' dir="ltr"
                                class="apex-charts"></div>
                        </div>
                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->

        <!-- Top Instructors & Courses -->
        <div class="row mt-4 g-4">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Người hướng dẫn nổi bật</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_instructor"><i
                                class='fs-9 bx bx-download'> Excel</i></button>
                        <button class="fs-7 badge bg-primary mx-2" id="showTopInstructorButton">Xem biểu đồ</button>
                    </div>
                    <div class="card-body" id="showTopInstructorDiv">
                        <div class="table-responsive table-card">
                            <table id="table-instructors"
                                class="table table-centered table-hover align-middle table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Người hướng dẫn</th>
                                        <th>Khoá học</th>
                                        <th>Học viên</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topInstructors as $topInstructor)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $topInstructor->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}"
                                                        alt=""
                                                        class="avatar-sm p-2 rounded-circle object-fit-cover" />
                                                    <div class="ms-2">
                                                        <h5 class="fs-14 my-1 fw-medium">{{ $topInstructor->name ?? '' }}
                                                        </h5>
                                                        <span class="text-muted">Tham gia
                                                            {{ \Carbon\Carbon::parse($topInstructor->created_at)->format('d/m/Y') ?? '' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $topInstructor->total_courses ?? '' }}</td>
                                            <td>{{ $topInstructor->total_enrolled_students ?? '' }}</td>
                                            <td>{{ number_format($topInstructor->total_revenue) ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4 px-4 text-center">
                                <div id="pagination-links-instructors">
                                    {{ $topInstructors->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Top khoá học bán chạy</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_course"><i
                                class='fs-9 bx bx-download'> Excel</i></button>
                        <button class="fs-7 badge bg-primary mx-2" id="showBestSellingCoursesButton">Xem biểu đồ</button>
                    </div>
                    <div class="card-body" id="showBestSellingCoursesDiv">
                        <div class="table-responsive table-card">
                            <table id="table-courses"
                                class="table table-hover table-centered align-middle table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Khoá học</th>
                                        <th>Đã bán</th>
                                        <th>Người học</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topCourses as $topCourse)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img style="width:70px" src="{{ $topCourse->thumbnail }}"
                                                        alt="" class="img-fluid d-block" />
                                                    <div>
                                                        <h5 class="fs-14 my-1">
                                                            {{ \Illuminate\Support\Str::limit($topCourse->name, 20) }}</h5>
                                                        <span
                                                            class="text-muted">{{ \Carbon\Carbon::parse($topCourse->created_at)->format('d/m/Y') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $topCourse->total_sales }}</td>
                                            <td class="text-center">{{ $topCourse->total_enrolled_students }}</td>
                                            <td>{{ number_format($topCourse->total_revenue) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4 px-4 text-center">
                                <div id="pagination-links-courses">
                                    {{ $topCourses->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ratings & Top Students -->
        <div class="row mt-4 g-4">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0 text-white">Đánh giá khoá học</h4>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="rating-pie-chart" class="w-100"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Top học viên</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_student"><i
                                class='fs-9 bx bx-download'> Excel</i></button>
                        <button class="fs-7 badge bg-primary mx-2" id="showRenderTopStudentsButton">Xem biểu đồ</button>
                    </div>
                    <div class="card-body" id="showRenderTopStudentsDiv">
                        <div class="table-responsive table-card">
                            <table id="table-students"
                                class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                <thead class="text-muted table-light">
                                    <tr>
                                        <th>STT</th>
                                        <th>Học viên</th>
                                        <th>Khoá học đã mua</th>
                                        <th>Tổng tiền đã chi</th>
                                        <th>Lần mua gần nhất</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topUsers as $topUser)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $topUser->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}"
                                                        alt=""
                                                        class="avatar-xs rounded-circle object-fit-cover" />
                                                    <div class="ms-2">{{ $topUser->name ?? '' }}</div>
                                                </div>
                                            </td>
                                            <td>{{ $topUser->total_courses_purchased }}</td>
                                            <td>{{ number_format($topUser->total_spent ?? 0) }}</td>
                                            <td>{{ $topUser->last_purchase_date }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4 px-4 text-center">
                                <div id="pagination-links-users">
                                    {{ $topUsers->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Completed Courses & Top Instructors -->
        <div class="row mt-4 g-4">
            <div class="col-xxl-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <img src="https://img.themesbrand.com/velzon/images/img-2.gif"
                            class="avatar-xs rounded-circle object-fit-cover" alt="">
                        <h4 class="card-title mb-0 mx-2 text-white">Top 10 khóa học có tỉ lệ hoàn thành cao nhất</h4>
                    </div>
                    <div class="card-body">
                        <div id="topCompletedCourses" class="w-100"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-5">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0 text-white">Top 10 giảng viên được yêu thích nhất</h4>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="topInstructorsChart" class="w-100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Viewed Courses -->
        <div class="row mt-4">
            <div class="col-xxl-12">
                <div class="swiper marketplace-swiper rounded gallery-light">
                    <div class="d-flex pt-2 pb-4">
                        <h4 class="fs-16 mb-0 mx-2 fw-bold">Top 10 khóa học có lượt xem nhiều nhất</h4>
                    </div>
                    <div class="swiper-wrapper">
                        @foreach ($getTopViewCourses as $getTopViewCourse)
                            <div class="swiper-slide h-100">
                                <div class="card explore-box card-animate rounded">
                                    <div class="explore-place-bid-img">
                                        <img src="{{ $getTopViewCourse->thumbnail }}" alt=""
                                            class="img-fluid card-img-top explore-img" />
                                        <div class="bg-overlay"></div>
                                        <div class="place-bid-btn">
                                            <a class="btn btn-success"><i
                                                    class="ri-auction-fill align-bottom me-1 fw-bold text-white"></i>{{ $getTopViewCourse->instructor_name }}</a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="fw-medium mb-0 float-end"><i
                                                class="mdi mdi-eye text-primary align-middle"></i>
                                            {{ $getTopViewCourse->views }}</p>
                                        <h5 class="mb-1">{{ $getTopViewCourse->name }}</h5>
                                    </div>
                                    <div class="card-footer border-top border-top-dashed">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 fs-14">
                                                <i class="ri-price-tag-3-fill text-warning align-bottom me-2"></i> Giá:
                                                <span
                                                    class="fw-medium">{{ number_format($getTopViewCourse->price_sale != 0 ? $getTopViewCourse->price_sale : $getTopViewCourse->price ?? 0) }}
                                                    VND</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/daterangepicker.min.js') }}"></script>

    <script>
        var topCourse = @json($topCourses);
        var topInstructor = @json($topInstructors);
        var newData = @json($system_Funds);
        var ratingData = @json($courseRatings);
        var topStudent = @json($topUsers);
        var topCategory = @json($categoryStats);

        let chart, pieChart, chartBestSellingCourses, chartTopInstructors, chartTopInstructorFollows,
            chartTopCompletedCourses, chartTopStudents, categoryRevenueChart;

        var currentHour = new Date().getHours();
        var greetingText = "Xin chào, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        if (currentHour >= 5 && currentHour < 12) greetingText =
            "Chào buổi sáng, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        else if (currentHour >= 12 && currentHour < 18) greetingText =
            "Chào buổi chiều, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        else if (currentHour >= 18 && currentHour < 22) greetingText =
            "Chào buổi tối, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        else greetingText = "Chúc ngủ ngon, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        $("#greeting").text(greetingText);

        $(".dateRangePicker").each(function() {
            let button = $(this);

            function updateDateRangeText(start, end) {
                button.html("📅 " + start.format("DD/MM/YYYY") + " - " + end.format("DD/MM/YYYY"));
                button.attr("data-start", start.format("YYYY-MM-DD"));
                button.attr("data-end", end.format("YYYY-MM-DD"));
            }

            let defaultStart = moment().startOf("year");
            let defaultEnd = moment();

            button.daterangepicker({
                autoUpdateInput: false,
                showDropdowns: true,
                linkedCalendars: false,
                minDate: moment("2000-01-01"),
                maxDate: moment(),
                startDate: defaultStart,
                endDate: defaultEnd,
                ranges: {
                    "Hôm nay": [moment(), moment()],
                    "Hôm qua": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                    "7 ngày trước": [moment().subtract(6, "days"), moment()],
                    "Tháng này": [moment().startOf("month"), moment().endOf("month")],
                    "Tháng trước": [moment().subtract(1, "month").startOf("month"), moment().subtract(1,
                        "month").endOf("month")],
                    "1 năm trước": [moment().subtract(1, "year").startOf("year"), moment().subtract(1,
                        "year").endOf("year")]
                },
                locale: {
                    format: "DD/MM/YYYY",
                    applyLabel: "Áp dụng",
                    cancelLabel: "Hủy",
                    customRangeLabel: "Tùy chỉnh",
                }
            }, function(start, end) {
                updateDateRangeText(start, end);

                let data = {
                    startDate: start.format("YYYY-MM-DD"),
                    endDate: end.format("YYYY-MM-DD"),
                    page: 1,
                };

                loadAll(data)
            });

            updateDateRangeText(defaultStart, defaultEnd);
        });

        function updateChart(newData) {
            let chartContainer = document.querySelector("#projects-overview-chart");

            if (typeof chart !== "undefined" && chart) {
                chart.destroy();
                chart = undefined;
            }

            chartContainer.innerHTML = "";

            if (!newData || newData.length === 0) {
                chartContainer.innerHTML = `
            <div style="text-align: center; padding: 20px; color: #999;">
                <p><i class="fas fa-exclamation-circle"></i> Không có doanh thu</p>
            </div>`;
                return;
            }

            let categories = [];
            let revenueData = [];
            let profitData = [];

            newData.forEach(item => {
                categories.push("Tháng " + item.month + ", " + item.year);
                revenueData.push(parseFloat(item.total_revenue));
                profitData.push(parseFloat(item.total_profit));
            });

            let options = {
                series: [{
                        name: "Doanh thu",
                        type: "bar",
                        data: revenueData
                    },
                    {
                        name: "Lợi nhuận",
                        type: "bar",
                        data: profitData
                    }
                ],
                chart: {
                    height: 374,
                    type: "line",
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: ["#007bff", "#ff4d4d"],
                xaxis: {
                    categories: categories,
                    tickPlacement: 'on',
                    labels: {
                        rotate: -45
                    },
                    scrollbar: {
                        enabled: true
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return value.toLocaleString("vi-VN").replace(/\./g,
                                ",") + 'VND';
                        }
                    }
                }
            };

            chart = new ApexCharts(chartContainer, options);
            chart.render();
        }

        function updatePieChart(ratingData) {
            let pieChartContainer = document.querySelector("#rating-pie-chart");
            if (pieChart) pieChart.destroy();
            if (!ratingData || !ratingData.length) {
                pieChartContainer.innerHTML = '<div class="text-center p-4 text-muted">Không có đánh giá</div>';
                return;
            }

            let series = ratingData.map(item => parseFloat(item.total_courses));
            let labels = ratingData.map(item => `${item.rating} sao`);

            let options = {
                series: series,
                chart: {
                    type: "pie",
                    height: 350,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },
                labels: labels,
                legend: {
                    position: "bottom"
                }
            };

            pieChart = new ApexCharts(pieChartContainer, options);
            pieChart.render();
        }

        function updateCategoryRevenueChart(data = []) {
            let chartContainer = document.querySelector("#category-revenue-chart");

            if (categoryRevenueChart) categoryRevenueChart.destroy();

            if (!data || !data.length) {
                chartContainer.innerHTML = '<div class="text-center p-4 text-muted">Không có dữ liệu</div>';
                return;
            }

            let categories = data.map(item => item.category_name);
            let totalCoursesSeries = data.map(item => parseInt(item.total_courses));
            let totalEnrolledStudentsSeries = data.map(item => parseInt(item.total_enrolled_students));
            let totalInstructorsSeries = data.map(item => parseInt(item.total_instructors));

            let options = {
                series: [{
                        name: 'Số khóa học',
                        data: totalCoursesSeries
                    },
                    {
                        name: 'Số học viên',
                        data: totalEnrolledStudentsSeries
                    },
                    {
                        name: 'Số người hướng dẫn',
                        data: totalInstructorsSeries
                    }
                ],
                chart: {
                    height: "100%",
                    type: 'line',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                markers: {
                    size: 5
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Số lượng'
                    },
                    labels: {
                        formatter: function(val) {
                            return val.toLocaleString();
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toLocaleString();
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    offsetY: 0
                },
                colors: ['#008FFB', '#00E396', '#FEB019']
            };

            categoryRevenueChart = new ApexCharts(chartContainer, options);
            categoryRevenueChart.render();
        }

        function renderBestSellingCourses(data = []) {
            let chartContainer = document.querySelector("#bestSellingCourses");
            if (chartBestSellingCourses) chartBestSellingCourses.destroy();
            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let colors = ["#008FFB", "#00E396", "#FEB019", "#FF4560", "#775DD0", "#546E7A", "#26A69A", "#D7263D", "#F86624",
                "#1B998B"
            ];
            let options = {
                chart: {
                    height: "100%",
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },
                series: [{
                        name: 'Số lượng bán',
                        type: "bar",
                        data: data.data.map(item => item.total_sales)
                    },
                    {
                        name: "Doanh thu (triệu VND)",
                        type: "line",
                        data: data.data.map(item => item.total_revenue)
                    }
                ],
                yaxis: [{
                        labels: {
                            formatter: val => val.toLocaleString("vi-VN")
                        }
                    },
                    {
                        opposite: true,
                        labels: {
                            formatter: val => val.toLocaleString("vi-VN", {
                                style: "currency",
                                currency: "VND"
                            }).replace("₫", "")
                        }
                    }
                ],
                xaxis: {
                    categories: data.data.map((_, index) => index + 1)
                },
                plotOptions: {
                    bar: {
                        distributed: true,
                        borderRadius: 4
                    }
                },
                colors: colors,
                tooltip: {
                    y: {
                        formatter: (val, {
                            seriesIndex,
                            dataPointIndex
                        }) => {
                            if (seriesIndex === 0)
                                return `${data.data[dataPointIndex].name}: ${val.toLocaleString("vi-VN") + ' lượt bán'}`;
                            return `${data.data[dataPointIndex].name}: ${val.toLocaleString("vi-VN", { style: "currency", currency: "VND" })}`;
                        }
                    }
                }
            };

            chartBestSellingCourses = new ApexCharts(chartContainer, options);
            chartBestSellingCourses.render();
        }

        function renderTopInstructorsChart(data = []) {
            let chartContainer = document.querySelector("#renderTopInstructorsChart");
            if (chartTopInstructors) chartTopInstructors.destroy();
            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let options = {
                chart: {
                    height: "100%",
                    type: "area",
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },
                series: [{
                        name: "Doanh thu (VND)",
                        data: data.data.map(item => Number(item.total_revenue) || 0)
                    },
                    {
                        name: "Số khóa học",
                        data: data.data.map(item => Number(item.total_courses) || 0)
                    },
                    {
                        name: "Số học viên",
                        data: data.data.map(item => Number(item.total_enrolled_students) || 0)
                    }
                ],
                xaxis: {
                    categories: data.data.map(item => item.name || "Không rõ"),
                    labels: {
                        rotate: -20,
                        rotateAlways: true
                    }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    labels: {
                        formatter: val => val.toLocaleString("vi-VN")
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                colors: ["#008FFB", "#00E396", "#FEB019"],
                tooltip: {
                    y: {
                        formatter: (val, {
                            seriesIndex
                        }) => {
                            if (seriesIndex === 0)
                                return `Doanh thu: ${val.toLocaleString("vi-VN", { style: "currency", currency: "VND" })}`;
                            else if (seriesIndex === 1) return `Số khóa học: ${val}`;
                            return `Số học viên: ${val}`;
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3
                    }
                }
            };

            chartTopInstructors = new ApexCharts(chartContainer, options);
            chartTopInstructors.render();
        }

        function renderTopStudentsChart(data = []) {
            let chartContainer = document.querySelector("#renderTopStudentsChart");
            if (chartTopStudents) chartTopStudents.destroy();
            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let options = {
                chart: {
                    height: "100%",
                    type: "bubble",
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },
                series: [{
                    name: "Học viên",
                    data: data.data.map(item => ({
                        x: item.name || "Không rõ",
                        y: Number(item.total_spent) || 0,
                        z: Number(item.total_courses_purchased) || 0
                    }))
                }],
                xaxis: {
                    type: "category",
                    labels: {
                        rotate: -20,
                        rotateAlways: true
                    }
                },
                yaxis: {
                    min: 0,
                    labels: {
                        formatter: val => val.toLocaleString("vi-VN", {
                            style: "currency",
                            currency: "VND"
                        })
                    }
                },
                tooltip: {
                    y: {
                        formatter: val =>
                            `Doanh thu: ${val.toLocaleString("vi-VN", { style: "currency", currency: "VND" })}`
                    },
                    z: {
                        formatter: val => `Số lượt mua: ${val}`
                    }
                },
                colors: ["#008FFB"]
            };

            chartTopStudents = new ApexCharts(chartContainer, options);
            chartTopStudents.render();
        }

        function renderTopInstructorsFollow(data = []) {
            let chartContainer = document.querySelector("#topInstructorsChart");
            if (chartTopInstructorFollows) chartTopInstructorFollows.destroy();
            if (!data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let options = {
                series: [{
                    data: data.map(item => ({
                        x: `${item.name}`,
                        y: item.total_student,
                        custom: {
                            name: item.name,
                            follow: item.total_follow,
                            students: item.total_student
                        }
                    }))
                }],
                chart: {
                    type: "radar",
                    height: 350,
                    width: "100%",
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },
                colors: ["#008FFB", "#00E396", "#FEB019", "#FF4560", "#775DD0"],
                legend: {
                    show: false
                },
                tooltip: {
                    custom: ({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) => {
                        let data = w.config.series[seriesIndex].data[dataPointIndex].custom;
                        return `<div class="custom-tooltip">🧑‍🏫 <b>${data.name}</b><br>🔥 Follow: <b>${data.follow}</b><br>🎓 Học viên: <b>${data.students}</b></div>`;
                    }
                }
            };

            chartTopInstructorFollows = new ApexCharts(chartContainer, options);
            chartTopInstructorFollows.render();
        }

        function renderTopCompletedCourses(data = []) {
            let chartContainer = document.querySelector("#topCompletedCourses");
            if (chartTopCompletedCourses) chartTopCompletedCourses.destroy();
            if (!data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let options = {
                chart: {
                    type: 'bar',
                    height: 330,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    }
                },
                series: [{
                    name: 'Tỷ lệ hoàn thành (%)',
                    data: data.map(item => item.avg_progress)
                }],
                xaxis: {
                    categories: data.map((item, index) => index + 1),
                    labels: {
                        formatter: val => Math.round(val)
                    }
                },
                yaxis: {
                    labels: {
                        formatter: val => val.toString()
                    }
                },
                legend: {
                    show: false
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        distributed: true
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: val => `${val}%`
                },
                tooltip: {
                    y: {
                        formatter: (val, {
                            dataPointIndex
                        }) => `${data[dataPointIndex]?.course?.name || 'N/A'}: ${val}%`
                    }
                }
            };

            chartTopCompletedCourses = new ApexCharts(chartContainer, options);
            chartTopCompletedCourses.render();
        }

        $(document).on('click', '#pagination-links-courses a', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            loadCoursesContent({
                page: page
            });
        });

        $(document).on('click', '#pagination-links-instructors a', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            loadInstructorsContent({
                page: page
            });
        });

        $(document).on('click', '#pagination-links-users a', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            loadUsersContent({
                page: page
            });
        });

        function loadCoursesContent(dataFilter) {
            dataFilter.type = "courses";
            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                type: "GET",
                data: dataFilter,
                dataType: "json",
                success: function(data) {
                    $('#table-courses tbody').html(data.top_courses_table);
                    $('#pagination-links-courses').html(data.pagination_links_courses);
                    topCourse = data.topCourses;
                    if ($('#bestSellingCourses').is(':visible')) renderBestSellingCourses(topCourse);
                }
            });
        }

        function loadInstructorsContent(dataFilter) {
            dataFilter.type = 'instructors';
            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                type: "GET",
                data: dataFilter,
                dataType: "json",
                success: function(data) {
                    $('#table-instructors tbody').html(data.top_instructors_table);
                    $('#pagination-links-instructors').html(data.pagination_links_instructors);
                    topInstructor = data.topInstructors;
                    if ($('#renderTopInstructorsChart').is(':visible')) renderTopInstructorsChart(
                        topInstructor);
                }
            });
        }

        function loadUsersContent(dataFilter) {
            dataFilter.type = 'user';
            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                type: "GET",
                data: dataFilter,
                dataType: "json",
                success: function(data) {
                    $('#table-students tbody').html(data.top_users_table);
                    $('#pagination-links-users').html(data.pagination_links_users);
                    topStudent = data.topUsers;
                    if ($('#renderTopStudentsChart').is(':visible')) renderTopStudentsChart(topStudent);
                }
            });
        }

        function loadApexCharts(filterData) {
            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                type: "GET",
                data: filterData,
                success: function(response) {
                    updateChart(response.apexCharts);
                }
            });
        }

        function loadCourseRatingCharts(filterData) {
            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                type: "GET",
                data: filterData,
                success: function(response) {
                    updatePieChart(response.course_rating);
                }
            });
        }

        function loadAll(filterData) {
            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                type: "GET",
                data: filterData,
                success: function(response) {
                    console.log(response.topCourses);

                    topCourse = response.topCourses;
                    topInstructor = response.topInstructors;
                    topStudent = response.topUsers;

                    $('#table-students tbody').html(response.top_users_table);
                    $('#pagination-links-users').html(response.pagination_links_users);

                    $('#table-instructors tbody').html(response.top_instructors_table);
                    $('#pagination-links-instructors').html(response.pagination_links_instructors);

                    $('#table-courses tbody').html(response.top_courses_table);
                    $('#pagination-links-courses').html(response.pagination_links_courses);

                    updateChart(response.apexCharts);
                    updatePieChart(response.course_rating);
                    renderTopCompletedCourses(response.topCoursesProgress);
                    renderTopInstructorsFollow(response.topInstructorsFollows);
                    updateCategoryRevenueChart(response.categoryStats);

                    $('.counter-value[data-target="totalRevenue"]').text(new Intl.NumberFormat('vi-VN', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(response.totalAmount.total_revenue || 0));

                    $('.counter-value[data-target="totalProfit"]').text(new Intl.NumberFormat('vi-VN', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(response.totalAmount.total_profit || 0));

                    $('.counter-value[data-target="totalCourse"]').text(new Intl.NumberFormat('vi-VN', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(response.totalCourse || 0));

                    $('.counter-value[data-target="totalInstructor"]').text(new Intl.NumberFormat('vi-VN', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(response.totalInstructor || 0));

                    $('.counter-value-revenue').text(new Intl.NumberFormat('vi-VN', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(response.totalAmount.total_revenue || 0));

                    $('.counter-value-profit').text(new Intl.NumberFormat('vi-VN', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(response.totalAmount.total_profit || 0));

                    if ($('#bestSellingCourses').is(':visible')) renderBestSellingCourses(topCourse);
                    if ($('#renderTopInstructorsChart').is(':visible')) renderTopInstructorsChart(
                        topInstructor);
                    if ($('#renderTopStudentsChart').is(':visible')) renderTopStudentsChart(topStudent);
                }
            });
        }

        function getSelectedDateRange() {
            let button = $(".dateRangePicker");
            return {
                startDate: button.attr("data-start"),
                endDate: button.attr("data-end")
            };
        }

        $(document).on('click', '#showBestSellingCoursesButton', function(e) {
            e.preventDefault();
            let tableDiv = $('#table-courses').closest('.table-responsive');
            let chartDiv = $('#bestSellingCourses');
            let button = $(this);

            if (tableDiv.is(':visible')) {
                tableDiv.hide();
                if (chartDiv.length === 0) {
                    $('#showBestSellingCoursesDiv').append(
                        '<div id="bestSellingCourses" class="apex-charts"></div>');
                    renderBestSellingCourses(topCourse);
                } else chartDiv.show();
                button.text('Xem bảng');
            } else {
                if (chartBestSellingCourses) chartBestSellingCourses.destroy();
                $('#bestSellingCourses').remove();
                tableDiv.show();
                button.text('Xem biểu đồ');
            }
        });

        $(document).on('click', '#showTopInstructorButton', function(e) {
            e.preventDefault();
            let tableDiv = $('#table-instructors').closest('.table-responsive');
            let chartDiv = $('#renderTopInstructorsChart');
            let button = $(this);

            if (tableDiv.is(':visible')) {
                tableDiv.hide();
                if (chartDiv.length === 0) {
                    $('#showTopInstructorDiv').append(
                        '<div id="renderTopInstructorsChart" class="apex-charts"></div>');
                    renderTopInstructorsChart(topInstructor);
                } else chartDiv.show();
                button.text('Xem bảng');
            } else {
                if (chartTopInstructors) chartTopInstructors.destroy();
                $('#renderTopInstructorsChart').remove();
                tableDiv.show();
                button.text('Xem biểu đồ');
            }
        });

        $(document).on('click', '#showRenderTopStudentsButton', function(e) {
            e.preventDefault();
            let tableDiv = $('#table-students').closest('.table-responsive');
            let chartDiv = $('#renderTopStudentsChart');
            let button = $(this);

            if (tableDiv.is(':visible')) {
                tableDiv.hide();
                if (chartDiv.length === 0) {
                    $('#showRenderTopStudentsDiv').append(
                        '<div id="renderTopStudentsChart" class="apex-charts"></div>');
                    renderTopStudentsChart(topStudent);
                } else chartDiv.show();
                button.text('Xem bảng');
            } else {
                if (chartTopStudents) chartTopStudents.destroy();
                $('#renderTopStudentsChart').remove();
                tableDiv.show();
                button.text('Xem biểu đồ');
            }
        });

        $(document).on('click', '.dowloadExcel', function() {
            let type_export = $(this).data('type');
            let data_export;

            if (type_export == 'top_instructor') {
                data_export = topInstructor.data;
            } else if (type_export == 'top_course') {
                data_export = topCourse.data;
            } else if (type_export == 'top_student') {
                data_export = topStudent.data;
            } else if (type_export == 'top_category') {
                data_export = topCategory;
            } else {
                return;
            }

            if (!data_export || !Array.isArray(data_export)) {
                return;
            }

            $.ajax({
                url: "{{ route('admin.dashboard.export') }}",
                method: 'POST',
                data: {
                    type: type_export,
                    data: data_export,
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    let filename = `${type_export}_export.xlsx`;
                    const disposition = xhr.getResponseHeader('Content-Disposition');

                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const matches = /filename="([^"]*)"/.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1];
                    }

                    const blob = new Blob([response]);
                    const url = window.URL.createObjectURL(blob);
                    const tempLink = document.createElement('a');
                    tempLink.style.display = 'none';
                    tempLink.href = url;
                    tempLink.setAttribute('download', filename);
                    document.body.appendChild(tempLink);
                    tempLink.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(tempLink);
                }
            });
        });

        updateChart(newData);
        updatePieChart(ratingData);
        renderTopCompletedCourses(@json($topCoursesProgress));
        renderTopInstructorsFollow(@json($topInstructorsFollows));
        updateCategoryRevenueChart(topCategory);

        new Swiper('.marketplace-swiper', {
            slidesPerView: 4,
            spaceBetween: 20,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },
            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 4
                }
            }
        });
    </script>
@endpush
