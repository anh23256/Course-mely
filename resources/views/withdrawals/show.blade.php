@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css"/>
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
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.withdrawals.index') }}">{{ $subTitle ?? '' }}</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- social-customer -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Thông tin yêu cầu rút
                            tiền: {{ $withDraw->id ?? '' }}</h4>
                        <button data-id="{{ $withDraw->id }}" type="button" class="btn btn-info me-2 check-status-btn"
                                data-bs-toggle="modal" data-bs-target="#transactionModal">
                            Kiểm tra trạng thái giao dịch
                        </button>

                        @if (!is_null($withDraw) && $withDraw->status)
                            @if ($withDraw->status === 'Đang xử lý' || $withDraw->status === 'Chờ xác nhận lại')
                                <button type="button" class="btn btn-danger reject-btn me-2" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" data-action="reject">
                                    Từ chối
                                </button>
                                <button type="button" class="btn btn-primary approve-btn" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" data-action="approve">
                                    Xác nhận
                                </button>
                            @endif
                        @endif
                    </div>
                    <div class="card-body">
                        <h5>Thông tin yêu cầu</h5>
                        <div class="live-preview">
                            <div class="table-responsive">
                                <table class="table border table-nowrap align-middle mb-0">
                                    <tbody>
                                    <tr>
                                        <td>Người gửi yêu cầu</td>
                                        <td>{{ $withDraw->wallet->user->name ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Ngân hàng</td>
                                        <td>{{ $withDraw->bank_name ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Số tài khoản</td>
                                        <td>{{ $withDraw->account_number ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Chủ tài khoản</td>
                                        <td>{{ $withDraw->account_holder ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Số tiền</td>
                                        <td>{{ number_format($withDraw->amount) ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Mã QR</td>
                                        <td>
                                            <img height="200"
                                                 src="{{ \Illuminate\Support\Facades\Storage::url($withDraw->qr_code ?? '') }}"
                                                 alt="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Ngày gửi yêu cầu</td>
                                        <td>{{ $withDraw->request_date ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Ghi chú</td>
                                        <td>{{ $withDraw->note ?? '' }}</td>
                                    </tr>
                                    @if ($withDraw->completed_date)
                                        <tr>
                                            <td>Ngày xử lý yêu cầu</td>
                                            <td>{{ $withDraw->completed_date ?? '' }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>Trạng thái</td>
                                        <td>
                                            @if ($withDraw->status === 'Đang xử lý')
                                                <span class="badge bg-warning">Đang xử lý</span>
                                            @elseif($withDraw->status === 'Hoàn thành')
                                                <span class="badge bg-success">Hoàn thành</span>
                                            @elseif($withDraw->status === 'Đã từ chối')
                                                <span class="badge bg-danger">Đã từ chối</span>
                                            @elseif($withDraw->status === 'Chờ xác nhận lại')
                                                <span class="badge bg-info">Chờ xác nhận lại</span>
                                            @else
                                                <span class="badge bg-secondary">Không xác định</span>
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if ($withDraw->instructor_confirmation === 'confirmed')
                            <div class="live-preview mt-4">
                                <h5>Giảng viên xác nhận</h5>
                                <div class="table-responsive">
                                    <table class="table border table-nowrap align-middle mb-0">
                                        <tbody>
                                        <tr>
                                            <td>Phản hồi</td>
                                            <td>{{ $withDraw->instructor_confirmation_note ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Thời gian phản hồi</td>
                                            <td>{{ $withDraw->instructor_confirmation_date ?? '' }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                        <div class="mt-2">
                            <a href="{{ route('admin.withdrawals.index') }}" class="btn  btn-primary">Danh sách</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="transactionModal" class="modal fade" tabindex="-1" aria-labelledby="transactionModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Thêm modal-lg hoặc modal-xl -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">Chi tiết trạng thái giao dịch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="transaction-details">
                        <p>Đang kiểm tra giao dịch...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="confirmForm" action="{{ route('admin.withdrawals.confirmPayment') }}" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Xác nhận thanh toán</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($withDraw->admin_comment)
                            <div>
                                <textarea id="oldComment" readonly rows="3" style="resize: none"
                                          class="form-control mb-3"
                                          placeholder="Phản hồi trước đó">{{ $withDraw->admin_comment ?? '' }}</textarea>
                            </div>
                        @endif
                        <input id="withdrawal_id" type="hidden" value="{{ $withDraw->id }}">
                        <div class="mb-2">
                            @if($withDraw->status === 'Chờ xác nhận lại')
                                <div class="d-flex justify-content-end">
                                    <button type="button" id="loadOldComment" class="btn btn-sm btn-secondary mb-2">Lấy
                                        phản
                                        hồi trước đó
                                    </button>
                                </div>
                            @endif
                            <textarea id="comment" rows="3" style="resize: none" class="form-control mb-3"
                                      placeholder="Nhập nội dung">{{ old('admin_comment', '') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                        <button id="confirmPayment" type="button" class="btn btn-primary">Xác nhận</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('page-scripts')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.check-status-btn', function () {
                var withdrawalId = $(this).data('id');

                var modal = $('#transactionModal');
                modal.modal('show');

                $('#transaction-details').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" id="loading-spinner">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p>Đang kiểm tra giao dịch...</p>
        </div>
    `);


                $.ajax({
                    url: "{{ route('admin.withdrawals.check-status') }}",
                    type: "POST",
                    data: {
                        withdrawal_id: withdrawalId,
                    },
                    success: function (response) {
                        function formatCurrency(amount) {
                            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
                        }

                        let transactionStatusBadge = "";
                        if (response.withdrawal_request.status === "Từ chối") {
                            transactionStatusBadge = `<span class="badge bg-danger">${response.withdrawal_request.status}</span>`;
                        } else if (response.withdrawal_request.status === "Hoàn thành") {
                            transactionStatusBadge = `<span class="badge bg-success">${response.withdrawal_request.status}</span>`;
                        } else if (response.withdrawal_request.status === "Đang xử lý") {
                            transactionStatusBadge = `<span class="badge bg-warning">${response.withdrawal_request.status}</span>`;
                        } else {
                            transactionStatusBadge = `<span class="badge bg-secondary">${response.withdrawal_request.status}</span>`;
                        }

                        var details = `
    <div class="card">
        <div class="card-body px-5">
            <div class="row">
                <!-- Cột 1: Yêu Cầu Rút Tiền -->
                <div class="col-md-6">
                    <h5 class="text-primary">Yêu Cầu Rút Tiền</h5>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Số tiền:</strong></div>
                        <div class="col-md-8">${formatCurrency(response.withdrawal_request?.amount)} </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Ngân hàng:</strong></div>
                        <div class="col-md-8">${response.withdrawal_request?.bank_name}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Số tài khoản:</strong></div>
                        <div class="col-md-8">${response.withdrawal_request?.account_number}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Chủ tài khoản:</strong></div>
                        <div class="col-md-8">${response.withdrawal_request?.account_holder}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Ghi chú:</strong></div>
                        <div class="col-md-8">${response.withdrawal_request?.note}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Trạng thái:</strong></div>
                   <div class="col-md-8">${transactionStatusBadge}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Ngày yêu cầu:</strong></div>
                        <div class="col-md-8">${response.withdrawal_request?.request_date}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Ngày hoàn thành:</strong></div>
                        <div class="col-md-8">${response.withdrawal_request?.completed_date}</div>
                    </div>
                </div>

                <!-- Cột 2: Giao Dịch -->
                <div class="col-md-6">
                    <h5 class="text-primary">Giao Dịch</h5>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Mã giao dịch:</strong></div>
                        <div class="col-md-8">${response.transaction?.transaction_code}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Loại giao dịch:</strong></div>
                        <div class="col-md-8">${response.transaction?.type}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Số tiền:</strong></div>
                        <div class="col-md-8">${formatCurrency(response.transaction?.amount)}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Trạng thái:</strong></div>
                        <div class="col-md-8">${transactionStatusBadge}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Ngày tạo:</strong></div>
                        <div class="col-md-8">${response.transaction?.created_at}</div>
                    </div>
                </div>
            </div>

          ${response.system_fund_transaction
                                ? `
                    <hr/>
                    <h5 class="text-primary">Chi Tiết Quỹ Hệ Thống</h5>
                    <div class="row">
                        <div class="col-md-4"><strong>Mô tả:</strong></div>
                        <div class="col-md-8">${response.system_fund_transaction?.description}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Số tiền:</strong></div>
                        <div class="col-md-8">${formatCurrency(response.system_fund_transaction?.total_amount)}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Ngày tạo:</strong></div>
                        <div class="col-md-8">${response.system_fund_transaction?.created_at}</div>
                    </div>
                    `
                                : `
                    <div class="alert alert-warning text-center mt-3">
                        Không tìm thấy chi tiết quỹ hệ thống liên quan.
                    </div>
                    `
                        }
        </div>
    </div>
`;

                        $('#transaction-details').html(details);
                    },
                    error: function (xhr, status, error) {
                        $('#transaction-details').html(
                            '<p class="text-danger">Đã xảy ra lỗi trong quá trình kiểm tra giao dịch. Vui lòng thử lại sau.</p>'
                        );
                        console.error(xhr.responseText);
                    },
                });
            });


            $('#loadOldComment').on('click', function () {
                var oldComment = $('#oldComment').val();

                $('#comment').val(oldComment);
            });

            $('#confirmPayment').on('click', function () {
                const comment = $("#comment").val();

                const action = $(this).data('action');

                if (comment.trim() === '') {
                    Swal.fire({
                        text: "Vui lòng nhập nội dung",
                        icon: 'warning'
                    });
                    return;
                }

                const confirmButton = $('#confirmPayment');
                const cancelButton = $('.btn-secondary');
                confirmButton.prop('disabled', true);
                cancelButton.prop('disabled', true);

                const originalButtonText = confirmButton.html();
                confirmButton.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...'
                );

                $.ajax({
                    type: 'POST',
                    url: $('#confirmForm').attr('action'),
                    data: {
                        withdrawal_id: $('#withdrawal_id').val(),
                        admin_comment: comment,
                        action
                    },
                    success: function (response) {
                        if (response.status === true) {
                            Swal.fire({
                                title: 'Thao tác thành công!',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                $('#exampleModal').modal('hide');
                                window.location.href =
                                    "{{ route('admin.withdrawals.index') }}";
                            });
                        } else {
                            Swal.fire({
                                title: 'Thao tác thất bại!',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function (error) {
                        Swal.fire({
                            title: 'Thao tác thất bại!',
                            text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                            icon: 'error'
                        });
                    },
                    complete: function () {
                        confirmButton.prop('disabled', false);
                        cancelButton.prop('disabled', false);
                        confirmButton.html(originalButtonText);
                    }
                })
            })

            $(document).on('click', '.approve-btn, .reject-btn', function () {
                const action = $(this).data('action');
                const modalTitle = action === 'approve' ? 'Xác nhận thanh toán' : 'Từ chối yêu cầu'; // Set modal title dynamically
                const confirmButtonText = action === 'approve' ? 'Xác nhận' : 'Từ chối';

                $('#exampleModalLabel').text(modalTitle);
                $('#confirmPayment').text(confirmButtonText);
                $('#confirmPayment').data('action', action);
            });
        })
    </script>
@endpush
