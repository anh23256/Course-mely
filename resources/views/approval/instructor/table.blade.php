<div class="listjs-table" id="customerList">
    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Tên giảng viên</th>
                    <th>Email</th>
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
                        <td>{{ $approval->user->name ?? '' }}</td>
                        <td>{{ $approval->user->email ?? '' }}</td>
                        <td>
                            {!! !empty($approval->approver->name)
                                ? '<span class="badge bg-primary text-white"><i class="bx bx-user"></i> ' . $approval->approver->name . '</span>' 
                                : '<span class="badge bg-secondary text-white"><i class="bx bx-cog"></i> Hệ thống đã xử lý</span>' !!}
                        </td>
                        <td>
                            @if ($approval->status == 'pending')
                                <span class="badge bg-warning text-dark"><i class="bx bx-time-five"></i> Chờ xử lý</span>
                            @elseif($approval->status == 'approved')
                                <span class="badge bg-success text-white"><i class="bx bx-check-circle"></i> Đã kiểm duyệt</span>
                            @else
                                <span class="badge bg-danger text-white"><i class="bx bx-x-circle"></i> Từ chối</span>
                            @endif
                        </td>
                        <td>
                            {!! $approval->request_date 
                                ? '<span class="badge bg-info text-white"><i class="bx bx-calendar"></i> ' . \Carbon\Carbon::parse($approval->request_date)->format('d/m/Y') . '</span>'
                                : '<span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Chưa kiểm duyệt</span>' !!}
                        </td>
                        <td>
                            @if($approval->approved_at)
                                <span class="badge bg-success text-white"><i class="bx bx-calendar-check"></i> {{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y') }}</span>
                            @elseif($approval->rejected_at)
                                <span class="badge bg-danger text-white"><i class="bx bx-calendar-x"></i> {{ \Carbon\Carbon::parse($approval->rejected_at)->format('d/m/Y') }}</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Chưa kiểm duyệt</span>
                            @endif
                        </td>                
                        <td>
                            <a
                                href="{{ route('admin.approvals.instructors.show', $approval->id) }}">
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