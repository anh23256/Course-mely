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

                    <!-- Top Instructors & Courses -->
                    <div class="row g-4">
                        <div class="col-xl-12">
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
                                                        <td>{{ $topInstructor->total_courses ?? '' }}
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
                    </div>

                    <!-- Top Completed Courses & Top Instructors -->
                    <div class="row mt-2 ">
                        <div class="col-xxl-6">
                            <div class="card">
                                <div
                                    class="card-header d-flex justify-content-between bg-primary bg-gradient bg-opacity-60">
                                    <h4 class="card-title mb-0 mx-2 text-white">Top giảng viên có nhiều học viên nhất
                                    </h4>
                                    <div class="dateRangePicker btn btn-outline-warning rounded-pill"
                                        data-filter="topStudentCourseMely"
                                        style="padding: 5px 9px; font-size: 10px; height: auto; min-width: auto; width: fit-content; display: inline-block;">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="topInstructorStudentChart" class="w-100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6">
                            <div class="card">
                                <div
                                    class="card-header d-flex justify-content-between bg-primary bg-gradient bg-opacity-60">
                                    <h4 class="card-title mb-0 text-white">Top 10 giảng viên được yêu thích nhất</h4>
                                    <div class="dateRangePicker btn btn-outline-warning rounded-pill"
                                        data-filter="topInstructorFollow"
                                        style="padding: 5px 9px; font-size: 10px; height: auto; min-width: auto; width: fit-content; display: inline-block;">
                                    </div>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <div id="topInstructorsChart" class="w-100"></div>
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
        var topInstructor = @json($topInstructors);
        var chartTopInstructors;
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
            $(document).on('click', '#pagination-links-instructors a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                const keySelected = ".dateRangePicker[data-filter='topInstructorCourseMely']";

                let dataFilter = getSelectedDateRange(keySelected);

                dataFilter.page = page;

                loadInstructorsContent(dataFilter);
            });

            function loadInstructorsContent(dataFilter) {
                dataFilter.type = 'instructors';
                $.ajax({
                    url: "{{ route('admin.top-instructors.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        topInstructor = data.topInstructor;
                        console.log(data);

                        $('#table-instructors tbody').html(data.top_instructors_table);
                        $('#pagination-links-instructors').html(data.pagination_links_instructors);
                        renderTopInstructorsChart(topInstructor);
                    }
                });
            }

            function loadInstructorsFollow(dataFilter) {
                dataFilter.type = 'instructors';
                $.ajax({
                    url: "{{ route('admin.top-instructors.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        renderTopInstructorsFollow(data.topInstructorsFollows);
                    }
                });
            }

            function loadInstructorsStudent(dataFilter) {
                dataFilter.type = 'instructors';
                $.ajax({
                    url: "{{ route('admin.top-instructors.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        renderTopInstructorsStudent(data.topInstructorsStudents);
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

                        if (filter == "topInstructorFollow") {
                            loadInstructorsFollow(data);
                        } else if (filter == "topInstructorCourseMely") {
                            loadInstructorsContent(data);
                        } else if (filter == "topStudentCourseMely") {
                            loadInstructorsStudent(data);
                        }
                    }
                );

                updateDateRangeText(defaultStart, defaultEnd);
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
        });

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
                    height: "50%"
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

        function renderTopInstructorsFollow(data = []) {
            let chartContainer = document.querySelector("#topInstructorsChart");
            chartContainer.innerHTML = "";

            if (!data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.map(item => item.name);
            let totalFollows = data.map(item => item.total_follow);

            Highcharts.chart(chartContainer, {
                chart: {
                    polar: true,
                    type: 'line',
                    height: 330
                },
                title: {
                    text: null
                },
                credits: {
                    enabled: false
                },
                pane: {
                    size: '80%'
                },
                xAxis: {
                    categories: categories,
                    tickmarkPlacement: 'on',
                    lineWidth: 0
                },
                yAxis: {
                    gridLineInterpolation: 'polygon',
                    min: 0,
                    title: {
                        text: 'Số lượng'
                    }
                },
                tooltip: {
                    shared: true,
                    pointFormat: `<b>{series.name}</b>: {point.y}`
                },
                series: [{
                    name: 'Lượt Follow',
                    data: totalFollows,
                    color: '#00E396',
                    pointPlacement: 'on'
                }]
            });
        }

        function renderTopInstructorsStudent(data = []) {
            let chartContainer = document.querySelector("#topInstructorStudentChart");
            chartContainer.innerHTML = "";

            if (!data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.map(item => item.name);
            let totalFollows = data.map(item => item.total_student);

            let colors = [
                { stops: [[0, '#008FFB'], [1, '#00E396']] },
                { stops: [[0, '#FEB019'], [1, '#FF4560']] },
                { stops: [[0, '#775DD0'], [1, '#D7263D']] },
                { stops: [[0, '#546E7A'], [1, '#26A69A']] },
                { stops: [[0, '#D4526E'], [1, '#F86624']] },
                { stops: [[0, '#1B998B'], [1, '#E84855']] },
                { stops: [[0, '#9C27B0'], [1, '#673AB7']] },
                { stops: [[0, '#FF6F61'], [1, '#FF9F1C']] },
                { stops: [[0, '#16A085'], [1, '#2ECC71']] },
                { stops: [[0, '#34495E'], [1, '#95A5A6']] }
            ]; 

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'column',
                    height: 400
                },
                title: {
                    text: 'Giảng viên có nhiều học viên nhất'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: categories,
                    title: {
                        text: 'Giảng viên'
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Số lượng học viên'
                    }
                },
                tooltip: {
                    shared: true,
                    pointFormat: `<b>{series.name}</b>: {point.y}`
                },
                series: [{
                    name: 'Học viên',
                    data: totalFollows.map((value, index) => ({
                        y: value,
                        color: {
                            linearGradient: {
                                x1: 0,
                                x2: 0,
                                y1: 0,
                                y2: 1
                            },
                            stops: colors[index % colors.length].stops
                        }
                    }))
                }]
            });
        }

        renderTopInstructorsFollow(@json($topInstructorsFollows));
        renderTopInstructorsStudent(@json($topInstructorsStudents));
        $(document).on('click', '.dowloadExcel', function() {
            let type_export = $(this).data('type');
            let data_export;

            if (type_export == 'top_instructor') {
                data_export = topInstructor.data;
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

        function getSelectedDateRange() {
            let button = $(".dateRangePicker");
            return {
                startDate: button.attr("data-start"),
                endDate: button.attr("data-end")
            };
        }
    </script>
@endpush
