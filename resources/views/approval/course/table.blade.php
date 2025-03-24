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
                    <th>Trạng thái</th>
                    <th>Người kiểm duyệt</th>
                    <th>Ngày gửi yêu cầu</th>
                    <th>Ngày kiểm duyệt</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($approvals as $approval)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($approval->course->name ?? 'Không có tên', 50) }}</td>
                        <td>{{ $approval->course->user->name }}</td>
                        <td>
                            <img style="height: 80px" src="{{ $approval->course->thumbnail }}"
                                alt="" class="w-100 object-fit-cover">
                        </td>
                        <td>{{ number_format($approval->course->price ?? 0) ?? '' }}</td>
                        <td>
                            @if ($approval->status == 'pending')
                                <span class="btn btn-sm btn-soft-warning">Chờ xử lý</span>
                            @elseif($approval->status == 'approved')
                                <span class="btn btn-sm btn-soft-success">Đã kiểm duyệt</span>
                            @else
                                <span class="btn btn-sm btn-soft-danger">Từ chối</span>
                            @endif
                        </td>
                        <td>
                            {!! $approval->approver->name ?? '<span class="btn btn-sm btn-soft-success">Hệ thống đã xử lý</span>' !!}
                        </td>
                        <td>{!!  $approval->request_date ? \Carbon\Carbon::parse($approval->request_date)->format('d/m/Y') : '<span class="btn btn-sm btn-soft-warning">Chưa kiểm duyệt</span>' !!}</td>
                        <td>
                            {!! $approval->approved_at ?\Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y') : '<span class="btn btn-sm btn-soft-warning">Chưa kiểm duyệt</span>' !!}
                        </td>
                        <td>
                            <a href="{{ route('admin.approvals.courses.show', $approval->id) }}">
                                <button class="btn btn-sm btn-info edit-item-btn">
                                    <span class="ri-folder-user-line"></span>
                                </button>
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
