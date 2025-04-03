<div class="listjs-table" id="customerList">
    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Mã hóa đơn</th>
                    <th>Người mua</th>
                    <th>Khoá học</th>
                    <th>Giảng viên</th>
                    <th>Tổng thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày mua</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $invoice->code }}</td>
                        <td><span
                                class="text-danger fw-bold">{{ $invoice->user->name ?? '' }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $invoice->course->thumbnail }}" class="course-thumbnail me-3" alt="">
                                <span
                                    class="fw-medium">{{ Str::limit($invoice->course->name ?? 'Không có tên', 40) }}</span>
                            </div>
                        </td>
                        <td>
                            {{ $invoice->course->instructor->name ?? '' }}
                        </td>
                        <td>{{ number_format($invoice->final_amount ?? 0) }} VND</td>
                        <td>
                            <span class="badge rounded-pill bg-success badge-status">Hoàn thành</span>
                        </td>
                        <td>{{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') : '' }}
                        </td>
                        <td>
                            <a href="{{ route('admin.invoices.show', $invoice->code ?? '') }}"
                               class="btn btn-sm btn-soft-info rounded-circle" data-bs-toggle="tooltip"
                               title="Xem chi tiết">
                                <i class="ri-eye-line"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        {{ $invoices->appends(request()->query())->links() }}
    </div>
</div>
