@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        h5 {
            min-height: 50px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
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
        <!-- end page title -->
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
        <div class="row dash-nft">
            <div class="col-xxl-8">
                <div class="row">
                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="row g-0">
                                    <div class="col-xxl-8">
                                        <div class="">
                                            <div class="card-header border-0 align-items-center d-flex">
                                                <h4 class="card-title mb-0 flex-grow-1">Top 10 khóa học được mua nhiều nhất
                                                </h4>
                                            </div><!-- end card header -->
                                            <div id="bestSellingCourses"
                                                data-colors='["--vz-primary","--vz-success", "--vz-light"]'
                                                class="apex-charts" dir="ltr"></div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-4">
                                        <div class="border-start p-4 h-100 d-flex flex-column">

                                            <div class="w-100">
                                                <div class="d-flex align-items-center">
                                                    <img src="https://img.themesbrand.com/velzon/images/img-2.gif"
                                                        class="img-fluid avatar-xs rounded-circle object-fit-cover"
                                                        alt="">
                                                    <div class="ms-3 flex-grow-1">
                                                        <h5 class="fs-16 mb-1">Top 10 khóa học có tỉ lệ hoàn thành cao nhất
                                                        </h5>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div id="topCompletedCourses"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                </div><!--end row-->
            </div><!--end col-->

            <div class="col-xxl-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h6 class="card-title mb-0 flex-grow-1">Top 10 giảng viên có thu nhập cao nhất</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-card">
                            <div data-simplebar style="max-height: 400px;">
                                <ul class="list-group list-group-flush">
                                    @foreach ($topInstructors as $topInstructor)
                                        <li class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $topInstructor->avatar }}" alt=""
                                                    class="avatar-xs object-fit-cover rounded-circle">
                                                <div class="ms-2 flex-grow-1">
                                                    <a class="stretched-link">
                                                        <h6 class="fs-14 mb-1">{{ $topInstructor->name }}</h6>
                                                    </a>
                                                    <p class="mb-0 text-muted"></p>
                                                </div>
                                                <div class="ms-2">
                                                    <h6 class="fs-14">{{ number_format($topInstructor->total_revenue) }}
                                                        VND</h6>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end col-->
        </div>
        <!--end row-->


        <div class="row">
            <div class="col-xxl-8">
                <div class="swiper marketplace-swiper rounded gallery-light">
                    <div class="d-flex pt-2 pb-4">
                        <h5 class="card-title fs-18 mb-1">Top 10 khóa học có lượt xem nhiều nhất</h5>
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
                                            {{ $getTopViewCourse->views }} </p>
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
                    <div class="swiper-button-next">
                    </div>
                    <div class="swiper-button-prev">
                    </div>
                </div>
            </div><!--end col-->
            <div class="col-xxl-4">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Top 10 giảng viên được yêu thích nhất</h4>
                    </div>
                    <div class="card-body">
                        <div id="topInstructorsChart"></div>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

        <div class="row">
            <div class="col-xxl-6">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Recent NFTs</h4>
                        <div class="flex-shrink-0">
                            <div class="dropdown card-header-dropdown">
                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="fw-semibold text-uppercase fs-12">Sort by: </span><span
                                        class="text-muted">Popular <i class="mdi mdi-chevron-down ms-1"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Popular</a>
                                    <a class="dropdown-item" href="#">Newest</a>
                                    <a class="dropdown-item" href="#">Oldest</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-card">
                            <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                <thead class="text-muted bg-light-subtle">
                                    <tr>
                                        <th>Collection</th>
                                        <th>Volume</th>
                                        <th>24h %</th>
                                        <th>Creators</th>
                                        <th>Items</th>
                                    </tr>
                                </thead><!-- end thead -->
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <img src="../assets/images/nft/img-01.jpg" alt=""
                                                        class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><a href="apps-nft-item-details.html">Abstract Face
                                                            Painting</a></h6>
                                                    <p class="text-muted mb-0"> Artworks</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><img src="../assets/images/svg/crypto-icons/btc.svg" class="avatar-xxs me-2"
                                                alt="">48,568.025</td>
                                        <td>
                                            <span class="text-success mb-0"><i
                                                    class="mdi mdi-trending-up align-middle me-1"></i>5.26
                                            </span>
                                        </td>
                                        <td>6.8K</td>
                                        <td>18.0K</td>
                                    </tr><!-- end -->

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <img src="https://img.themesbrand.com/velzon/images/img-5.gif"
                                                        alt="" class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><a href="apps-nft-item-details.html">Long-tailed
                                                            Macaque</a></h6>
                                                    <p class="text-muted mb-0">Games</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><img src="../assets/images/svg/crypto-icons/ltc.svg" class="avatar-xxs me-2"
                                                alt="">87,142.027</td>
                                        <td>
                                            <span class="text-danger mb-0"><i
                                                    class="mdi mdi-trending-down align-middle me-1"></i>3.07
                                            </span>
                                        </td>
                                        <td>2.6K</td>
                                        <td>6.3K</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <img src="../assets/images/nft/img-06.jpg" alt=""
                                                        class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><a href="apps-nft-item-details.html">Robotic Body
                                                            Art</a></h6>
                                                    <p class="text-muted mb-0">Photography</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><img src="../assets/images/svg/crypto-icons/etc.svg" class="avatar-xxs me-2"
                                                alt="">33,847.961</td>
                                        <td>
                                            <span class="text-success mb-0"><i
                                                    class="mdi mdi-trending-up align-middle me-1"></i>7.13
                                            </span>
                                        </td>
                                        <td>7.5K</td>
                                        <td>14.6K</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <img src="../assets/images/nft/img-04.jpg" alt=""
                                                        class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><a href="apps-nft-item-details.html">Smillevers
                                                            Crypto</a></h6>
                                                    <p class="text-muted mb-0">Artworks</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><img src="../assets/images/svg/crypto-icons/dash.svg" class="avatar-xxs me-2"
                                                alt="">73,654.421</td>
                                        <td>
                                            <span class="text-success mb-0"><i
                                                    class="mdi mdi-trending-up align-middle me-1"></i>0.97
                                            </span>
                                        </td>
                                        <td>5.3K</td>
                                        <td>36.4K</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <img src="../assets/images/nft/img-03.jpg" alt=""
                                                        class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><a href="apps-nft-item-details.html">Creative
                                                            Filtered Portrait</a></h6>
                                                    <p class="text-muted mb-0"> 3d Style</p>
                                                </div>
                                                <div class="flex-grow-1"></div>
                                            </div>
                                        </td>
                                        <td><img src="../assets/images/svg/crypto-icons/bnb.svg" class="avatar-xxs me-2"
                                                alt="">66,742.077</td>
                                        <td>
                                            <span class="text-danger mb-0"><i
                                                    class="mdi mdi-trending-down align-middle me-1"></i>1.08
                                            </span>
                                        </td>
                                        <td>3.1K</td>
                                        <td>12.4K</td>
                                    </tr><!-- end -->
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <img src="../assets/images/nft/img-02.jpg" alt=""
                                                        class="avatar-xs rounded-circle">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><a href="apps-nft-item-details.html">The
                                                            Chirstoper</a></h6>
                                                    <p class="text-muted mb-0"> Crypto Card</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><img src="../assets/images/svg/crypto-icons/usdt.svg" class="avatar-xxs me-2"
                                                alt="">34,736.209</td>
                                        <td>
                                            <span class="text-success mb-0"><i
                                                    class="mdi mdi-trending-up align-middle me-1"></i>4.52
                                            </span>
                                        </td>
                                        <td>7.2K</td>
                                        <td>25.0K</td>
                                    </tr><!-- end -->
                                </tbody><!-- end tbody -->
                            </table><!-- end table -->
                        </div><!-- end tbody -->

                    </div>
                </div>
            </div><!--end col-->
            <!--end card-->
            <div class="col-xxl-3 col-lg-6">
                <div class="card card-height-100">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Worldwide Top Creators</h4>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-soft-primary btn-sm">
                                Export Report
                            </button>
                        </div>
                    </div><!-- end card header -->

                    <!-- card body -->
                    <div class="card-body">

                        <div id="creators-by-locations" data-colors='["--vz-light", "--vz-success", "--vz-primary"]'
                            style="height: 265px" dir="ltr"></div>

                        <div class="mt-1">
                            <p class="mb-1"><img src="../assets/images/flags/us.svg" alt="" height="15"
                                    class="rounded me-2"> United States <span class="float-end">34%</span></p>
                            <p class="mb-1"><img src="../assets/images/flags/russia.svg" alt="" height="15"
                                    class="rounded me-2"> Russia <span class="float-end">27%</span></p>
                            <p class="mb-1"><img src="../assets/images/flags/spain.svg" alt="" height="15"
                                    class="rounded me-2"> Spain <span class="float-end">21%</span></p>
                            <p class="mb-1"><img src="../assets/images/flags/italy.svg" alt="" height="15"
                                    class="rounded me-2"> Italy <span class="float-end">13%</span></p>
                            <p class="mb-0"><img src="../assets/images/flags/germany.svg" alt=""
                                    height="15" class="rounded me-2"> Germany <span class="float-end">5%</span></p>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div><!--end col-->
            <div class="col-xxl-3 col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h6 class="card-title flex-grow-1 mb-0">Top Collections</h6>
                        <a href="apps-nft-collections.html" type="button"
                            class="btn btn-soft-primary btn-sm flex-shrink-0">
                            See All <i class="ri-arrow-right-line align-bottom"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="swiper collection-slider">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="dash-collection overflow-hidden rounded-top position-relative">
                                        <img src="../assets/images/nft/img-03.jpg" alt="" height="220"
                                            class="object-fit-cover w-100" />
                                        <div
                                            class="content position-absolute bottom-0 m-2 p-2 start-0 end-0 rounded d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <a href="#!">
                                                    <h5 class="text-white fs-16 mb-1">Artworks</h5>
                                                </a>
                                                <p class="text-white text-opacity-75 mb-0">4700+ Items</p>
                                            </div>
                                            <div class="avatar-xxs">
                                                <div class="avatar-title bg-white rounded-circle">
                                                    <a href="#!" class="link-success"><i
                                                            class="ri-arrow-right-line"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="dash-collection overflow-hidden rounded-top position-relative">
                                        <img src="../assets/images/nft/img-04.jpg" alt="" height="220"
                                            class="object-fit-cover w-100" />
                                        <div
                                            class="content position-absolute bottom-0 m-2 p-2 start-0 end-0 rounded d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <a href="#!">
                                                    <h5 class="text-white fs-16 mb-1">Crypto Card</h5>
                                                </a>
                                                <p class="text-white text-opacity-75 mb-0">743+ Items</p>
                                            </div>
                                            <div class="avatar-xxs">
                                                <div class="avatar-title bg-white rounded-circle">
                                                    <a href="#!" class="link-success"><i
                                                            class="ri-arrow-right-line"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="dash-collection overflow-hidden rounded-top position-relative">
                                        <img src="https://img.themesbrand.com/velzon/images/img-5.gif" alt=""
                                            height="220" class="object-fit-cover w-100" />
                                        <div
                                            class="content position-absolute bottom-0 m-2 p-2 start-0 end-0 rounded d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <a href="#!">
                                                    <h5 class="text-white fs-16 mb-1">3d Style</h5>
                                                </a>
                                                <p class="text-white text-opacity-75 mb-0">4781+ Items</p>
                                            </div>
                                            <div class="avatar-xxs">
                                                <div class="avatar-title bg-white rounded-circle">
                                                    <a href="#!" class="link-success"><i
                                                            class="ri-arrow-right-line"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="dash-collection overflow-hidden rounded-top position-relative">
                                        <img src="../assets/images/nft/img-06.jpg" alt="" height="220"
                                            class="object-fit-cover w-100" />
                                        <div
                                            class="content position-absolute bottom-0 m-2 p-2 start-0 end-0 rounded d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <a href="#!">
                                                    <h5 class="text-white fs-16 mb-1">Collectibles</h5>
                                                </a>
                                                <p class="text-white text-opacity-75 mb-0">3468+ Items</p>
                                            </div>
                                            <div class="avatar-xxs">
                                                <div class="avatar-title bg-white rounded-circle">
                                                    <a href="#!" class="link-success"><i
                                                            class="ri-arrow-right-line"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end swiper-->
                    </div>
                </div>
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title flex-grow-1 mb-0">Popular Creators</h5>
                        <a href="apps-nft-creators.html" type="button"
                            class="btn btn-soft-primary btn-sm flex-shrink-0">
                            See All <i class="ri-arrow-right-line align-bottom"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="swiper collection-slider">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="d-flex">
                                        <div class="flex-shink-0">
                                            <img src="../assets/images/nft/img-02.jpg" alt=""
                                                class="avatar-sm object-fit-cover rounded">
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <a href="pages-profile.html">
                                                <h5 class="mb-1">Alexis Clarke</h5>
                                            </a>
                                            <p class="text-muted mb-0"><i class="mdi mdi-ethereum text-primary fs-14"></i>
                                                81,369 ETH</p>
                                        </div>
                                        <div>
                                            <div class="dropdown float-end">
                                                <button class="btn btn-ghost-primary btn-icon dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle fs-16"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">View</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Share</a></li>
                                                    <li><a class="dropdown-item" href="#!">Report</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="d-flex">
                                        <div class="flex-shink-0">
                                            <img src="../assets/images/nft/img-01.jpg" alt=""
                                                class="avatar-sm object-fit-cover rounded">
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <a href="pages-profile.html">
                                                <h5 class="mb-1">Timothy Smith</h5>
                                            </a>
                                            <p class="text-muted mb-0"><i class="mdi mdi-ethereum text-primary fs-14"></i>
                                                4,754 ETH</p>
                                        </div>
                                        <div>
                                            <div class="dropdown float-end">
                                                <button class="btn btn-ghost-primary btn-icon dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle fs-16"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">View</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Share</a></li>
                                                    <li><a class="dropdown-item" href="#!">Report</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="d-flex">
                                        <div class="flex-shink-0">
                                            <img src="../assets/images/nft/img-04.jpg" alt=""
                                                class="avatar-sm object-fit-cover rounded">
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <a href="pages-profile.html">
                                                <h5 class="mb-1">Herbert Stokes</h5>
                                            </a>
                                            <p class="text-muted mb-0"><i class="mdi mdi-ethereum text-primary fs-14"></i>
                                                68,945 ETH</p>
                                        </div>
                                        <div>
                                            <div class="dropdown float-end">
                                                <button class="btn btn-ghost-primary btn-icon dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle fs-16"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">View</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Share</a></li>
                                                    <li><a class="dropdown-item" href="#!">Report</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="d-flex">
                                        <div class="flex-shink-0">
                                            <img src="../assets/images/users/avatar-1.jpg" alt=""
                                                class="avatar-sm object-fit-cover rounded">
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <a href="pages-profile.html">
                                                <h5 class="mb-1">Glen Matney</h5>
                                            </a>
                                            <p class="text-muted mb-0"><i class="mdi mdi-ethereum text-primary fs-14"></i>
                                                49,031 ETH</p>
                                        </div>
                                        <div>
                                            <div class="dropdown float-end">
                                                <button class="btn btn-ghost-primary btn-icon dropdown" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle fs-16"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">View</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Share</a></li>
                                                    <li><a class="dropdown-item" href="#!">Report</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end swiper-->
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

    </div>
