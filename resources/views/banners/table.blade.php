<div class="listjs-table" id="customerList">
    <div class="row g-4 mb-3">
        <div class="col-sm-auto">
            <div>
                @if (!@empty($banner_deleted_at))
                    <button class="btn btn-danger" id="restoreSelected">
                        <i class=" ri-restart-line"> Khôi phục</i>
                    </button>
                @else
                    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary add-btn"><i
                            class="ri-add-line align-bottom me-1"></i> Thêm mới</a>
                @endif
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
        <table class="table align-middle table-nowrap">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 50px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                        </div>
                    </th>
                    <th>Mã banner</th>
                    <th>Tiêu đề</th>
                    <th>Ảnh</th>
                    <th>Thứ tự</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Ngày cập nhật</th>
                    @if (!@empty($banner_deleted_at))
                        <th>Thời gian xóa</th>
                    @else
                        <th>Hành động</th>
                    @endif
                </tr>
            </thead>
            <tbody class="list form-check-all">
                @foreach ($banners as $banner)
                    <tr>
                        <th scope="row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                            </div>
                        </th>

                        <td class="id">{{ $banner->id }}</td>
                        <td class="customer_name">{{ $banner->title }}</td>
                        <td class="phone">
                            @if ($banner->image)
                                <img src="{{ $banner->image }}" alt="" width="100px">
                            @else
                                <p>Không có ảnh</p>
                            @endif

                        </td>
                        <td class="date">{{ $banner->order }}</td>
                        @if ($banner->status)
                            <td class="status"><span class="badge bg-success-subtle text-success">
                                    Hoạt động
                                </span></td>
                        @else
                            <td class="status"><span class="badge bg-danger-subtle text-danger">
                                    Không hoạt động
                                </span></td>
                        @endif
                        <td class="date">{{ $banner->created_at }}</td>
                        <td class="date">{{ $banner->updated_at }}</td>
                        @if (!@empty($banner_deleted_at))
                            <td class="date">{{ $banner->deleted_at }}</td>
                        @else
                            <td>
                                <div class="d-flex gap-2">
                                    <div class="remove">
                                        <a href="{{ route('admin.banners.edit', $banner->id) }}">
                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                <span class="ri-edit-box-line"></span>
                                            </button>
                                        </a>
                                    </div>
                                    <div class="edit">
                                        <a href="{{ route('admin.banners.show', $banner->id) }}">
                                            <button class="btn btn-sm btn-info edit-item-btn">
                                                <span class="ri-eye-line"></span>
                                            </button>
                                        </a>
                                    </div>
                                    <div class="remove">
                                        <a href="{{ route('admin.banners.destroy', $banner->id) }}"
                                            class="btn btn-sm btn-danger sweet-confirm">
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
                <p class="text-muted mb-0">We've searched more than 150+ Orders We did not find any
                    orders for you search.</p>
            </div>
        </div>
    </div>

    {{ $banners->appends(request()->query())->links() }}
</div>
