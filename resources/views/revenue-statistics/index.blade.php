@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
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
                    <div class="row mb-3 pb-1">
                        <div class="col-12">
                            <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                <div class="flex-grow-1">
                                    <h4 class="fs-16 mb-1" id="greeting">Xin chào, {{ Auth::user()->name ?? '' }}!</h4>
                                    <p class="text-muted mb-0">
                                        Chúc bạn một ngày tốt lành!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Tổng doanh thu
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i> +16.24 %
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4 ">
                                        <div class="d-flex gap-2 align-items-center ">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                                    <i class="bx bx-dollar-circle text-success"></i>
                                                </span>
                                            </div>
                                            <h4 class="fs-22 fw-semibold ff-secondary">
                                                <span class="counter-value" data-target="559.25">
                                                    {{ number_format($totalAmount->totalRevenue ?? 0) }}
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div><!-- end col -->
                        <div class="col-xl-3 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Lợi nhuận đạt được
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-danger fs-14 mb-0">
                                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i> -3.57 %
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div class="d-flex gap-2 justify-content-between align-content-center">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                                    <i class="bx bx-dollar-circle text-info"></i>
                                                </span>
                                            </div>
                                            <h4 class="fs-22 fw-semibold ff-secondary">
                                                <span class="counter-value" data-target="36894">
                                                    {{ number_format($totalAmount->totalProfit ?? 0) }}
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div><!-- end col -->
                        <div class="col-xl-3 col-md-6">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Tổng khoá học
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i> +29.08 %
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div class="d-flex gap-2 align-items-center justify-content-between">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-warning-subtle rounded fs-3">
                                                    <i class="las la-book-reader text-warning"></i>
                                                </span>
                                            </div>
                                            <h4 class="fs-22 fw-semibold ff-secondary">
                                                <span class="counter-value" data-target="183.35">
                                                    {{ $totalCourse ?? '' }}
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div><!-- end col -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Người hướng dẫn
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-muted fs-14 mb-0">
                                                +0.00 %
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div class="d-flex gap-2 align-items-center justify-content-between">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                                    <i class=" ri-account-circle-line text-primary"></i>
                                                </span>
                                            </div>
                                            <h4 class="fs-22 fw-semibold ff-secondary ">
                                                <span class="counter-value" data-target="165.89">
                                                    {{ $totalInstructor ?? '' }}
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Doanh thu 2025 CourseMeLy</h4>
                                    <div class="dateRangePicker" data-filter="totalRevenueCourseMely">
                                    </div>
                                </div>
                                <!-- end card header -->


                                <div class="card-header p-0 border-0 bg-light-subtle">
                                    <div class="row g-0 text-center">
                                        <div class="col-6 col-sm-6">
                                            <div class="p-3 border border-dashed border-start-0">
                                                <h5 class="mb-1"><span class="counter-value-revenue" data-target="228.89">
                                                        {{ number_format($sumRevenueProfit->total_revenue ?? 0) }}</span>
                                                    VND</h5>
                                                <p class="text-muted mb-0">Doanh thu</p>
                                            </div>
                                        </div>
                                        <!--end col-->
                                        <div class="col-6 col-sm-6">
                                            <div class="p-3 border border-dashed border-start-0 border-end-0">
                                                <h5 class="mb-1 text-success"><span class="counter-value-profit"
                                                        data-target="10589">{{ number_format($sumRevenueProfit->total_profit ?? 0) }}</span>
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

                    <div class="row">
                        <div class="col-xl-5">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Người hướng dẫn nổi bật</h4>
                                    <div class="dateRangePicker" data-filter="topInstructorCourseMely">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive table-card">
                                        <table id="table-instructors"
                                            class="table table-centered table-hover align-middle table-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Người hướng dẫn</th>
                                                    <th scope="col">Khoá học</th>
                                                    <th scope="col">Học viên</th>
                                                    <th scope="col">Doanh thu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($topInstructors as $topInstructor)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0 me-2">
                                                                    <img src="{{ $topInstructor->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}"
                                                                        alt=""
                                                                        class="avatar-sm p-2 rounded-circle object-fit-cover" />
                                                                </div>
                                                                <div>
                                                                    <h5 class="fs-14 my-1 fw-medium">
                                                                        <a href="" class="text-reset">
                                                                            {{ $topInstructor->name ?? '' }}
                                                                        </a>
                                                                    </h5>
                                                                    <span class="text-muted">
                                                                        Tham gia
                                                                        {{ \Carbon\Carbon::parse($topInstructor->created_at)->format('d/m/Y') ?? '' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <p class="mb-0">{{ $topInstructor->total_courses ?? '' }}
                                                            </p>
                                                            <span class="text-muted">Đã bán</span>
                                                        </td>
                                                        <td>
                                                            <h5 class="fs-14 mb-0">
                                                                {{ $topInstructor->total_enrolled_students ?? '' }}
                                                            </h5>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted">
                                                                {{ number_format($topInstructor->total_revenue) ?? '' }}
                                                            </span>
                                                        </td>
                                                    </tr><!-- end -->
                                                @endforeach
                                            </tbody>
                                        </table><!-- end table -->
                                    </div>
                                    <div
                                        class="align-items-center mt-4 pt-2 justify-content-between row text-center text-sm-start">
                                        <div id="pagination-links-instructors">
                                            {{ $topInstructors->appends(request()->query())->links() }}
                                        </div>

                                    </div>
                                </div>
                            </div> <!-- .card-->
                        </div> <!-- .col-->

                        <div class="col-xl-7">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top khoá học bán chạy </h4>
                                    <div class="flex-shrink-0">
                                        <div class="dropdown card-header-dropdown">
                                            <a class="text-reset" href="#" data-bs-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <div class="dateRangePicker" data-filter="topCourseBoughtCourseMely">
                                                </div>
                                            </a>

                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive table-card">
                                        <table id="table-courses"
                                            class="table table-hover table-centered align-middle table-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Khoá học</th>
                                                    <th scope="col">Đã bán</th>
                                                    <th scope="col">Người học</th>
                                                    <th scope="col">Doanh thu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($topCourses as $topCourse)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2 ju">
                                                                <img style="width:70px" src="{{ $topCourse->thumbnail }}"
                                                                    alt="" class="img-fluid d-block " />
                                                                <div>
                                                                    <h5 class="fs-14 my-1"><a href="#"
                                                                            class="text-reset">{{ \Illuminate\Support\Str::limit($topCourse->name, 20) }}</a>
                                                                    </h5>
                                                                    <span class="text-muted">
                                                                        {{ \Carbon\Carbon::parse($topCourse->created_at)->format('d/m/Y') }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <h5 class="fs-14 my-1 fw-normal">{{ $topCourse->total_sales }}
                                                            </h5>
                                                        </td>
                                                        <td class="text-center">
                                                            <h5 class="fs-14 my-1 fw-normal">
                                                                {{ $topCourse->total_enrolled_students }}</h5>
                                                        </td>
                                                        <td>
                                                            <h5 class="fs-14 my-1 fw-normal">
                                                                {{ number_format($topCourse->total_revenue) }}</h5>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div
                                        class="align-items-center mt-4 pt-2 justify-content-between row text-center text-sm-start">
                                        <div id="pagination-links-courses">
                                            {{ $topCourses->appends(request()->query())->links() }}
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- end row-->

                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card card-height-100">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        Đánh giá khoá học
                                    </h4>
                                    <div class="dateRangePicker" data-filter="topRatingCourseMely">
                                    </div>
                                </div><!-- end card header -->

                                <div class="card-body">
                                    <div id="rating-pie-chart" dir="ltr"></div>
                                </div>
                            </div> <!-- .card-->
                        </div> <!-- .col-->

                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Top học viên </h4>
                                    <div class="dateRangePicker" data-filter="topStudentCourseMely">
                                    </div>
                                </div><!-- end card header -->

                                <div class="card-body">
                                    <div class="table-responsive table-card">
                                        <table id="table-users"
                                            class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                            <thead class="text-muted table-light">
                                                <tr>
                                                    <th scope="col">STT</th>
                                                    <th scope="col">Học viên</th>
                                                    <th scope="col">Khoá học đã mua</th>
                                                    <th scope="col">Tổng tiền đã chi</th>
                                                    <th>Lần mua gần nhất</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($topUsers as $topUser)
                                                    <tr>
                                                        <td>
                                                            <a href="#" class="fw-medium link-primary">
                                                                {{ $loop->iteration }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0 me-2">
                                                                    <img src="{{ $topUser->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}"
                                                                        alt=""
                                                                        class="avatar-xs rounded-circle object-fit-cover" />
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    {{ $topUser->name ?? '' }}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $topUser->total_courses_purchased }}</td>
                                                        <td>{{ number_format($topUser->total_spent ?? 0) }}</td>
                                                        <td>{{ $topUser->last_purchase_date }}</td>
                                                    </tr>
                                                @endforeach
                                        </table>
                                    </div>
                                    <div
                                        class="align-items-center mt-4 pt-2 justify-content-between row text-center text-sm-start">
                                        <div id="pagination-links-users">
                                            {{ $topUsers->appends(request()->query())->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- .card-->
                        </div> <!-- .col-->
                    </div> <!-- end row-->

                </div> <!-- end .h-100-->

            </div> <!-- end col -->

            <div class="col-auto layout-rightside-col">
                <div class="overlay"></div>
                <div class="layout-rightside">
                    <div class="card h-100 rounded-0">
                        <div class="card-body p-0">
                            <div class="p-3">
                                <h6 class="text-muted mb-0 text-uppercase fw-semibold">Recent Activity</h6>
                            </div>
                            <div data-simplebar style="max-height: 410px;" class="p-3 pt-0">
                                <div class="acitivity-timeline acitivity-main">
                                    <div class="acitivity-item d-flex">
                                        <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                            <div class="avatar-title bg-success-subtle text-success rounded-circle">
                                                <i class="ri-shopping-cart-2-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Purchase by James Price</h6>
                                            <p class="text-muted mb-1">Product noise evolve smartwatch </p>
                                            <small class="mb-0 text-muted">02:14 PM Today</small>
                                        </div>
                                    </div>
                                    <div class="acitivity-item py-3 d-flex">
                                        <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                            <div class="avatar-title bg-danger-subtle text-danger rounded-circle">
                                                <i class="ri-stack-fill"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Added new <span class="fw-semibold">style
                                                    collection</span>
                                            </h6>
                                            <p class="text-muted mb-1">By Nesta Technologies</p>
                                            <div class="d-inline-flex gap-2 border border-dashed p-2 mb-2">
                                                <a href="apps-ecommerce-product-details.html"
                                                    class="bg-light rounded p-1">
                                                    <img src="../assets/images/products/img-8.png" alt=""
                                                        class="img-fluid d-block" />
                                                </a>
                                                <a href="apps-ecommerce-product-details.html"
                                                    class="bg-light rounded p-1">
                                                    <img src="../assets/images/products/img-2.png" alt=""
                                                        class="img-fluid d-block" />
                                                </a>
                                                <a href="apps-ecommerce-product-details.html"
                                                    class="bg-light rounded p-1">
                                                    <img src="../assets/images/products/img-10.png" alt=""
                                                        class="img-fluid d-block" />
                                                </a>
                                            </div>
                                            <p class="mb-0 text-muted"><small>9:47 PM Yesterday</small></p>
                                        </div>
                                    </div>
                                    <div class="acitivity-item py-3 d-flex">
                                        <div class="flex-shrink-0">
                                            <img src="../assets/images/users/avatar-2.jpg" alt=""
                                                class="avatar-xs rounded-circle acitivity-avatar">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Natasha Carey have liked the products</h6>
                                            <p class="text-muted mb-1">Allow users to like products in your WooCommerce
                                                store.</p>
                                            <small class="mb-0 text-muted">25 Dec, 2021</small>
                                        </div>
                                    </div>
                                    <div class="acitivity-item py-3 d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs acitivity-avatar">
                                                <div class="avatar-title rounded-circle bg-secondary">
                                                    <i class="mdi mdi-sale fs-14"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Today offers by <a
                                                    href="apps-ecommerce-seller-details.html"
                                                    class="link-secondary">Digitech
                                                    Galaxy</a></h6>
                                            <p class="text-muted mb-2">Offer is valid on orders of Rs.500 Or above for
                                                selected products only.</p>
                                            <small class="mb-0 text-muted">12 Dec, 2021</small>
                                        </div>
                                    </div>
                                    <div class="acitivity-item py-3 d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs acitivity-avatar">
                                                <div class="avatar-title rounded-circle bg-danger-subtle text-danger">
                                                    <i class="ri-bookmark-fill"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Favorite Product</h6>
                                            <p class="text-muted mb-2">Esther James have Favorite product.</p>
                                            <small class="mb-0 text-muted">25 Nov, 2021</small>
                                        </div>
                                    </div>
                                    <div class="acitivity-item py-3 d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs acitivity-avatar">
                                                <div class="avatar-title rounded-circle bg-secondary">
                                                    <i class="mdi mdi-sale fs-14"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Flash sale starting <span
                                                    class="text-primary">Tomorrow.</span>
                                            </h6>
                                            <p class="text-muted mb-0">Flash sale by <a href="javascript:void(0);"
                                                    class="link-secondary fw-medium">Zoetic
                                                    Fashion</a></p>
                                            <small class="mb-0 text-muted">22 Oct, 2021</small>
                                        </div>
                                    </div>
                                    <div class="acitivity-item py-3 d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs acitivity-avatar">
                                                <div class="avatar-title rounded-circle bg-info-subtle text-info">
                                                    <i class="ri-line-chart-line"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Monthly sales report</h6>
                                            <p class="text-muted mb-2"><span class="text-danger">2 days left</span>
                                                notification to submit the monthly sales report. <a
                                                    href="javascript:void(0);"
                                                    class="link-warning text-decoration-underline">Reports
                                                    Builder</a>
                                            </p>
                                            <small class="mb-0 text-muted">15 Oct</small>
                                        </div>
                                    </div>
                                    <div class="acitivity-item d-flex">
                                        <div class="flex-shrink-0">
                                            <img src="../assets/images/users/avatar-3.jpg" alt=""
                                                class="avatar-xs rounded-circle acitivity-avatar" />
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 lh-base">Frank Hook Commented</h6>
                                            <p class="text-muted mb-2 fst-italic">" A product that has reviews is more
                                                likable to be sold than a product. "</p>
                                            <small class="mb-0 text-muted">26 Aug, 2021</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 mt-2">
                                <h6 class="text-muted mb-3 text-uppercase fw-semibold">Top 10 Categories
                                </h6>

                                <ol class="ps-3 text-muted">
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Mobile & Accessories <span
                                                class="float-end">(10,294)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Desktop <span
                                                class="float-end">(6,256)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Electronics <span
                                                class="float-end">(3,479)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Home & Furniture <span
                                                class="float-end">(2,275)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Grocery <span
                                                class="float-end">(1,950)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Fashion <span
                                                class="float-end">(1,582)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Appliances <span
                                                class="float-end">(1,037)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Beauty, Toys & More <span
                                                class="float-end">(924)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Food & Drinks <span
                                                class="float-end">(701)</span></a>
                                    </li>
                                    <li class="py-1">
                                        <a href="#" class="text-muted">Toys & Games <span
                                                class="float-end">(239)</span></a>
                                    </li>
                                </ol>
                                <div class="mt-3 text-center">
                                    <a href="javascript:void(0);" class="text-muted text-decoration-underline">View all
                                        Categories</a>
                                </div>
                            </div>
                            <div class="p-3">
                                <h6 class="text-muted mb-3 text-uppercase fw-semibold">Products Reviews</h6>
                                <!-- Swiper -->
                                <div class="swiper vertical-swiper" style="height: 250px;">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="card border border-dashed shadow-none">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 avatar-sm">
                                                            <div class="avatar-title bg-light rounded">
                                                                <img src="../assets/images/companies/img-1.png"
                                                                    alt="" height="30">
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <div>
                                                                <p
                                                                    class="text-muted mb-1 fst-italic text-truncate-two-lines">
                                                                    " Great product and looks great, lots of features.
                                                                    "</p>
                                                                <div class="fs-11 align-middle text-warning">
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                </div>
                                                            </div>
                                                            <div class="text-end mb-0 text-muted">
                                                                - by <cite title="Source Title">Force Medicines</cite>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card border border-dashed shadow-none">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0">
                                                            <img src="../assets/images/users/avatar-3.jpg" alt=""
                                                                class="avatar-sm rounded">
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <div>
                                                                <p
                                                                    class="text-muted mb-1 fst-italic text-truncate-two-lines">
                                                                    " Amazing template, very easy to understand and
                                                                    manipulate. "</p>
                                                                <div class="fs-11 align-middle text-warning">
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-half-fill"></i>
                                                                </div>
                                                            </div>
                                                            <div class="text-end mb-0 text-muted">
                                                                - by <cite title="Source Title">Henry Baird</cite>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card border border-dashed shadow-none">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 avatar-sm">
                                                            <div class="avatar-title bg-light rounded">
                                                                <img src="../assets/images/companies/img-8.png"
                                                                    alt="" height="30">
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <div>
                                                                <p
                                                                    class="text-muted mb-1 fst-italic text-truncate-two-lines">
                                                                    "Very beautiful product and Very helpful customer
                                                                    service."</p>
                                                                <div class="fs-11 align-middle text-warning">
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-line"></i>
                                                                    <i class="ri-star-line"></i>
                                                                </div>
                                                            </div>
                                                            <div class="text-end mb-0 text-muted">
                                                                - by <cite title="Source Title">Zoetic Fashion</cite>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="card border border-dashed shadow-none">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0">
                                                            <img src="../assets/images/users/avatar-2.jpg" alt=""
                                                                class="avatar-sm rounded">
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <div>
                                                                <p
                                                                    class="text-muted mb-1 fst-italic text-truncate-two-lines">
                                                                    " The product is very beautiful. I like it. "</p>
                                                                <div class="fs-11 align-middle text-warning">
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-fill"></i>
                                                                    <i class="ri-star-half-fill"></i>
                                                                    <i class="ri-star-line"></i>
                                                                </div>
                                                            </div>
                                                            <div class="text-end mb-0 text-muted">
                                                                - by <cite title="Source Title">Nancy Martino</cite>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3">
                                <h6 class="text-muted mb-3 text-uppercase fw-semibold">Customer Reviews</h6>
                                <div class="bg-light px-3 py-2 rounded-2 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fs-16 align-middle text-warning">
                                                <i class="ri-star-fill"></i>
                                                <i class="ri-star-fill"></i>
                                                <i class="ri-star-fill"></i>
                                                <i class="ri-star-fill"></i>
                                                <i class="ri-star-half-fill"></i>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h6 class="mb-0">4.5 out of 5</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-muted">Total <span class="fw-medium">5.50k</span> reviews</div>
                                </div>

                                <div class="mt-3">
                                    <div class="row align-items-center g-2">
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0">5 star</h6>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="p-1">
                                                <div class="progress animated-progress progress-sm">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: 50.16%" aria-valuenow="50.16" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0 text-muted">2758</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end row -->

                                    <div class="row align-items-center g-2">
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0">4 star</h6>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="p-1">
                                                <div class="progress animated-progress progress-sm">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: 29.32%" aria-valuenow="29.32" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0 text-muted">1063</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end row -->

                                    <div class="row align-items-center g-2">
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0">3 star</h6>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="p-1">
                                                <div class="progress animated-progress progress-sm">
                                                    <div class="progress-bar bg-warning" role="progressbar"
                                                        style="width: 18.12%" aria-valuenow="18.12" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0 text-muted">997</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end row -->

                                    <div class="row align-items-center g-2">
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0">2 star</h6>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="p-1">
                                                <div class="progress animated-progress progress-sm">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: 4.98%" aria-valuenow="4.98" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0 text-muted">227</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end row -->

                                    <div class="row align-items-center g-2">
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0">1 star</h6>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="p-1">
                                                <div class="progress animated-progress progress-sm">
                                                    <div class="progress-bar bg-danger" role="progressbar"
                                                        style="width: 7.42%" aria-valuenow="7.42" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="p-1">
                                                <h6 class="mb-0 text-muted">408</h6>
                                            </div>
                                        </div>
                                    </div><!-- end row -->
                                </div>
                            </div>

                            <div class="card sidebar-alert bg-light border-0 text-center mx-4 mb-0 mt-3">
                                <div class="card-body">
                                    <img src="../assets/images/giftbox.png" alt="">
                                    <div class="mt-4">
                                        <h5>Invite New Seller</h5>
                                        <p class="text-muted lh-base">Refer a new seller to us and earn $100 per
                                            refer.</p>
                                        <button type="button" class="btn btn-primary btn-label rounded-pill"><i
                                                class="ri-mail-fill label-icon align-middle rounded-pill fs-16 me-2"></i>
                                            Invite Now
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div> <!-- end card-->
                </div> <!-- end .rightbar-->

            </div> <!-- end col -->
        </div>

    </div>
@endsection
@push('page-scripts')
    <!-- Vector map-->
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
        $(document).ready(function() {
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
                        $('#table-courses tbody').html(data.top_courses_table);
                        $('#pagination-links-courses').html(data.pagination_links_courses);
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
                        $('#table-instructors tbody').html(data.top_instructors_table);
                        $('#pagination-links-instructors').html(data.pagination_links_instructors);
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
                        $('#table-users tbody').html(data.top_users_table);
                        $('#pagination-links-users').html(data.pagination_links_users);
                    }
                });
            }

            function loadApexCharts(filterData) {
                $.ajax({
                    url: "{{ route('admin.revenue-statistics.index') }}",
                    type: "GET",
                    data: filterData,
                    success: function(response) {
                        updateChart(response.apexCharts);

                        console.log(response);


                        if (response.sumRevenueProfit) {
                            let totalRevenue = Math.floor(Number(response.sumRevenueProfit
                                .total_revenue)) || 0;
                            let totalProfit = Math.floor(Number(response.sumRevenueProfit
                                .total_profit)) || 0;

                            let formattedRevenue = totalRevenue.toLocaleString("vi-VN").replace(/\./g,
                                ",");
                            let formattedProfit = totalProfit.toLocaleString("vi-VN").replace(/\./g,
                                ",");

                            $('.counter-value-revenue').text(formattedRevenue);
                            $('.counter-value-profit').text(formattedProfit);
                        }
                    }
                });
            }

            function loadCourseRatingCharts(filterData) {
                $.ajax({
                    url: "{{ route('admin.revenue-statistics.index') }}",
                    type: "GET",
                    data: filterData,
                    success: function(response) {
                        updatePieChart(response.course_rating);
                    }
                });
            }

            function getSelectedDateRange(keySelected) {
                let button = $(keySelected);

                return {
                    startDate: button.attr("data-start"),
                    endDate: button.attr("data-end"),
                    filter: button.data("filter")
                };
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
                        } else if (filter == "topRatingCourseMely") {
                            loadCourseRatingCharts(data);
                        } else if (filter == "topStudentCourseMely") {
                            loadUsersContent(data);
                        }
                    }
                );

                updateDateRangeText(defaultStart, defaultEnd);
            });

        });

        var chart;
        var pieChar;
        var newData = @json($system_Funds);
        var ratingData = @json($courseRatings);

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
                        show: true
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

            if (typeof pieChart !== "undefined" && pieChart) {
                pieChart.destroy();
                pieChart = undefined;
            }

            pieChartContainer.innerHTML = "";

            if (!ratingData || Object.keys(ratingData).length === 0) {
                pieChartContainer.innerHTML = `
            <div style="text-align: center; padding: 20px; color: #999;">
                <p><i class="fas fa-exclamation-circle"></i> Không có đánh giá nào</p>
            </div>`;
                return;
            }

            let series = [];
            let labels = [];

            ratingData.forEach(item => {
                series.push(parseFloat(item.total_courses));
                labels.push(parseFloat(item.rating) + " sao");
            });

            let pieOptions = {
                series: series,
                chart: {
                    type: "pie",
                    height: 350
                },
                labels: labels,
                legend: {
                    position: "bottom"
                }
            };

            pieChart = new ApexCharts(pieChartContainer, pieOptions);
            pieChart.render();
        }

        updatePieChart(ratingData);

        updateChart(newData);
    </script>
@endpush
