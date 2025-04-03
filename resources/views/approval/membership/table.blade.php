<div class="listjs-table" id="customerList">
    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Tên gói</th>
                    <th>Giảng viên</th>
                    <th>Email</th>
                    <th>Giá</th>
                    <th>Thời hạn</th>
                    <th>Trạng thái</th>
                    <th>Người kiểm duyệt</th>
                    <th>Ngày yêu cầu</th>
                    <th>Ngày kiểm duyệt</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($approvals as $approval)
                    <tr>
                        <td>{{ $loop->iteration ?? '' }}</td>
                        <td>{{ $approval->membershipPlan->name ?? '' }}</td>
                        <td>
                            <span
                                class="text-danger font-weight-bold">{{ $approval->membershipPlan->instructor->name ?? '' }}</span>
                            <br>
                            <small
                                class="text-muted">{{ $approval->membershipPlan->instructor->profile->phone ?? '' }}</small>
                        </td>
                        <td>{{ $approval->membershipPlan->instructor->email ?? '' }}</td>
                        <td>{{ number_format($approval->membershipPlan->price ?? 0, 0, ',', '.') }}đ
                        </td>
                        <td>{{ $approval->membershipPlan->duration_months ?? 1 }} tháng</td>
                        <td>
                            @if ($approval->status === 'pending')
                                <span class="badge bg-warning">Chờ duyệt</span>
                            @elseif ($approval->status === 'approved')
                                <span class="badge bg-success">Đã duyệt</span>
                            @elseif ($approval->status === 'rejected')
                                <span class="badge bg-danger">Từ chối</span>
                            @endif
                        </td>
                        <td>
                            {!! !empty($approval->approver->name)
                                ? '<span class="badge bg-primary text-white"><i class="bx bx-user"></i> ' . $approval->approver->name . '</span>'
                                : '<span class="badge bg-secondary text-white"><i class="bx bx-cog"></i> Hệ thống đã xử lý</span>' !!}
                        </td>
                        <td>
                            {!! $approval->request_date
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
                                <span class="badge bg-warning text-dark"><i
                                        class="bx bx-time"></i> Chưa kiểm duyệt</span>
                            @endif
                        </td>
                        <td>
                            <a href="#">
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