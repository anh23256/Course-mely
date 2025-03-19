<div class="live-preview">
    <div class="permission-list">
        @foreach ($permissions as $guardName => $groupedPermissions)
            <div class="module-group mb-4">
                <div class="module-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="ri-shield-keyhole-line me-1"></i>
                        Module {{ Str::ucfirst($guardName) }}
                        <span
                            class="badge bg-secondary ms-2">{{ count($groupedPermissions) }}</span>
                    </div>
                    <div class="permission-badges d-none d-md-block">
                        <span class="badge badge-view">view_*</span>
                        <span class="badge badge-create">create_*</span>
                        <span class="badge badge-edit">edit_*</span>
                        <span class="badge badge-delete">delete_*</span>
                        <span class="badge badge-manage">manage_*</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover permission-table">
                        <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Tên quyền</th>
                            <th width="45%">Mô tả</th>
                            <th width="15%">Ngày tạo</th>
                            <th width="10%" class="text-center">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($groupedPermissions as $permission)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $permission->name }}
                                </td>
                                <td>{{ $permission->description }}</td>
                                <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center actions-column">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.permissions.edit', $permission) }}"
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <a href="{{ route('admin.permissions.destroy', $permission->id) }}"
                                           class="sweet-confirm btn btn-sm btn-outline-danger">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row justify-content-end mt-3">
        {{ $permissions->appends(request()->query())->links() }}
    </div>
</div>
