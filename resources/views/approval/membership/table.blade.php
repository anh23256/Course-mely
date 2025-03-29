<div class="card-body" id="item_List">
    <div class="listjs-table" id="customerList">
        <div class="table-responsive table-card mt-3 mb-1">
            <table class="table align-middle table-nowrap" id="customerTable">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Tên gói</th>
                        <th>Giảng viên</th>
                        <th>Email</th>
                        <th>Người kiểm duyệt</th>
                        <th>Giá</th>
                        <th>Thời hạn</th>
                        <th>Trạng thái</th>
                        <th>Ngày yêu cầu</th>
                        <th>Ngày kiểm duyệt</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @foreach ($approvals as $approval)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $approval->membershipPlan->name }}</td>
                            <td>{{ $approval->membershipPlan->instructor->name  }}</td>
                            <td>{{ $approval->membershipPlan->instructor->email  }}</td>
                            <td>{{ $approval->approver->name  }}</td>
                            <td>{{ number_format($approval->membershipPlan->price, 0, ',', '.') }}đ
                            </td>
                            <td>{{ $approval->membershipPlan->duration_months }} tháng</td>
                            <td>
                                @if ($approval->status === 'pending')
                                    <span class="badge bg-warning">Chờ duyệt</span>
                                @elseif ($approval->status === 'approved')
                                    <span class="badge bg-success">Đã duyệt</span>
                                @elseif ($approval->status === 'rejected')
                                    <span class="badge bg-danger">Từ chối</span>
                                @endif
                            </td>
                            <td>{{ $approval->request_date }}</td>
                            <td>{{ $approval->approved_at }}  </td>
                           
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            {{ $approvals->appends(request()->query())->links() }}
        </div>
    </div>
</div>