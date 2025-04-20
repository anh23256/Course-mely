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