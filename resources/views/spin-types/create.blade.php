@extends('layouts.app')

@section('title', 'Thêm loại phần thưởng')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4>Thêm loại phần thưởng</h4>
                    <a href="{{ route('admin.spin-types.index') }}" class="btn btn-secondary mt-2">Quay lại</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.spin-types.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên (Key)</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="display_name" class="form-label">Tên hiển thị</label>
                                <input type="text" class="form-control" id="display_name" name="display_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Thêm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection