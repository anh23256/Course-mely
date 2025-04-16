<div class="card-body" id="item_List">
    <div class="listjs-table">
        <div class="row g-4 mb-3">
            <div class="col-sm-auto">
                <div>
                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary add-btn"><i
                            class="ri-add-line align-bottom me-1"></i> Thêm mới</a>
                    <button class="btn btn-danger" id="deleteSelected">
                        <i class="ri-delete-bin-2-line"> Xóa nhiều</i>
                    </button>
                </div>
            </div>
            <div class="col-sm">
                <div class="d-flex justify-content-sm-end">
                    <div class="search-box ms-2">
                        <form action="{{ route('admin.coupons.index') }}" method="get">
                            <input type="text" name="query" class="form-control search"
                                placeholder="Search..." value="{{ old('query') }}">
                            <i class="ri-search-line search-icon"></i>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive table-card mt-3 mb-1">
            <table class="table align-middle table-nowrap">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 50px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="checkAll"
                                    value="option">
                            </div>
                        </th>
                        <th>STT</th>
                        <th>Giảng viên</th>
                        <th>Hoa hồng hiện tại (%)</th>
                        <th>Lịch sử thay đổi</th>
                        <th>Cập nhật lúc</th>
                    </tr>
                </thead>
                <tbody class="list form-check-all">
                    @foreach ($instructorCommissions as $instructorCommission)
                        <tr>
                            <th scope="row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="itemID"
                                        value="{{ $instructorCommission->id }}">
                                </div>
                            </th>

                            <td class="id">{{ $loop->iteration }}</td>

                            <td>{{ $instructorCommission->instructor->name ?? 'Không tìm thấy' }}</td>
                            <td>{{ number_format($instructorCommission->rate, 2) }}%</td>
                            <td>
                                @php
                                    $logs = json_decode($instructorCommission->rate_logs, true);
                                @endphp
                                @if ($logs)
                                <ul class="list-unstyled mb-0">
                                    @foreach ($logs as $log)
                                        <li class="mb-1 d-flex align-items-center">
                                            <i class="bi bi-clock-history text-primary me-2"></i>
                                            <span>
                                                <strong>{{ number_format($log['rate'], 2) }}%</strong>
                                                <small class="text-muted">- {{ \Carbon\Carbon::parse($log['changed_at'])->format('H:i d/m/Y') }}</small>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                @else
                                    <em>Không có dữ liệu</em> 
                                @endif
                            </td>
                            <td>{{ $instructorCommission->updated_at->format('d/m/Y H:i') }}</td>
                            


                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $instructorCommissions->appends(request()->query())->links() }}
    </div>
</div><!-- end card -->  