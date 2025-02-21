<table class="table align-middle table-nowrap" id="customerTable">
    <thead class="table-light">
        <tr>
            <th scope="col" style="width: 50px;">
                <input type="checkbox" id="checkAll">
            </th>
            <th>#</th>
            <th>Tiêu đề</th>
            <th>Loại câu hỏi</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
            <th>Hành Động</th>
        </tr>
    </thead>
    <tbody class="list">
        @foreach ($qaSystems as $qaSystem)
            <tr>
                <th scope="row">
                    <div class="form-check">
                        <input class="form-check-input" id="checkAll" type="checkbox"
                            name="itemID" value="{{ $qaSystem->id }}">
                    </div>
                </th>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $qaSystem->title ?? '' }}</td>
                <td>
                    @if ($qaSystem->answer_type === 'multiple')
                        <span class="badge bg-primary">
                            Chọn nhiều
                        </span>
                    @else
                        <span class="badge bg-info">
                            Chọn một
                        </span>
                    @endif
                </td>
                <td>
                    @if ($qaSystem->status === 1)
                        <span class="badge bg-success">
                            Hoạt động
                        </span>
                    @else
                        <span class="badge bg-danger">
                            Không hoạt động
                        </span>
                    @endif
                </td>
                <td>{{ $qaSystem->created_at->format('d/m/Y') ?? '' }}</td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.qa-systems.edit', $qaSystem->id) }}">
                            <button class="btn btn-sm btn-warning edit-item-btn">
                                <span class="ri-edit-box-line"></span>
                            </button>
                        </a>
                        <a href="{{ route('admin.qa-systems.destroy', $qaSystem->id) }}"
                            class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                            <span class="ri-delete-bin-7-line"></span>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="row justify-content-end">
    {{ $qaSystems->appends(request()->query())->links() }}
</div>