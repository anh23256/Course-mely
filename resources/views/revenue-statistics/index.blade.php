@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <style>
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #999;
            padding: 20px;
        }

        .highcharts-series rect {
            transition: all 0.3s ease-in-out;
        }

        .highcharts-series rect:hover {
            filter: brightness(1.2);
            transform: scale(1.05);
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="h-100">
                    <!-- Greeting -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="bg-white p-4 rounded shadow-sm">
                                <h4 class="fs-20 mb-1 text-primary" id="greeting">Xin chào, {{ Auth::user()->name ?? '' }}!
                                </h4>
                                <p class="text-muted mb-0">Chúc bạn một ngày làm việc hiệu quả!</p>
                            </div>
                        </div>
                    </div>
                    <!-- Revenue Chart -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div
                                    class="card-header bg-primary bg-gradient bg-opacity-60 border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1 text-white">Doanh thu 2025 CourseMeLy</h4>
                                    <div class="dateRangePicker btn btn-outline-warning rounded-pill px-3"
                                        data-filter="totalRevenueCourseMely"></div>
                                </div>
                                <!-- end card header -->
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
                    <div class="row mt-2 g-4">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                                    <h4 class="card-title mb-0 flex-grow-1 text-white">Người hướng dẫn nổi bật</h4>
                                    <button class="badge bg-warning rounded-5 dowloadExcel" data-type="top_instructor"><i
                                            class='fs-9 bx bx-download'> Excel</i></button>
                                    <button class="fs-7 badge bg-primary mx-2" id="showTopInstructorButton">Xem biểu
                                        đồ</button>
                                    <div class="dateRangePicker btn btn-outline-warning rounded-pill"
                                        data-filter="topInstructorCourseMely"
                                        style="padding: 2px 6px; font-size: 10px; height: auto; min-width: auto; width: fit-content; display: inline-block;">
                                    </div>
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
                                                                    <h5 class="fs-14 my-1 fw-medium">
                                                                        {{ $topInstructor->name ?? '' }}
                                                                    </h5>
                                                                    <span class="text-muted">Tham gia
                                                                        {{ \Carbon\Carbon::parse($topInstructor->created_at)->format('d/m/Y') ?? '' }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $topInstructor->total_courses ?? '' }}
                                                        </td>
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
                                <div class="card-header d-flex align-items-center bg-primary bg-gradient bg-opacity-60">
                                    <h4 class="card-title mb-0 flex-grow-1 text-white">Top khoá học bán chạy</h4>
                                    <button class="badge bg-warning rounded-5 dowloadExcel" data-type="top_course"><i
                                            class='fs-9 bx bx-download'> Excel</i></button>
                                    <button class="fs-7 badge bg-primary mx-2" id="showBestSellingCoursesButton">Xem biểu
                                        đồ
                                    </button>
                                    <div class="dateRangePicker btn btn-outline-warning rounded-pill"
                                        data-filter="topCourseBoughtCourseMely"
                                        style="padding: 2px 6px; font-size: 10px; height: auto; min-width: auto; width: fit-content; display: inline-block;">
                                    </div>
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
                                                                        {{ \Illuminate\Support\Str::limit($topCourse->name, 20) }}
                                                                    </h5>
                                                                    <span
                                                                        class="text-muted">{{ \Carbon\Carbon::parse($topCourse->created_at)->format('d/m/Y') }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $topCourse->total_sales }}</td>
                                                        <td class="text-center">{{ $topCourse->total_enrolled_students }}
                                                        </td>
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
                    <div class="row mt-5 d-flex">
                        <div class="col-xl-12 d-flex">
                            <div class="card w-100 h-100">
                                <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                                    <h4 class="card-title mb-0 flex-grow-1 text-white">Top học viên</h4>
                                    <button class="badge bg-warning rounded-5 dowloadExcel" data-type="top_student">
                                        <i class='fs-9 bx bx-download'> Excel</i>
                                    </button>
                                    <button class="fs-7 badge bg-primary mx-2" id="showRenderTopStudentsButton">Xem biểu
                                        đồ</button>
                                    <div class="dateRangePicker btn btn-outline-warning rounded-pill"
                                        data-filter="topStudentCourseMely"
                                        style="padding: 2px 6px; font-size: 10px; height: auto; min-width: auto; width: fit-content; display: inline-block;">
                                    </div>
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

                </div> <!-- end .h-100-->

            </div> <!-- end col -->
        </div>

    </div>
