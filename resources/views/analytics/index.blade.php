@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Thống kê truy cập</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item active dateRangePicker"></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xxl-7">
                <div class="d-flex flex-column">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-muted mb-0">Tổng người dùng duyệt web</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value"
                                                    data-target="28.05">{{ number_format($analyticsUserSession[0]['totalUsers'] ?? 0) }}
                                                    người</span>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                                    <i class="bx bx-user text-info"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="fw-medium text-muted mb-0">Số phiên duyệt web</p>
                                            <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value"
                                                    data-target="97.66">{{ number_format($analyticsUserSession[0]['sessions'] ?? 0) }}
                                                    phiên</span>
                                            </h2>
                                        </div>
                                        <div>
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                                    <i class="bx bx-pulse text-info"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    </div> <!-- end row-->
                    <div>
                        <div class="card">
                            <div class="card-body">
                                <div id="line_chart_basic" data-colors='["--vz-primary","--vz-success","--vz-danger"]'
                                    class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col-->

            <div class="col-xxl-5">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Số phiên duyệt web theo đất nước</h4>
                            </div>
                            <div class="card-body p-0">
                                <div>
                                    <div id="countries_charts"
                                        data-colors='["--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-danger", "--vz-info", "--vz-info", "--vz-info", "--vz-info", "--vz-info"]'
                                        class="apex-charts" dir="ltr"></div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div> <!-- end col-->

                </div> <!-- end row-->
            </div><!-- end col -->
        </div> <!-- end row-->

        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Audiences Metrics</h4>
                        <div>
                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                ALL
                            </button>
                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                1M
                            </button>
                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                6M
                            </button>
                            <button type="button" class="btn btn-soft-primary btn-sm">
                                1Y
                            </button>
                        </div>
                    </div><!-- end card header -->
                    <div class="card-header p-0 border-0 bg-light-subtle">
                        <div class="row g-0 text-center">
                            <div class="col-6 col-sm-4">
                                <div class="p-3 border border-dashed border-start-0">
                                    <h5 class="mb-1"><span class="counter-value" data-target="854">0</span>
                                        <span class="text-success ms-1 fs-12">49%<i
                                                class="ri-arrow-right-up-line ms-1 align-middle"></i></span>
                                    </h5>
                                    <p class="text-muted mb-0">Avg. Session</p>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-6 col-sm-4">
                                <div class="p-3 border border-dashed border-start-0">
                                    <h5 class="mb-1"><span class="counter-value" data-target="1278">0</span>
                                        <span class="text-success ms-1 fs-12">60%<i
                                                class="ri-arrow-right-up-line ms-1 align-middle"></i></span>
                                    </h5>
                                    <p class="text-muted mb-0">Conversion Rate</p>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-6 col-sm-4">
                                <div class="p-3 border border-dashed border-start-0 border-end-0">
                                    <h5 class="mb-1"><span class="counter-value" data-target="3">0</span>m
                                        <span class="counter-value" data-target="40">0</span>sec
                                        <span class="text-success ms-1 fs-12">37%<i
                                                class="ri-arrow-right-up-line ms-1 align-middle"></i></span>
                                    </h5>
                                    <p class="text-muted mb-0">Avg. Session Duration</p>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body p-0 pb-2">
                        <div>
                            <div id="audiences_metrics_charts" data-colors='["--vz-success", "--vz-light"]'
                                class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-6">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Audiences Sessions by Country</h4>
                        <div class="flex-shrink-0">
                            <div class="dropdown card-header-dropdown">
                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="fw-semibold text-uppercase fs-12">Sort by: </span><span
                                        class="text-muted">Current Week<i class="mdi mdi-chevron-down ms-1"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Today</a>
                                    <a class="dropdown-item" href="#">Last Week</a>
                                    <a class="dropdown-item" href="#">Last Month</a>
                                    <a class="dropdown-item" href="#">Current Year</a>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body p-0">
                        <div>
                            <div id="audiences-sessions-country-charts" data-colors='["--vz-success", "--vz-info"]'
                                class="apex-charts" dir="ltr">
                            </div>
                        </div>
                    </div><!-- end cardbody -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->

        <div class="row">
            <div class="col-xl-4">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Users by Device</h4>
                        <div class="flex-shrink-0">
                            <div class="dropdown card-header-dropdown">
                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="text-muted fs-16"><i
                                            class="mdi mdi-dots-vertical align-middle"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Today</a>
                                    <a class="dropdown-item" href="#">Last Week</a>
                                    <a class="dropdown-item" href="#">Last Month</a>
                                    <a class="dropdown-item" href="#">Current Year</a>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body">
                        <div id="user_device_pie_charts" data-colors='["--vz-primary", "--vz-warning", "--vz-info"]'
                            class="apex-charts" dir="ltr"></div>

                        <div class="table-responsive mt-3">
                            <table class="table table-borderless table-sm table-centered align-middle table-nowrap mb-0">
                                <tbody class="border-0">
                                    <tr>
                                        <td>
                                            <h4 class="text-truncate fs-14 fs-medium mb-0"><i
                                                    class="ri-stop-fill align-middle fs-18 text-primary me-2"></i>Desktop
                                                Users</h4>
                                        </td>
                                        <td>
                                            <p class="text-muted mb-0"><i data-feather="users"
                                                    class="me-2 icon-sm"></i>78.56k</p>
                                        </td>
                                        <td class="text-end">
                                            <p class="text-success fw-medium fs-12 mb-0"><i
                                                    class="ri-arrow-up-s-fill fs-5 align-middle"></i>2.08%
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h4 class="text-truncate fs-14 fs-medium mb-0"><i
                                                    class="ri-stop-fill align-middle fs-18 text-warning me-2"></i>Mobile
                                                Users</h4>
                                        </td>
                                        <td>
                                            <p class="text-muted mb-0"><i data-feather="users"
                                                    class="me-2 icon-sm"></i>105.02k</p>
                                        </td>
                                        <td class="text-end">
                                            <p class="text-danger fw-medium fs-12 mb-0"><i
                                                    class="ri-arrow-down-s-fill fs-5 align-middle"></i>10.52%
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h4 class="text-truncate fs-14 fs-medium mb-0"><i
                                                    class="ri-stop-fill align-middle fs-18 text-info me-2"></i>Tablet
                                                Users</h4>
                                        </td>
                                        <td>
                                            <p class="text-muted mb-0"><i data-feather="users"
                                                    class="me-2 icon-sm"></i>42.89k</p>
                                        </td>
                                        <td class="text-end">
                                            <p class="text-danger fw-medium fs-12 mb-0"><i
                                                    class="ri-arrow-down-s-fill fs-5 align-middle"></i>7.36%
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-4 col-md-6">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Top Referrals Pages</h4>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-soft-primary btn-sm">
                                Export Report
                            </button>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="row align-items-center">
                            <div class="col-6">
                                <h6 class="text-muted text-uppercase fw-semibold text-truncate fs-12 mb-3">
                                    Total Referrals Page</h6>
                                <h4 class="mb-0">725,800</h4>
                                <p class="mb-0 mt-2 text-muted"><span class="badge bg-success-subtle text-success mb-0">
                                        <i class="ri-arrow-up-line align-middle"></i> 15.72 % </span> vs.
                                    previous month</p>
                            </div><!-- end col -->
                            <div class="col-6">
                                <div class="text-center">
                                    <img src="../assets/images/illustrator-1.png" class="img-fluid" alt="">
                                </div>
                            </div><!-- end col -->
                        </div><!-- end row -->
                        <div class="mt-3 pt-2">
                            <div class="progress progress-lg rounded-pill">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"
                                    aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: 18%"
                                    aria-valuenow="18" aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-bar bg-success" role="progressbar" style="width: 22%"
                                    aria-valuenow="22" aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 16%"
                                    aria-valuenow="16" aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 19%"
                                    aria-valuenow="19" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div><!-- end -->

                        <div class="mt-3 pt-2">
                            <div class="d-flex mb-2">
                                <div class="flex-grow-1">
                                    <p class="text-truncate text-muted fs-14 mb-0"><i
                                            class="mdi mdi-circle align-middle text-primary me-2"></i>www.google.com
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="mb-0">24.58%</p>
                                </div>
                            </div><!-- end -->
                            <div class="d-flex mb-2">
                                <div class="flex-grow-1">
                                    <p class="text-truncate text-muted fs-14 mb-0"><i
                                            class="mdi mdi-circle align-middle text-info me-2"></i>www.youtube.com
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="mb-0">17.51%</p>
                                </div>
                            </div><!-- end -->
                            <div class="d-flex mb-2">
                                <div class="flex-grow-1">
                                    <p class="text-truncate text-muted fs-14 mb-0"><i
                                            class="mdi mdi-circle align-middle text-success me-2"></i>www.meta.com
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="mb-0">23.05%</p>
                                </div>
                            </div><!-- end -->
                            <div class="d-flex mb-2">
                                <div class="flex-grow-1">
                                    <p class="text-truncate text-muted fs-14 mb-0"><i
                                            class="mdi mdi-circle align-middle text-warning me-2"></i>www.medium.com
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="mb-0">12.22%</p>
                                </div>
                            </div><!-- end -->
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-truncate text-muted fs-14 mb-0"><i
                                            class="mdi mdi-circle align-middle text-danger me-2"></i>Other
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <p class="mb-0">17.58%</p>
                                </div>
                            </div><!-- end -->
                        </div><!-- end -->

                        <div class="mt-2 text-center">
                            <a href="javascript:void(0);" class="text-muted text-decoration-underline">Show
                                All</a>
                        </div>

                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-4 col-md-6">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Top Pages</h4>
                        <div class="flex-shrink-0">
                            <div class="dropdown card-header-dropdown">
                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="text-muted fs-16"><i
                                            class="mdi mdi-dots-vertical align-middle"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Today</a>
                                    <a class="dropdown-item" href="#">Last Week</a>
                                    <a class="dropdown-item" href="#">Last Month</a>
                                    <a class="dropdown-item" href="#">Current Year</a>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body">
                        <div class="table-responsive table-card">
                            <table class="table align-middle table-borderless table-centered table-nowrap mb-0">
                                <thead class="text-muted table-light">
                                    <tr>
                                        <th scope="col" style="width: 62;">Active Page</th>
                                        <th scope="col">Active</th>
                                        <th scope="col">Users</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0);">/themesbrand/skote-25867</a>
                                        </td>
                                        <td>99</td>
                                        <td>25.3%</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0);">/dashonic/chat-24518</a>
                                        </td>
                                        <td>86</td>
                                        <td>22.7%</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0);">/skote/timeline-27391</a>
                                        </td>
                                        <td>64</td>
                                        <td>18.7%</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0);">/themesbrand/minia-26441</a>
                                        </td>
                                        <td>53</td>
                                        <td>14.2%</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0);">/dashon/dashboard-29873</a>
                                        </td>
                                        <td>33</td>
                                        <td>12.6%</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0);">/doot/chats-29964</a>
                                        </td>
                                        <td>20</td>
                                        <td>10.9%</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0);">/minton/pages-29739</a>
                                        </td>
                                        <td>10</td>
                                        <td>07.3%</td>
                                    </tr><!-- end -->
                                </tbody><!-- end tbody -->
                            </table><!-- end table -->
                        </div><!-- end -->
                    </div><!-- end cardbody -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->

    </div>
