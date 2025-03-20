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

        <div class="table-responsive table-card mt-3 mb-1">
            <table class="table align-middle table-nowrap" id="customerTable">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 50px;">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th>STT</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Khóa học</th>
                        <th>Tổng tiền đã chi</th>
                        <th>Số lần đăng ký membership</th>
                        <th>Ngày đăng ký membership gần nhất</th>

                    </tr>
                </thead>
                <tbody class="list">
                    @foreach ($memberships as $user)
                        @foreach ($user->invoices as $invoice)
                            <tr>
                                <td>
                                    <input type="checkbox" class="checkItem" value="">
                                </td>
                                <td class="id">{{ $loop->index + 1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $invoice->course->name }}</td>
                                <td>{{ number_format($invoice->total_spent, 0, ',', '.') }} VNĐ</td>
                                <td>{{ $invoice->total_registrations }} lần</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->latest_membership)->format('d/m/Y') }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <div class="row justify-content-end">
                {{ $memberships->appends(request()->query())->links() }}
            </div>
        </div>

    </div>
</div>