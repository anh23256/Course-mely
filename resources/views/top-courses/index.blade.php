@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
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
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Top Khóa Học Bán Chạy</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                            <li class="breadcrumb-item active">Top Khóa Học Bán Chạy</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
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

        <div class="row g-4">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center bg-primary bg-gradient bg-opacity-60">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Top khoá học có doanh thu cao nhất</h4>
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
                                                    <img style="width:70px" src="{{ $topCourse->thumbnail }}" alt=""
                                                        class="img-fluid d-block" />
                                                    <div>
                                                        <h5 class="fs-14 my-1"
                                                            style="white-space: normal; word-wrap: break-word; overflow-wrap: break-word;">
                                                            {{ \Illuminate\Support\Str::limit($topCourse->name, 40) }}
                                                        </h5>
                                                        <span
                                                            class="text-muted">{{ \Carbon\Carbon::parse($topCourse->created_at)->format('d/m/Y') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $topCourse->total_sales }}</td>
                                            <td>{{ $topCourse->total_enrolled_students }}
                                            </td>
                                            <td>{{ number_format($topCourse->total_revenue) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2 mb-4">
            <div class="col-xxl-7 d-flex">
                <div class="card w-100 h-100">
                    <div
                        class="card-header d-flex align-items-center justify-content-between bg-primary bg-gradient bg-opacity-60">
                        <div class="d-flex align-items-center">
                            <img src="https://img.themesbrand.com/velzon/images/img-2.gif"
                                class="avatar-xs rounded-circle object-fit-cover" alt="">
                            <h4 class="card-title mb-0 mx-2 text-white">Top 10 khóa học có tỉ lệ hoàn thành cao nhất</h4>
                        </div>
                        <div class="dateRangePicker btn btn-outline-warning rounded-pill" data-filter="topCompletedCourse"
                            style="padding: 4px 8px; font-size: 12px; height: auto; min-width: auto; width: fit-content; display: inline-block;">
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="topCompletedCourses" class="w-100"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 d-flex">
                <div class="card w-100 h-100">
                    <div
                        class="card-header d-flex align-items-center justify-content-between bg-primary bg-gradient bg-opacity-60">
                        <h4 class="card-title mb-0 text-white">Đánh giá khoá học</h4>
                        <div class="dateRangePicker btn btn-outline-warning rounded-pill" data-filter="topRatingCourse"
                            style="padding: 4px 8px; font-size: 12px; height: auto; min-width: auto; width: fit-content; display: inline-block;">
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="rating-pie-chart" class="w-100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Courses Views -->
        <div class="row mt-2">
            <div class="col-xxl-12">
                <div class="card border-0 shadow">
                    <div
                        class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center justify-content-between p-3">
                        <h4 class="card-title mb-0 fw-bold text-white">
                            <i class="ri-award-fill me-2"></i>Top 10 khóa học
                        </h4>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-filter-3-line"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item active course-filter" href="#"
                                        data-filter-course="views">Xem nhiều nhất</a></li>
                                <li><a class="dropdown-item course-filter" href="#"
                                        data-filter-course="created_at">Mới nhất</a></li>
                                <li><a class="dropdown-item course-filter" href="#" data-filter-course="price_asc">Giá
                                        thấp đến cao</a></li>
                                <li><a class="dropdown-item course-filter" href="#"
                                        data-filter-course="price_desc">Giá cao đến thấp</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="swiper marketplace-swiper rounded gallery-light">
                            <div class="swiper-wrapper py-3" id="top-course-view">
                                @foreach ($getTopViewCourses as $getTopViewCourse)
                                    <div class="swiper-slide h-100">
                                        <div
                                            class="card explore-box card-animate rounded-lg overflow-hidden h-100 shadow-sm border-0 position-relative">
                                            @if ($getTopViewCourse->is_free)
                                                <div class="ribbon ribbon-primary ribbon-shape position-absolute">
                                                    <span>Miễn phí</span>
                                                </div>
                                            @elseif($getTopViewCourse->price_sale > 0)
                                                @php
                                                    $discount = round(
                                                        (1 - $getTopViewCourse->price_sale / $getTopViewCourse->price) *
                                                            100,
                                                    );
                                                @endphp
                                                <div class="ribbon ribbon-danger ribbon-shape position-absolute">
                                                    <span>-{{ $discount }}%</span>
                                                </div>
                                            @endif

                                            <div class="explore-place-bid-img position-relative">
                                                <img src="{{ $getTopViewCourse->thumbnail }}"
                                                    alt="{{ $getTopViewCourse->name }}"
                                                    class="img-fluid card-img-top explore-img"
                                                    style="max-height: 190px; width: 100%; object-fit: cover;">
                                                <div class="bg-overlay bg-dark opacity-25"></div>

                                                <div class="position-absolute bottom-0 start-0 w-100 p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs me-2 bg-white rounded-circle p-1">
                                                                <img src="{{ $getTopViewCourse->instructor_avatar ?? '' }}"
                                                                    alt=""
                                                                    class="rounded-circle w-100 h-100 object-fit-cover">
                                                            </div>
                                                            <span
                                                                class="text-white fw-medium text-shadow">{{ $getTopViewCourse->instructor_name }}</span>
                                                        </div>
                                                        <span class="badge bg-primary rounded-pill fs-11 px-2 py-1">
                                                            <i
                                                                class="mdi mdi-eye align-middle me-1"></i>{{ number_format($getTopViewCourse->views) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body p-3">
                                                <h5 class="mb-3 fw-semibold text-truncate course-title"
                                                    title="{{ $getTopViewCourse->name }}">
                                                    {{ $getTopViewCourse->name }}
                                                </h5>

                                                <div class="d-flex align-items-center mb-2">
                                                    @if ($getTopViewCourse->is_free)
                                                        <span class="badge bg-success me-1">Miễn phí</span>
                                                    @elseif($getTopViewCourse->price_sale > 0)
                                                        <span
                                                            class="fs-15 fw-semibold text-success">{{ number_format($getTopViewCourse->price_sale) }}
                                                            VND</span>
                                                        <span
                                                            class="text-muted text-decoration-line-through ms-2 fs-13">{{ number_format($getTopViewCourse->price) }}
                                                            VND</span>
                                                        @php
                                                            $discount = round(
                                                                (1 -
                                                                    $getTopViewCourse->price_sale /
                                                                        $getTopViewCourse->price) *
                                                                    100,
                                                            );
                                                        @endphp
                                                        <span class="badge bg-danger ms-auto">-{{ $discount }}%</span>
                                                    @elseif($getTopViewCourse->price > 0)
                                                        <span
                                                            class="fs-15 fw-semibold">{{ number_format($getTopViewCourse->price) }}
                                                            VND</span>
                                                    @else
                                                        <span class="badge bg-success">Miễn phí</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="card-footer bg-light p-3 border-top">
                                                <div class="d-grid gap-2">
                                                    <a href="{{ config('app.fe_url') }}courses/{{ $getTopViewCourse->slug }}"
                                                        target="_blank" class="btn btn-primary btn-sm">
                                                        <i class="ri-eye-line align-bottom me-1"></i>
                                                        Xem chi tiết
                                                    </a>
                                                    <a href="{{ route('admin.courses.show', $getTopViewCourse->id) }}"
                                                        class="btn btn-outline-secondary btn-sm">
                                                        <i class="ri-settings-3-line align-bottom me-1"></i>
                                                        Quản lý
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-next swiper-nav-btn"></div>
                            <div class="swiper-button-prev swiper-nav-btn"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('page-scripts')
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
        var topCourse = @json($topCourses);
        var ratingData = @json($courseRatings);
        let pieChart, chartTopCompletedCourses, chartBestSellingCourses;

        $(document).ready(function() {

            function loadCoursesContent(dataFilter) {
                dataFilter.type = "courses";
                $.ajax({
                    url: "{{ route('admin.top-courses.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        console.log(data);
                        
                        $('#table-courses tbody').html(data.top_courses_table);
                        $('#pagination-links-courses').html(data.pagination_links_courses);
                        topCourse = data.topCourses;
                        console.log(topCourse);
                        
                        renderBestSellingCourses(data.topCourses);
                    }
                });
            }

            function loadRatingCourse(dataFilter) {
                $.ajax({
                    url: "{{ route('admin.top-courses.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        ratingData = data.course_rating;
                        updatePieChart(ratingData);
                    }
                });
            }

            function loadCompletedCourse(dataFilter) {
                $.ajax({
                    url: "{{ route('admin.top-courses.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        renderTopCompletedCourses(data.topCoursesProgress);
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
                            page: 1,
                        };

                        if (filter == "topCourseBoughtCourseMely") {
                            loadCoursesContent(data);
                        } else if (filter == "topCompletedCourse") {
                            loadCompletedCourse(data);
                        } else if (filter == "topRatingCourse") {
                            loadRatingCourse(data);
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

            $(document).on('click', '.dowloadExcel', function() {
                let type_export = $(this).data('type');
                let data_export;

                if (type_export == 'top_course') {
                    data_export = topCourse.data;
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
        })

        function renderTopCompletedCourses(data = []) {
            let chartContainer = document.querySelector("#topCompletedCourses");
            chartContainer.innerHTML = "";

            if (!data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'bar',
                    height: 330
                },
                title: {
                    text: 'Tỷ lệ hoàn thành khóa học',
                    align: 'center'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: data.map(item => {
                        let name = item.course?.name || "N/A";
                        return name.length > 25 ? name.substring(0, 17) + "..." : name;
                    }),
                    title: {
                        text: 'Khoá học'
                    }
                },
                yAxis: {
                    min: 0,
                    max: 100,
                    title: {
                        text: 'Tỷ lệ hoàn thành (%)'
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '<b>{point.category}</b>: {point.y}% hoàn thành'
                },
                series: [{
                    name: 'Tỷ lệ hoàn thành',
                    data: data.map(item => Number(item.avg_progress) || 0),
                    colorByPoint: true
                }],
                plotOptions: {
                    bar: {
                        dataLabels: {
                            enabled: true,
                            format: '{y}%'
                        }
                    }
                }
            });
        }

        function updatePieChart(ratingData) {
            let pieChartContainer = document.querySelector("#rating-pie-chart");
            pieChartContainer.innerHTML = "";

            if (!ratingData || !ratingData.length) {
                pieChartContainer.innerHTML = '<div class="no-data">Không có đánh giá</div>';
                return;
            }

            let series = ratingData.map(item => parseFloat(item.total_courses));
            let labels = ratingData.map(item => `${item.rating} sao`);

            Highcharts.chart(pieChartContainer, {
                chart: {
                    type: 'pie',
                    height: "80%",
                    backgroundColor: null,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutBounce'
                    }
                },
                credits: {
                    enabled: false
                },
                title: {
                    text: 'Tỉ lệ đánh giá khóa học'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        borderRadius: 10,
                        shadow: true,
                        size: '100%',
                    }
                },
                series: [{
                    name: 'Khóa học',
                    data: labels.map((label, index) => ({
                        name: label,
                        y: series[index]
                    })),
                    colors: ['#FF9800', '#F44336', '#4CAF50', '#03A9F4', '#9C27B0']
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
                    height: "50%",
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

        document.addEventListener('DOMContentLoaded', function() {
            const filterItems = document.querySelectorAll('.course-filter');

            filterItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    filterItems.forEach(i => i.classList.remove('active'));

                    this.classList.add('active');
                });
            });
        });

        $(document).on('click', '.course-filter', function(e) {
            e.preventDefault();

            let data_type = $(this).data('filter-course');
            console.log(data_type);

            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                method: 'GET',
                data: {
                    orderby_course: data_type
                },
                dataType: 'json',
                success: function(data) {
                    console.log(data);

                    $('#top-course-view').empty();
                    $('#top-course-view').html(data.getTopViewCourses);
                }
            })
        })

        var swiper = new Swiper('.marketplace-swiper', {
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

        const observer = new MutationObserver(() => {
            swiper.update();
        });

        observer.observe(document.querySelector('.marketplace-swiper .swiper-wrapper'), {
            childList: true,
            subtree: true
        });

        updatePieChart(ratingData);
        renderTopCompletedCourses(@json($topCoursesProgress));
    </script>
@endpush