@endsection

@push('page-scripts')
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard-analytics.init.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}" />
    <script src="{{ asset('assets/js/pages/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/daterangepicker.min.js') }}"></script>

    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- Marketplace init -->
    <script src="{{ asset('assets/js/pages/dashboard-nft.init.js') }}"></script>

    <script>
        $(".dateRangePicker").each(function() {
            let button = $(this);

            function updateDateRangeText(start, end) {
                button.html("📅 " + start.format("DD/MM/YYYY") + " - " + end.format("DD/MM/YYYY"));

                button.attr("data-start", start.format("YYYY-MM-DD"));
                button.attr("data-end", end.format("YYYY-MM-DD"));
            }

            let defaultStart = moment().startOf("month");
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
                }
            );

            updateDateRangeText(defaultStart, defaultEnd);
        });

        function getFirstDayOfWeek(year, weekNumber) {
            let firstDayOfYear = new Date(year, 0, 1);
            let dayOffset = firstDayOfYear.getDay() === 0 ? 1 : (8 - firstDayOfYear.getDay());
            let firstWeekDate = new Date(year, 0, 1 + (weekNumber - 1) * 7 + dayOffset);
            return firstWeekDate.toISOString().split("T")[0]; // YYYY-MM-DD
        }

        function extractSeries(data, groupBy, metric) {
            let seriesData = {};

            data.forEach(item => {
                let formattedDate = "";

                if (groupBy === "yearWeek" && item.yearWeek) {
                    const year = parseInt(item.yearWeek.substring(0, 4), 10);
                    const weekNumber = parseInt(item.yearWeek.substring(4), 10);
                    formattedDate = getFirstDayOfWeek(year, weekNumber);
                } else if (groupBy === "yearMonth" && item.yearMonth) {
                    const year = parseInt(item.yearMonth.substring(0, 4), 10);
                    const month = parseInt(item.yearMonth.substring(4), 10);
                    formattedDate = `${year}-${String(month).padStart(2, "0")}-01`;
                } else if (groupBy === "date" && item.date) {
                    formattedDate = new Date(item.date).toISOString().split("T")[0];
                }

                if (formattedDate) {
                    seriesData[formattedDate] = (seriesData[formattedDate] || 0) + (parseInt(item[metric]) || 0);
                }
            });

            return Object.keys(seriesData).map(date => ({
                date: date,
                value: seriesData[date]
            }));
        }

        let charCountries;

        function updateCountriesChart(countriesData = []) {
            let chartContainer = document.querySelector("#countries_charts");

            if (typeof charCountries !== "undefined" && charCountries) {
                charCountries.destroy();
                charCountries = undefined;
            }

            chartContainer.innerHTML = "";

            if (!countriesData || countriesData.length === 0) {
                chartContainer.innerHTML = `
<div style="text-align: center; padding: 20px; color: #999;">
    <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
</div>`;
                return;
            }
            let countries = [];
            let viewPage = [];
            countriesData.forEach(item => {             
                countries.push(item.country);
                viewPage.push(parseFloat(item.screenPageViews));
            });

            let options = {
                series: [{
                    name: 'Lượt',
                    data: viewPage
                }],
                xaxis: {
                    categories: countries
                },
                chart: {
                    type: "bar",
                    height: 436,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                        distributed: true
                    }
                },
                colors: [
                    "#007bff", "#28a745", "#dc3545", "#ffc107", "#17a2b8", "#6610f2", "#e83e8c",
                    "#fd7e14", "#6c757d", "#343a40", "#ff00ff", "#00ff00", "#00ffff", "#ff5733",
                    "#800000", "#808000", "#008080", "#000080", "#4B0082", "#FFD700"
                ],
                dataLabels: {
                    enabled: true,
                    offsetX: 32,
                    style: {
                        fontSize: "12px",
                        fontWeight: 400,
                        colors: ["#adb5bd"]
                    }
                }
            };

            charCountries = new ApexCharts(document.querySelector("#countries_charts"), options);
            charCountries.render();
        }

        function line_chart_basic(line_chart_basic = []) {
            let type = 'date';
            if (line_chart_basic[0]['yearWeek']) {
                type = 'yearWeek';
            } else if (line_chart_basic[0]['yearMonth']) {
                type = 'yearMonth';
            }

            let categories = extractSeries(line_chart_basic, type, "date").map(item => item.date);

            let series = [{
                    name: "Người dùng mới",
                    data: extractSeries(line_chart_basic, type, "newUsers").map(item => item.value)
                },
                {
                    name: "Tổng người dùng",
                    data: extractSeries(line_chart_basic, type, "totalUsers").map(item => item.value)
                },
                {
                    name: "Số phiên duyệt web",
                    data: extractSeries(line_chart_basic, type, "sessions").map(item => item.value)
                }
            ];

            chart.updateOptions({
                series: series,
                xaxis: {
                    categories: categories
                },
                chart: {
                    height: 300
                }
            });
        }

        line_chart_basic(@json($analyticsData));
        updateCountriesChart(@json($analyticsCountriesSession));
    </script>
@endpush
