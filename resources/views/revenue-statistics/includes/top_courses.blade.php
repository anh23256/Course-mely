@foreach($topCourses as $topCourse)
    <tr>
        <td>
            <div class="d-flex align-items-center gap-2 ju">
                <img style="width:70px" src="{{ $topCourse->thumbnail ?? '' }}" alt="" class="img-fluid d-block "/>
                <div>
                    <h5 class="fs-14 my-1"><a href="#" class="text-reset">{{ \Illuminate\Support\Str::limit($topCourse->name, 20) }}</a></h5>
                    <span class="text-muted">{{ $topCourse->created_at ? Carbon\Carbon::parse($topCourse->created_at)->format('d/m/Y') : '' }}</span>
                </div>
            </div>
        </td>
        <td><h5 class="fs-14 my-1 fw-normal">{{ $topCourse->total_sales ?? '' }}</h5></td>
        <td><h5 class="fs-14 my-1 fw-normal">{{ $topCourse->total_enrolled_students ?? '' }}</h5></td>
        <td><h5 class="fs-14 my-1 fw-normal">{{ number_format($topCourse->total_revenue ?? 0) }}</h5></td>
    </tr>
@endforeach
