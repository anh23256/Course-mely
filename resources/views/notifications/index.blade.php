@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>

                            <li class="breadcrumb-item active"><a href="">{{ $subTitle ?? '' }}</a></li>

                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- List-customer -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                    <!-- end card header -->
                    <div class="card-body" id="item_List">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <a href="{{ route('admin.categories.create') }}">
                                            <button type="button" class="btn btn-primary add-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Thêm mới
                                            </button>
                                        </a>
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
                                <table class="table align-middle table-nowrap" id="notificationTable">
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
                                    <tbody class="list">
                                        @foreach($notifications as $notification)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="checkItem" value="{{ $notification->id }}">
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if(isset($notification->data['type']))
                                                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $notification->data['type'])) }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">Không xác định</span>
                                                    @endif
                                                </td>
                                                <td>{{ $notification->data['message'] ?? 'Không có nội dung' }}</td>
                                                <td>
                                                    @if(is_null($notification->read_at))
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

                            <div class="row justify-content-end">
                                {{ $notifications->appends(request()->query())->links() }}
                            </div>

                        </div>
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <!-- end col -->
        </div>

        <!-- end row -->
    </div>

    <!-- end List-customer -->
    </div>
@endsection
@push('page-scripts')
    
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>

    
@endpush
