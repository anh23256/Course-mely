@foreach($topUsers as $topUser)
    <tr>
        <td>
            <a href="#"
               class="fw-medium link-primary">
                {{ $loop->iteration }}
            </a>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                    <img src="{{ $topUser->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}" alt=""
                         class="avatar-xs rounded-circle object-fit-cover"/>
                </div>
                <div class="flex-grow-1">
                    {{ $topUser->name ?? '' }}
                </div>
            </div>
        </td>
        <td>{{ $topUser->total_courses_purchased ?? '' }}</td>
        <td>{{ number_format($topUser->total_spent ?? 0) ?? '' }}</td>
        <td>{{ $topUser->last_purchase_date }}</td>
    </tr>
@endforeach