@endsection
@push('page-scripts')
    <!-- Vector map-->
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}" />
    <script src="{{ asset('assets/js/pages/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/daterangepicker.min.js') }}"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/annotations.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://code.highcharts.com/modules/heatmap.js"></script>
    <script src="https://code.highcharts.com/modules/packed-bubble.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>
    <script>
        var currentHour = new Date().getHours();
        var greetingText = "Xin chào, {{ Auth::user()->name ?? 'Quản trị viên' }}!";

        if (currentHour >= 5 && currentHour < 12) {
            greetingText = "Chào buổi sáng, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        } else if (currentHour >= 12 && currentHour < 18) {
            greetingText = "Chào buổi chiều, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        } else if (currentHour >= 18 && currentHour < 22) {
            greetingText = "Chào buổi tối, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        } else {
            greetingText = "Chúc ngủ ngon, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        }

        $("#greeting").text(greetingText);
        $(document).ready(function() {
            $(document).on('click', '#pagination-links-courses a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                const keySelected = ".dateRangePicker[data-filter='topCourseBoughtCourseMely']";

                let dataFilter = getSelectedDateRange(keySelected);

                dataFilter.page = page;

                loadCoursesContent(dataFilter);
            });

            $(document).on('click', '#pagination-links-instructors a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                const keySelected = ".dateRangePicker[data-filter='topInstructorCourseMely']";

                let dataFilter = getSelectedDateRange(keySelected);

                dataFilter.page = page;

                loadInstructorsContent(dataFilter);
            });

            $(document).on('click', '#pagination-links-users a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                const keySelected = ".dateRangePicker[data-filter='topStudentCourseMely']";

                let dataFilter = getSelectedDateRange(keySelected);

                dataFilter.page = page;

                loadUsersContent(dataFilter);
            });

            function loadCoursesContent(dataFilter) {
                dataFilter.type = "courses";
                $.ajax({
                    url: "{{ route('admin.revenue-statistics.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        console.log(data);

                        $('#table-courses tbody').html(data.top_courses_table);
                        $('#pagination-links-courses').html(data.pagination_links_courses);
                        topCourse = data.topCourse;
                        renderBestSellingCourses(data.topCourses);
                    }
                });
            }

            function loadInstructorsContent(dataFilter) {
                dataFilter.type = 'instructors';
                $.ajax({
                    url: "{{ route('admin.revenue-statistics.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        console.log(data);
                        topInstructor = data.topInstructor;
                        $('#table-instructors tbody').html(data.top_instructors_table);
                        $('#pagination-links-instructors').html(data.pagination_links_instructors);
                        renderTopInstructorsChart(data.topInstructors);
                    }
                });
            }

            function loadUsersContent(dataFilter) {
                dataFilter.type = 'user';
                $.ajax({
                    url: "{{ route('admin.revenue-statistics.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        console.log(data);
                        topStudent = data.topUsers;
                        $('#table-students tbody').html(data.top_users_table);
                        $('#pagination-links-users').html(data.pagination_links_users);
                        renderTopStudentsChart(data.topUsers);
                    }
                });
            }

            function loadApexCharts(filterData) {
                $.ajax({
                    url: "{{ route('admin.revenue-statistics.index') }}",
                    type: "GET",
                    data: filterData,
                    success: function(response) {
                        updateChart(response.system_Funds);
                        console.log(response);
                    }
                });
            }

            $(".dateRangePicker").each(function() {
                let button = $(this);
                let filter = $(this).data('filter');

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
                            "Tháng trước": [
                                moment().subtract(1, "month").startOf("month"),
                                moment().subtract(1, "month").endOf("month"),
                            ],
                            "1 năm trước": [
                                moment().subtract(1, "year").startOf("year"),
                                moment().subtract(1, "year").endOf("year")
                            ],

                        },
                        locale: {
                            format: "DD/MM/YYYY",
                            applyLabel: "Áp dụng",
                            cancelLabel: "Hủy",
                            customRangeLabel: "Tùy chỉnh",
                        },
                    },
                    function(start, end) {
                        updateDateRangeText(start, end);

                        let data = {
                            startDate: start.format("YYYY-MM-DD"),
                            endDate: end.format("YYYY-MM-DD"),
                            filter: filter,
                            page: 1,
                        };

                        console.log("Filter:", filter, "data:", data);

                        if (filter == "totalRevenueCourseMely") {
                            loadApexCharts(data);
                        } else if (filter == "topInstructorCourseMely") {
                            loadInstructorsContent(data);
                        } else if (filter == "topCourseBoughtCourseMely") {
                            loadCoursesContent(data);
                        } else if (filter == "topStudentCourseMely") {
                            loadUsersContent(data);
                        }
                    }
                );

                updateDateRangeText(defaultStart, defaultEnd);
            });

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

        });

        var topCourse = @json($topCourses);
        var topInstructor = @json($topInstructors);
        var system_Funds = @json($system_Funds);
        var topStudent = @json($topUsers);
        let chart, chartBestSellingCourses, chartTopInstructors, chartTopStudents;

        function updateChart(data) {
            let chartContainer = document.querySelector("#projects-overview-chart");
            chartContainer.innerHTML = "";

            if (!data || data.length === 0) {
                chartContainer.innerHTML = `<div class="no-data">Chưa có doanh thu</div>`;
                return;
            }

            let categories = data.map(item => `${item.month}/${item.year}`);
            let profitData = data.map(item => parseFloat(item.total_profit));

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'area',
                    zooming: {
                        type: 'x'
                    },
                    panning: true,
                    panKey: 'shift',
                    scrollablePlotArea: {
                        minWidth: 600
                    },
                    backgroundColor: null,
                    height: "40%"
                },
                title: {
                    text: 'Biểu đồ lợi nhuận Course MeLy'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: categories,
                    labels: {
                        format: '{value}'
                    },
                    title: {
                        text: 'Thời gian'
                    }
                },
                yAxis: {
                    startOnTick: true,
                    endOnTick: false,
                    title: {
                        text: 'Lợi nhuận (VND)'
                    },
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString() + " VND";
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    pointFormat: '<b>{point.y} VND</b>'
                },
                legend: {
                    enabled: false
                },
                series: [{
                    name: 'Lợi nhuận',
                    data: profitData,
                    lineColor: Highcharts.getOptions().colors[1],
                    color: Highcharts.getOptions().colors[2],
                    fillOpacity: 0.5,
                    marker: {
                        enabled: false
                    },
                    threshold: null
                }]
            });
        }

        function renderBestSellingCourses(data = []) {
            let chartContainer = document.querySelector("#bestSellingCourses");
            if (!chartContainer) return;
            chartContainer.innerHTML = "";

            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.data.map(item => item.name);
            let salesData = data.data.map(item => Number(item.total_sales) || 0);
            let revenueData = data.data.map(item => Number(item.total_revenue) || 0);

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'column',
                    height: "100%",
                    backgroundColor: null
                },
                title: {
                    text: 'Khóa học có doanh thu lớn nhất'
                },
                exporting: {
                    enabled: true,
                    buttons: {
                        contextButton: {
                            menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                        }
                    }
                },
                xAxis: {
                    categories: categories.map(name => name.length > 20 ? name.substring(0, 17) + "..." : name),
                    crosshair: true
                },
                credits: {
                    enabled: false
                },
                yAxis: [{
                        title: {
                            text: 'Số lượng bán'
                        },
                        labels: {
                            formatter: function() {
                                return this.value.toLocaleString("vi-VN");
                            }
                        }
                    },
                    {
                        title: {
                            text: 'Doanh thu (VND)'
                        },
                        opposite: true,
                        labels: {
                            formatter: function() {
                                return this.value.toLocaleString("vi-VN", {
                                    style: "currency",
                                    currency: "VND"
                                });
                            }
                        }
                    }
                ],
                tooltip: {
                    shared: true,
                    formatter: function() {
                        let index = this.points[0].point.index;
                        return `<b>${categories[index]}</b><br>
                <span style="color:#008FFB">●</span> Số lượng bán: <b>${salesData[index].toLocaleString("vi-VN")}</b><br>
                <span style="color:#FF4560">●</span> Doanh thu: <b>${revenueData[index].toLocaleString("vi-VN", { 
                    style: "currency", 
                    currency: "VND" 
                })}</b>`;
                    }
                },
                plotOptions: {
                    column: {
                        borderRadius: 5,
                        grouping: true,
                        shadow: true
                    },
                    areaspline: {
                        fillOpacity: 0.2
                    }
                },
                series: [{
                        name: 'Số lượng bán',
                        type: 'column',
                        data: salesData,
                        color: '#008FFB'
                    },
                    {
                        name: 'Doanh thu (VND)',
                        type: 'areaspline',
                        yAxis: 1,
                        data: revenueData,
                        color: '#FF4560',
                        lineWidth: 3,
                        marker: {
                            radius: 5,
                            symbol: 'circle'
                        }
                    }
                ]
            });
        }

        function renderTopInstructorsChart(data = []) {
            let chartContainer = document.querySelector("#renderTopInstructorsChart");
            if (!chartContainer) return;
            chartContainer.innerHTML = "";

            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.data.map(item => item.name || "Không rõ");
            let revenueData = [],
                courseData = [],
                studentData = [];

            data.data.forEach((item, index) => {
                revenueData.push({
                    x: index,
                    y: 0,
                    value: Number(item.total_revenue) || 0
                });
                courseData.push({
                    x: index,
                    y: 1,
                    value: Number(item.total_courses) || 0
                });
                studentData.push({
                    x: index,
                    y: 2,
                    value: Number(item.total_enrolled_students) || 0
                });
            });

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'heatmap',
                    plotBorderWidth: 1,
                    height: "100%"
                },
                title: {
                    text: 'Giảng viên có doanh thu cao nhất'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: categories,
                    title: {
                        text: "Giảng viên"
                    },
                    labels: {
                        rotation: -45
                    }
                },
                yAxis: {
                    categories: ["Doanh thu (VND)", "Số khóa học", "Số học viên"],
                    title: null
                },
                colorAxis: [{
                    min: 0,
                    stops: [
                        [0, '#E3F2FD'],
                        [0.5, '#42A5F5'],
                        [1, '#0D47A1']
                    ]
                }, {
                    min: 0,
                    stops: [
                        [0, '#FFF3E0'],
                        [0.5, '#FFA726'],
                        [1, '#E65100']
                    ]
                }, {
                    min: 0,
                    stops: [
                        [0, '#FFEBEE'],
                        [0.5, '#EF5350'],
                        [1, '#B71C1C']
                    ]
                }],
                legend: {
                    align: 'right',
                    layout: 'vertical',
                    margin: 0,
                    verticalAlign: 'top',
                    y: 25,
                    symbolHeight: 280
                },
                tooltip: {
                    formatter: function() {
                        return `<b>${categories[this.point.x]}</b><br>${this.series.name}: ${this.point.value.toLocaleString("vi-VN")}`;
                    }
                },
                series: [{
                        name: "Doanh thu (VND)",
                        borderWidth: 1,
                        data: revenueData,
                        colorAxis: 0,
                        dataLabels: {
                            enabled: true,
                            color: '#000000'
                        }
                    },
                    {
                        name: "Số khóa học",
                        borderWidth: 1,
                        data: courseData,
                        colorAxis: 1,
                        dataLabels: {
                            enabled: true,
                            color: '#000000'
                        }
                    },
                    {
                        name: "Số học viên",
                        borderWidth: 1,
                        data: studentData,
                        colorAxis: 2,
                        dataLabels: {
                            enabled: true,
                            color: '#000000'
                        }
                    }
                ]
            });
        }

        function renderTopStudentsChart(data = []) {
            let chartContainer = document.querySelector("#renderTopStudentsChart");

            if (!chartContainer) return;
            chartContainer.innerHTML = "";

            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let seriesData = [];
            data.data.forEach(item => {
                let student = item.name || "Không rõ";
                let totalSpent = Number(item.total_spent) || 0;
                let totalCourses = Number(item.total_courses_purchased) || 0;

                if (totalCourses > 0) {
                    seriesData.push([student, "Số khóa học đã mua", totalCourses]);
                }
                if (totalSpent > 0) {
                    seriesData.push([student, "Tổng số tiền chi tiêu", totalSpent]);
                }
            });

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'sankey',
                    height: "50%"
                },
                title: {
                    text: 'Chi tiêu của học viên'
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    pointFormatter: function() {
                        let unit = this.to === "Số khóa học đã mua" ? " khóa học" : " VND";
                        return `<b>${this.from}</b> → <b>${this.to}</b>: <b>${this.weight.toLocaleString("vi-VN")}${unit}</b>`;
                    }
                },
                series: [{
                    keys: ['from', 'to', 'weight'],
                    data: seriesData,
                    colors: ["#007BFF", "#28A745", "#FFC107"],
                    dataLabels: {
                        color: "#333",
                        style: {
                            fontWeight: "bold"
                        }
                    },
                    nodes: [{
                        id: "Số khóa học đã mua",
                        color: "#28A745"
                    }, {
                        id: "Tổng số tiền chi tiêu",
                        color: "#FFC107"
                    }]
                }]
            });
        }
        $(document).on('click', '.dowloadExcel', function() {
            let type_export = $(this).data('type');
            let data_export;

            if (type_export == 'top_instructor') {
                data_export = topInstructor.data;
            } else if (type_export == 'top_course') {
                data_export = topCourse.data;
            } else if (type_export == 'top_student') {
                data_export = topStudent.data;
            } else {
                return;
            }

            if (!data_export || !Array.isArray(data_export)) {
                return;
            }

            $.ajax({
                url: "{{ route('admin.revenue-statistics.export') }}",
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
        updateChart(system_Funds);

        function getSelectedDateRange() {
            let button = $(".dateRangePicker");
            return {
                startDate: button.attr("data-start"),
                endDate: button.attr("data-end")
            };
        }
    </script>
@endpush
