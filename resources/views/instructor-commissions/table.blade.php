<div class="listjs-table">
    <div class="row g-4 mb-3">
        <div class="col-sm-auto">

        </div>
        <div class="col-sm">
            <div class="d-flex justify-content-sm-end">
                <div class="search-box ms-2">
                    <form action="{{ route('admin.instructor-commissions.index') }}"
                        method="get">
                        <input type="text" name="search_full" class="form-control search"
                            placeholder="Search..." value="{{ old('search_full') }}">
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
                    <th>Cập nhật lúc</th>
                    <th>Lịch sử thay đổi</th>
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
                        <td>
                            <input type="number" step="1"
                                class="form-control input-rate"
                                data-id="{{ $instructorCommission->id }}"
                                value="{{ fmod($instructorCommission->rate * 100, 1) == 0
                                    ? number_format($instructorCommission->rate * 100, 0)
                                    : number_format($instructorCommission->rate * 100, 2) }}"
                                data-old="{{ fmod($instructorCommission->rate * 100, 1) == 0
                                    ? number_format($instructorCommission->rate * 100, 0)
                                    : number_format($instructorCommission->rate * 100, 2) }}"
                                data-name="{{ $instructorCommission->instructor->name }}"
                                style="width: 80px;" />
                        </td>
                        <td>{{ $instructorCommission->updated_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @php
                                $logs = json_decode($instructorCommission->rate_logs, true);
                            @endphp

                            <button type="button" class="btn btn-sm btn-info"
                                data-bs-toggle="modal"
                                data-bs-target="#modalLog_{{ $instructorCommission->id }}">
                                <i class="ri-eye-line"></i>
                            </button>

                            <div class="modal fade" id="modalLog_{{ $instructorCommission->id }}"
                                tabindex="-1"
                                aria-labelledby="modalLogLabel_{{ $instructorCommission->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="modalLogLabel_{{ $instructorCommission->id }}">
                                                Lịch sử thay đổi hoa hồng
                                            </h5>
                                            <button type="button" class="btn-close"
                                                data-bs-dismiss="modal"
                                                aria-label="Đóng"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if (!empty($logs))
                                                <ul
                                                    class="list-unstyled mb-0 small history-log-list">
                                                    @foreach ($logs as $log)
                                                        @php
                                                            $formattedRate =
                                                                fmod($log['rate'] * 100, 1) == 0
                                                                    ? number_format(
                                                                        $log['rate'] * 100,
                                                                        0,
                                                                    )
                                                                    : number_format(
                                                                        $log['rate'] * 100,
                                                                        2,
                                                                    );
                                                        @endphp
                                                        <li
                                                            class="mb-2 d-flex justify-content-between align-items-center border-bottom pb-1">
                                                            <span
                                                                class="text-danger fw-semibold fs-14">{{ $formattedRate }}%</span>
                                                            <span
                                                                class="badge bg-secondary text-white fs-13">{{ \Carbon\Carbon::parse($log['changed_at'])->format('d/m/Y H:i') }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <em>Không có lịch sử thay đổi</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        {{-- <td>
                            <div class="d-flex gap-2">
                                <div class="remove">
                                    <a
                                        href="{{ route('admin.instructorCommissions.edit', $instructorCommission->id) }}">
                                        <button class="btn btn-sm btn-warning edit-item-btn">
                                            <span class="ri-edit-box-line"></span>
                                        </button>
                                    </a>
                                </div>
                                <div class="edit">
                                    <a
                                        href="{{ route('admin.instructorCommissions.show', $instructorCommission->id) }}">
                                        <button class="btn btn-sm btn-info edit-item-btn">
                                            <span class="ri-eye-line"></span>
                                        </button>
                                    </a>
                                </div>
                                <div class="remove">
                                    <a href="{{ route('admin.instructorCommissions.destroy', $instructorCommission->id) }}"
                                        class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                        <span class="ri-delete-bin-7-line"></span>
                                    </a>
                                </div>

                            </div>
                        </td> --}}


                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $instructorCommissions->appends(request()->query())->links() }}
</div>