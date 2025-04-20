@extends('layouts.app')

@section('title', 'Quản lý loại phần thưởng')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Danh sách loại phần thưởng</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Danh sách loại phần thưởng</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Danh sách loại phần thưởng</h4>
                        <a href="{{ route('admin.spin-types.create') }}" class="btn btn-primary">Thêm mới</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên (Key)</th>
                                        <th>Tên hiển thị</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($spinTypes as $spinType)
                                        <tr>
                                            <td>{{ $spinType->id }}</td>
                                            <td>{{ $spinType->name }}</td>
                                            <td>{{ $spinType->display_name }}</td>
                                            <td>
                                                <a href="{{ route('admin.spin-types.edit', $spinType->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                                                <form action="{{ route('admin.spin-types.destroy', $spinType->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection