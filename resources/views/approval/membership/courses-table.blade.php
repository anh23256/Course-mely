<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 40%">Khoá học</th>
                <th style="width: 10%">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courses as $key => $courseAccess)
                <tr>
                    <td class="text-center">{{ ($courses->currentPage() - 1) * $courses->perPage() + $key + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $courseAccess->thumbnail }}" alt="Thumbnail" class="rounded" width="50" height="50" style="object-fit: cover;">
                            <span class="fw-semibold">{{ $courseAccess->name }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.courses.show', $courseAccess->id) }}" 
                           class="btn btn-sm btn-primary" target="_blank" title="Xem chi tiết">
                            <i class="ri-eye-line"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Không có khóa học nào</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-center mt-3">
        {{ $courses->links() }}
    </div>
</div>