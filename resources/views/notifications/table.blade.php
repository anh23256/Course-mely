<div class="card-body" id="item_List">
    <div class="listjs-table" id="customerList">
        <div class="row g-4 mb-3">
            <div class="col-sm-auto">
                <div>
                    <button class="btn btn-danger" id="deleteSelected">
                        <i class="ri-delete-bin-2-line"> Xóa nhiều</i>
                    </button>
                </div>
            </div>
            <div class="col-sm">
                <div class="d-flex justify-content-sm-end">
                    <div class="search-box ms-2">
                        <input type="text" name="search_full" id="searchFull"
                            class="form-control search" placeholder="Tìm kiếm..." data-search
                            value="{{ request()->input('search_full') ?? '' }}">
                        <button id="search-full" class="ri-search-line search-icon m-0 p-0 border-0"
                            style="background: none;"></button>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{ request('status', 'all') === 'all' ? 'active' : '' }}"
                    href="{{ route('admin.notifications.show', ['status' => 'all']) }}">
                    Tất cả
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'unread' ? 'active' : '' }}"
                    href="{{ route('admin.notifications.show', ['status' => 'unread']) }}">
                    Chưa đọc
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') === 'read' ? 'active' : '' }}"
                    href="{{ route('admin.notifications.show', ['status' => 'read']) }}">
                    Đã đọc
                </a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Tab Tất cả -->
            <div class="tab-pane fade show active" id="all">
                <div class="table-responsive table-card">
                    <table class="table align-middle table-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>STT</th>
                                <th>Loại thông báo</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notifications as $key => $notification)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="checkItem" name="itemID"
                                            value="{{ $notification->id }}">
                                    </td>
                                    <td>{{ $notifications->firstItem() + $key }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ ucfirst(str_replace('_', ' ', $notification->data['type'] ?? 'Không xác định')) }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->data['message'] ?? 'Không có nội dung' }}
                                    </td>
                                    <td>
                                        @if (is_null($notification->read_at))
                                            <span class="badge bg-danger">Chưa đọc</span>
                                        @else
                                            <span class="badge bg-success">Đã đọc</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Hiển thị phân trang -->
                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>

            </div>


        </div>

    </div>
</div>