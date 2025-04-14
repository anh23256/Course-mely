@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .stat-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            height: 150px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .card-title-custom {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .action-buttons .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0;
        }

        .coupon-table th {
            background-color: #f5f7fa;
        }
    </style>
@endpush
@php
    $title = 'Danh sách coupon';
@endphp
@section('content')
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $subTitle }}</h4>
                        <div class="d-flex gap-2">
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-filter-2-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown"
                                    style="min-width: 500px;">
                                    <div class="container">
                                        <div class="container">
                                            <div class="row">
                                               
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="startDate" class="form-label">Ngày bắt đầu</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="start_date" id="startDate" data-filter
                                                            value="{{ request()->input('start_date') ?? '' }}">
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Ngày kết thúc</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="expire_date" id="endDate" data-filter
                                                            value="{{ request()->input('expire_date') ?? '' }}">
                                                    </div>
                                                </li>
                                            </div>
                                            <li class="mt-2">
                                                <button class="btn btn-sm btn-primary w-100" id="applyFilter">Áp
                                                    dụng</button>
                                            </li>

                                        </div>
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Tìm kiếm nâng cao -->

                    <div class="card-body" id="item_List">
                        <div class="listjs-table">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">

                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <form action="{{ route('admin.instructor-commissions.index') }}"
                                                method="get">
                                                <input type="text" name="search_full" class="form-control search"
                                                    placeholder="Search..." value="{{ old('search_full') }}">
                                                <i class="ri-search-line search-icon"></i>
                                            </form>
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
                                                    <input class="form-check-input" type="checkbox" id="checkAll"
                                                        value="option">
                                                </div>
                                            </th>
                                            <th>STT</th>
                                            <th>Giảng viên</th>
                                            <th>Hoa hồng hiện tại (%)</th>
                                            <th>Lịch sử thay đổi</th>
                                            <th>Cập nhật lúc</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
                                        @foreach ($instructorCommissions as $instructorCommission)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="itemID"
                                                            value="{{ $instructorCommission->id }}">
                                                    </div>
                                                </th>

                                                <td class="id">{{ $loop->iteration }}</td>

                                                <td>{{ $instructorCommission->instructor->name ?? 'Không tìm thấy' }}</td>
                                                <td>{{ number_format($instructorCommission->rate, 2) }}%</td>
                                                <td>
                                                    @php
                                                        $logs = json_decode($instructorCommission->rate_logs, true);
                                                    @endphp

                                                    @if ($logs)
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach ($logs as $log)
                                                                <li class="mb-2">
                                                                    <span
                                                                        class="fw-bold">{{ number_format($log['rate'], 2) }}%</span>
                                                                    <span class="badge bg-light text-dark">
                                                                        {{ \Carbon\Carbon::parse($log['changed_at'])->format('d/m/Y H:i') }}
                                                                    </span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <em>Không có dữ liệu</em>
                                                    @endif
                                                </td>
                                                <td>{{ $instructorCommission->updated_at->format('d/m/Y H:i') }}</td>
                                                {{-- <td>
                                                    <div class="d-flex gap-2">
                                                        <div class="remove">
                                                            <a
                                                                href="{{ route('admin.instructorCommissions.edit', $instructorCommission->id) }}">
                                                                <button class="btn btn-sm btn-warning edit-item-btn">
                                                                    <span class="ri-edit-box-line"></span>
                                                                </button>
                                                            </a>
                                                        </div>
                                                        <div class="edit">
                                                            <a
                                                                href="{{ route('admin.instructorCommissions.show', $instructorCommission->id) }}">
                                                                <button class="btn btn-sm btn-info edit-item-btn">
                                                                    <span class="ri-eye-line"></span>
                                                                </button>
                                                            </a>
                                                        </div>
                                                        <div class="remove">
                                                            <a href="{{ route('admin.instructorCommissions.destroy', $instructorCommission->id) }}"
                                                                class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                                <span class="ri-delete-bin-7-line"></span>
                                                            </a>
                                                        </div>

                                                    </div>
                                                </td> --}}


                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $instructorCommissions->appends(request()->query())->links() }}
                        </div>
                    </div><!-- end card -->
                </div>
                <!-- end col -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->

    </div>
@endsection
@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.instructor-commissions.index') }}";
        var routeDeleteAll = "{{ route('admin.coupons.destroy', ':itemID') }}";

        function updateRange() {
            let rangeValue = document.getElementById("amountMinRange").value;
            document.getElementById("amountMin").textContent = rangeValue;
        }

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>

    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/restore-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/resetFilter.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
