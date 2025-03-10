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
                    <th>STT</th>
                    <th>Người dùng</th>
                    <th>Bình luận gốc</th>
                    <th>Vị trí bình luận</th>
                    <th>Bình luận trả lời</th>
                    <th>Thời gian bình luận</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody class="list form-check-all">
                @foreach ($comments as $comment)
                    <tr>
                        <th scope="row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="itemID"
                                    value="{{ $comment->id }}">
                            </div>
                        </th>
                        <td class="phone">
                            {{ $comment->user->name }}
                        </td>
                        <td class="id">{{ $comment->content }}</td>

                        <td class="id">{{ $comment->commentable->title ?? 'Nội dung khác' }}
                        </td>

                        <td>
                            <button class="btn btn-sm btn-danger"
                                onclick="loadReplies({{ $comment->id }}, '{{ addslashes($comment->content) }}')"
                                data-bs-toggle="modal" data-bs-target="#replyModal"> Xem bình luận
                            </button>
                        </td>

                        <td>{{ $comment->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        {{ $comments->appends(request()->query())->links() }}
    </div>
</div>
