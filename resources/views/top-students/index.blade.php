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
                    <!-- Top Students -->
                    <div class="row d-flex">
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

        var topStudent = @json($topUsers);
        let chartTopStudents;
        $(document).ready(function() {
            $(document).on('click', '#pagination-links-users a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                const keySelected = ".dateRangePicker[data-filter='topStudentCourseMely']";

                let dataFilter = getSelectedDateRange(keySelected);

                dataFilter.page = page;

                loadUsersContent(dataFilter);
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

            function loadUsersContent(dataFilter) {
                dataFilter.type = 'user';
                $.ajax({
                    url: "{{ route('admin.top-students.index') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        topStudent = data.topUsers;
                        $('#table-students tbody').html(data.top_users_table);
                        $('#pagination-links-users').html(data.pagination_links_users);
                        renderTopStudentsChart(topUsers);
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

                        if (filter == "topStudentCourseMely") {
                            loadUsersContent(data);
                        }
                    }
                );

                updateDateRangeText(defaultStart, defaultEnd);
            });
        });

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

            if (type_export == 'top_student') {
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

        function getSelectedDateRange() {
            let button = $(".dateRangePicker");
            return {
                startDate: button.attr("data-start"),
                endDate: button.attr("data-end")
            };
        }
    </script>
@endpush
