<div class="listjs-table">
    <div class="row g-4 mb-3">
        <div class="col-sm-auto">
            <div>
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary add-btn"><i
                        class="ri-add-line align-bottom me-1"></i> Thêm mới</a>
                <button class="btn btn-danger" onClick="deleteMultiple()"><i class="ri-delete-bin-2-line"> Xóa
                        nhiều</i></button>
            </div>
        </div>
        <div class="col-sm">
            <div class="d-flex justify-content-sm-end">
                <div class="search-box ms-2">
                    <form action="{{ route('admin.coupons.index') }}" method="get">
                        <input type="text" name="query" class="form-control search" placeholder="Search..."
                            value="{{ old('query') }}">
                        <i class="ri-search-line search-icon"></i>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="itemList">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 50px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                        </div>
                    </th>
                    <th>ID</th>
                    <th>Người tạo</th>
                    <th>Chương trình</th>
                    <th>Mã giảm giá</th>
                    <th>Giảm giá</th>
                    <th>Trạng Thái</th>
                    @if (empty($coupon_deleted_at))
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                    @endif
                    <th>Số lượng sử dụng</th>
                    @if (!empty($coupon_deleted_at))
                        <th>Thời gian xóa</th>
                    @else
                        <th>Hành động</th>
                    @endif
                </tr>
            </thead>
            <tbody class="list form-check-all">
                @foreach ($coupons as $coupon)
                    <tr>
                        <th scope="row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                            </div>
                        </th>

                        <td class="id">{{ $coupon->id }}</td>
                        <td class="id">{{ $coupon->user_id }}</td>
                        <td class="customer_name">{{ $coupon->name }}</td>
                        <td class="date">{{ $coupon->code }}</td>
                        <td class="date">{{ number_format($coupon->discount_value) }}
                            {{ $coupon->discount_type == 'fixed' ? 'VND' : '%' }}
                        </td>
                        @if ($coupon->status)
                            <td class="status"><span class="badge bg-success text-uppercase">
                                    Active
                                </span></td>
                        @else
                            <td class="status"><span class="badge bg-danger text-uppercase">
                                    InActive
                                </span></td>
                        @endif
                        @if (empty($coupon_deleted_at) && !$coupon->deleted_at)
                        <td class="date">{{ $coupon->start_date ? \Carbon\Carbon::parse($coupon->start_date)->format('d/m/Y') : '' }}</td>
                        <td class="date">{{ $coupon->expire_date ? \Carbon\Carbon::parse($coupon->expire_date)->format('d/m/Y') : '' }}</td>
                        @endif
                        <td class="date">{{ $coupon->max_usage }}</td>
                        @if (!empty($coupon_deleted_at) && $coupon->deleted_at)
                            <td class="date">{{ $coupon->deleted_at }}</td>
                        @else
                            <td>
                                <div class="d-flex gap-2">
                                    <div class="remove">
                                        <a href="{{ route('admin.coupons.edit', $coupon->id) }}">
                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                <span class="ri-edit-box-line"></span>
                                            </button>
                                        </a>
                                    </div>
                                    <div class="edit">
                                        <a href="{{ route('admin.coupons.show', $coupon->id) }}">
                                            <button class="btn btn-sm btn-info edit-item-btn">
                                                <span class="ri-eye-line"></span>
                                            </button>
                                        </a>
                                    </div>
                                    <div class="remove">
                                        <a href="{{ route('admin.coupons.destroy', $coupon->id) }}"
                                            class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                            <span class="ri-delete-bin-7-line"></span>
                                        </a>
                                    </div>

                                </div>
                            </td>
                        @endif


                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="noresult" style="display: none">
            <div class="text-center">
                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                <h5 class="mt-2">Sorry! No Result Found</h5>
                <p class="text-muted mb-0">We've searched more than 150+ Orders We did not find
                    any
                    orders for you search.</p>
            </div>
        </div>
    </div>

    {{ $coupons->appends(request()->query())->links() }}
</div>
