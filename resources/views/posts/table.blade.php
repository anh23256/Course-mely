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
                    <input type="text" name="search_full" id="searchFull" class="form-control search"
                        placeholder="Tìm kiếm..." data-search value="{{ request()->input('search_full') ?? '' }}">
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
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Ảnh bìa</th>
                    <th>Tác giả</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    @if (!empty($post_deleted_at))
                        <th>Thời gian xóa</th>
                    @else
                        <th>Ngày đăng tải</th>
                        <th>Hành Động</th>
                    @endif
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($posts as $post)
                    <tr>
                        <th scope="row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="itemID"
                                    value="{{ $post->id }}">
                            </div>
                        </th>

                        <td class="customer_name">{{ $post->id }}</td>
                        <td class="email">{{ $post->title }}</td>
                        <td>
                            <img class="img-thumbnail" src="{{ $post->thumbnail ?? '' }}" alt="Hình đại diện" width="100">
                        </td>
                        <td class="text-danger fw-bold">{{ $post->user->name ?? ''}}</td>
                        <td>{{ $post->category->name ?? '' }}</td>
                        <td class="status col-1">
                            @if ($post->status === 'published')
                                <span class="badge bg-success w-75">
                                    Xuất bản
                                </span>
                            @elseif($post->status === 'pending')
                                <span class="badge bg-warning w-75">
                                    Chờ xử lí
                                </span>
                            @elseif($post->status === 'draft')
                                <span class="badge bg-secondary w-75">
                                    Bản nháp
                                </span>
                            @else
                                <span class="badge bg-danger w-75">
                                    Riêng tư
                                </span>
                            @endif
                        </td>
                        @if (!empty($post_deleted_at))
                            <td>
                                {{ $post->deleted_at ? \Carbon\Carbon::parse($post->deleted_at)->format('d/m/Y') : '' }}
                            </td>
                        @else
                            <td>
                                {!! $post->published_at
                                    ? \Carbon\Carbon::parse($post->published_at)->format('d/m/Y')
                                    : '<span class="btn btn-sm btn-soft-warning">Chưa đăng</span>' !!}
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.posts.edit', $post->id) }}">
                                        <button class="btn btn-sm btn-warning edit-item-btn">
                                            <span class="ri-edit-box-line"></span>
                                        </button>
                                    </a>
                                    <a href="{{ route('admin.posts.show', $post->id) }}">
                                        <button class="btn btn-sm btn-info edit-item-btn">
                                            <span class="ri-eye-line"></span>
                                        </button>
                                    </a>
                                    <a href="{{ route('admin.posts.destroy', $post->id) }}"
                                        class="btn btn-sm btn-danger sweet-confirm">
                                        <span class="ri-delete-bin-7-line"></span>
                                    </a>
                                    </a>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        {{ $posts->appends(request()->query())->links() }}
    </div>
</div>