@endsection
@push('page-scripts')
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/coming-soon.init.js') }}"></script>

    <!-- Marketplace init -->
    <script src="{{ asset('assets/js/pages/dashboard-nft.init.js') }}"></script>
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

        let chartBestSellingCourses;

        function renderBestSellingCourses(data = []) {
            let chartContainer = document.querySelector("#bestSellingCourses");

            if (chartBestSellingCourses) {
                chartBestSellingCourses.destroy();
            }

            if (!data.length) {
                chartContainer.innerHTML = `<p style="text-align: center; color: #999;">Không có dữ liệu</p>`;
                return;
            }

            let colors = [
                "#008FFB", "#00E396", "#FEB019", "#FF4560", "#775DD0",
                "#546E7A", "#26A69A", "#D7263D", "#F86624", "#1B998B",
                "#2E93fA", "#66DA26", "#E91E63", "#FF9800", "#C0C0C0",
                "#8D6E63", "#2b908f", "#F9A3A4", "#90EE7E", "#F48024"
            ];


            let options = {
                chart: {
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Số lượng bán',
                    type: "bar",
                    data: data.map(item => item.total_sales)
                }, {
                    name: "Doanh thu (triệu VND)",
                    type: "line",
                    data: data.map(item => item.total_amount)
                }],
                yaxis: [{
                    labels: {
                        formatter: function(value) {
                            return value.toLocaleString("vi-VN");
                        }
                    }
                }, {
                    opposite: true,
                    labels: {
                        formatter: function(value) {
                            return value.toLocaleString("vi-VN", {
                                style: "currency",
                                currency: "VND"
                            }).replace("₫", "");
                        }
                    }
                }],
                xaxis: {
                    categories: data.map((_, index) => index + 1)
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
                        formatter: function(value, {
                            seriesIndex,
                            dataPointIndex
                        }) {
                            if (seriesIndex === 0) {
                                return `${data[dataPointIndex].name}: ${value.toLocaleString("vi-VN") + ' lượt bán'}`;
                            } else {
                                return `${data[dataPointIndex].name}: ${value.toLocaleString("vi-VN", { style: "currency", currency: "VND" })}`;
                            }
                        }
                    }
                }
            };

            chartBestSellingCourses = new ApexCharts(chartContainer, options);
            chartBestSellingCourses.render();
        }

        let chartTopInstructorFollows;

        function renderTopInstructorsFollow(data = []) {
            let chartContainer = document.querySelector("#topInstructorsChart");

            if (chartTopInstructorFollows) {
                chartTopInstructorFollows.destroy();
            }

            if (!data.length) {
                chartContainer.innerHTML = `<p style="text-align: center; color: #999;">Không có dữ liệu</p>`;
                return;
            }

            let options = {
                series: [{
                    data: data.map(item => ({
                        x: `${item.name}`,
                        y: item.total_student,
                        name: item.name,
                        custom: {
                            name: item.name,
                            follow: item.total_follow,
                            students: item.total_student
                        }
                    }))
                }],
                chart: {
                    type: "radar",
                    height: 400,
                    toolbar: {
                        show: false
                    }
                },
                colors: ["#008FFB", "#00E396", "#FEB019", "#FF4560", "#775DD0"],
                legend: {
                    show: false
                },
                tooltip: {
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        let data = w.config.series[seriesIndex].data[dataPointIndex].custom;
                        return `<div class="custom-tooltip">
                    🧑‍🏫 <b>${data.name}</b><br>
                    🔥 Follow: <b>${data.follow}</b><br>
                    🎓 Học viên: <b>${data.students}</b>
                </div>`;
                    },
                    position: "left",
                    style: {
                        fontSize: '12px'
                    }
                }
            };

            chartTopInstructorFollows = new ApexCharts(chartContainer, options);
            chartTopInstructorFollows.render();
        }

        let chartTopCompletedCourses;

        function renderTopCompletedCourses(data = []) {
            let chartContainer = document.querySelector("#topCompletedCourses");

            if (chartTopCompletedCourses) {
                chartTopCompletedCourses.destroy();
            }

            if (!data.length) {
                chartContainer.innerHTML = `<p style="text-align: center; color: #999;">Không có dữ liệu</p>`;
                return;
            }

            let options = {
                chart: {
                    type: 'bar',
                    height: 330,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Tỷ lệ hoàn thành (%)',
                    data: data.map(item => item.avg_progress)
                }],
                xaxis: {
                    categories: data.map((item, index) => index + 1),
                    labels: {
                        formatter: (val) => Math.round(val)
                    }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => val.toString(),
                        style: {
                            fontSize: '14px'
                        }
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
                    formatter: (val) => `${val}%`
                },
                tooltip: {
                    y: {
                        formatter: (val, {
                            dataPointIndex
                        }) => {
                            let courseName = data[dataPointIndex]?.course?.name || 'N/A';
                            return `${courseName}: ${val}%`;
                        }
                    }
                }
            };

            chartTopCompletedCourses = new ApexCharts(chartContainer, options);
            chartTopCompletedCourses.render();
        }

        renderBestSellingCourses(@json($getBestSellingCourses));
        renderTopCompletedCourses(@json($topCoursesProgress));
        renderTopInstructorsFollow(@json($topInstructorsFollows));
    </script>
@endpush
