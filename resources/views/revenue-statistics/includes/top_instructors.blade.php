@foreach ($topInstructors as $topInstructor)
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                    <img src="{{ $topInstructor->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}"
                        alt="" class="avatar-sm p-2 rounded-circle object-fit-cover" />
                </div>
                <div>
                    <h5 class="fs-14 my-1 fw-medium">
                        <a href="" class="text-reset">
                            {{ $topInstructor->name ?? '' }}
                        </a>
                    </h5>
                    <span class="text-muted">
                        Tham gia {{ $topInstructor->created_at ? Carbon\Carbon::parse($topInstructor->created_at)->format('d/m/Y') : '' }}
                    </span>
                </div>
            </div>
        </td>
        <td>
            <p class="mb-0">{{ $topInstructor->total_courses ?? '' }}</p>
            <span class="text-muted">Đã bán</span>
        </td>
        <td>
            <h5 class="fs-14 mb-0">
                {{ $topInstructor->total_enrolled_students ?? '' }}
            </h5>
        </td>
        <td>
            <span class="text-muted">
                {{ number_format($topInstructor->total_revenue ?? 0) }}
            </span>
        </td>
    </tr>
@endforeach
