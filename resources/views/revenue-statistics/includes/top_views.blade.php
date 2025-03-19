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