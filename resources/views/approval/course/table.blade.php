<div class="listjs-table" id="customerList">
    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Tên khoá học</th>
                    <th>Giảng viên</th>
                    <th>Hình ảnh</th>
                    <th>Giá</th>
                    <th>Người kiểm duyệt</th>
                    <th>Trạng thái</th>
                    <th>Ngày gửi yêu cầu</th>
                    <th>Ngày kiểm duyệt</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($approvals as $approval)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td
                            style="max-width: 200px; white-space: normal; word-wrap: break-word; overflow-wrap: break-word;">
                            {{ \Illuminate\Support\Str::limit($approval->course->name ?? 'Không có tên', 60) }}
                        <td>
                            <span
                                class="text-danger font-weight-bold">{{ $approval->course->user->name ?? '' }}</span>
                            <br>
                            <small
                                class="text-muted">{{ $approval->course->user->email ?? '' }}</small>
                        </td>
                        <td>
                            <img style="height: 80px" src="{{ $approval->course->thumbnail }}"
                                alt="" class="w-100 object-fit-cover">
                        </td>
                        <td>{{ $approval->course->price > 0 ? number_format($approval->course->price) : 'Miễn phí' }}
                        </td>
                        <td>
                            {!! !empty($approval->approver->name)
                                ? '<span class="badge bg-primary text-white"><i class="bx bx-user"></i> ' . $approval->approver->name . '</span>'
                                : '<span class="badge bg-secondary text-white"><i class="bx bx-cog"></i> Hệ thống đã xử lý</span>' !!}
                        </td>
                        <td>
                            @if ($approval->status == 'pending')
                                <span class="badge bg-warning text-dark"><i
                                        class="bx bx-time-five"></i> Chờ xử lý</span>
                            @elseif($approval->status == 'approved')
                                <span class="badge bg-success text-white"><i
                                        class="bx bx-check-circle"></i> Đã kiểm duyệt</span>
                            @elseif($approval->status == 'modify_request')
                                <span class="badge bg-info text-white"><i class="bx bx-edit"></i> Sửa
                                    đổi nội dung</span>
                            @else
                                <span class="badge bg-danger text-white"><i
                                        class="bx bx-x-circle"></i> Từ chối</span>
                            @endif
                        </td>
                        <td>
                            {!! !empty($approval->request_date)
                                ? '<span class="badge bg-info text-white"><i class="bx bx-calendar"></i> ' .
                                    \Carbon\Carbon::parse($approval->request_date)->format('d/m/Y') .
                                    '</span>'
                                : '<span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Chưa kiểm duyệt</span>' !!}
                        </td>
                        <td>
                            @if ($approval->approved_at)
                                <span class="badge bg-success text-white"><i
                                        class="bx bx-calendar-check"></i>
                                    {{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y') }}</span>
                            @elseif($approval->rejected_at)
                                <span class="badge bg-danger text-white"><i
                                        class="bx bx-calendar-x"></i>
                                    {{ \Carbon\Carbon::parse($approval->rejected_at)->format('d/m/Y') }}</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bx bx-time"></i>
                                    Chưa kiểm duyệt</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.approvals.courses.show', $approval->id) }}">
                                <button class="btn btn-sm btn-info edit-item-btn">
                                    <span class="ri-eye-line"></span>
                                </button>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        {{ $approvals->appends(request()->query())->links() }}
    </div>
</div>