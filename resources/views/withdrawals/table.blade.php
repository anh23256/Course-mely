<div class="listjs-table" id="customerList">
    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Ngân hàng</th>
                    <th>Tên chủ tài khoản</th>
                    <th>Số tài khoản</th>
                    <th>Số tiền</th>
                    <th>QR</th>
                    <th>Trạng thái</th>
                    <th>Ngày yêu cầu</th>
                    <th>Ngày xác nhận</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($withdrawals as $withdrawal)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td >
                            @php
                                $bank = $supportedBank->firstWhere(
                                    'short_name',
                                    $withdrawal->bank_name,
                                );
                            @endphp
                            @if ($bank)
                                <img src="{{ $bank->logo_rounded }}" alt="Bank Logo" class="me-2" style="width: 24px; height: 24px; object-fit: contain;"/>
                                {{ $bank->short_name }}
                            @else
                                {{ $withdrawal->bank_name ?? 'Không có thông tin' }}
                            @endif
                        </td>
                        <td>{{ $withdrawal->account_holder ?? 'Không có thông tin' }}</td>
                        <td><span
                                class="text-danger">{{ $withdrawal->account_number ?? 'Không có thông tin' }}</span>
                        </td>
                        <td>{{ number_format($withdrawal->amount ?? 0) }} VND</td>
                        <td>
                            <img id="thumbnail-{{ $withdrawal->id }}"
                                class="img-thumbnail img-preview" width="50" height="50"
                                src="{{ \Illuminate\Support\Facades\Storage::url($withdrawal->qr_code ?? '') }}"
                                alt="QR Code {{ $withdrawal->id }}" style="cursor: pointer;" />
                        </td>
                        <td>
                            @if ($withdrawal->status === 'Hoàn thành')
                                <span class="badge bg-success w-100">Hoàn thành</span>
                            @elseif($withdrawal->status === 'Đang xử lý')
                                <span class="badge bg-warning w-100">Đang xử lý</span>
                            @elseif($withdrawal->status === 'Chờ xác nhận lại')
                                <span class="badge bg-primary w-100">Chờ xác nhận lại</span>
                            @elseif($withdrawal->status === 'Đã xử lý')
                                <span class="badge bg-info w-100">Đã xử lý</span>
                            @elseif($withdrawal->status === 'Từ chối')
                                <span class="badge bg-danger w-100">Từ chối</span>
                            @else
                                <span class="badge bg-secondary w-100">Không xác định</span>
                            @endif
                        </td>
                        <td>{!! $withdrawal->request_date
                            ? \Carbon\Carbon::parse($withdrawal->request_date)->format('d/m/Y')
                            : '<span class="btn btn-sm btn-soft-warning">Không có thông tin</span>' !!}
                        </td>
                        <td>{!! $withdrawal->completed_date
                            ? \Carbon\Carbon::parse($withdrawal->completed_date)->format('d/m/Y')
                            : '<span class="btn btn-sm btn-soft-warning">Chưa xác nhận</span>' !!}
                        </td>
                        <td>
                            <a href="{{ route('admin.withdrawals.show', $withdrawal->id) }}">
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
        {{ $withdrawals->appends(request()->query())->links() }}
    </div>
</div>