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
                        @if($withDraw->is_received === 1)
                            <button type="button" class="btn btn-info me-2" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal">
                                Kiểm tra trạng thái giao dịch
                            </button>
                        @endif

                        @if(!is_null($withDraw) && $withDraw->status)
                            @if($withDraw->status === 'Đang xử lý' || $withDraw->status === 'Chờ xử lý')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal">
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
                                    @if($withDraw->completed_date)
                                        <tr>
                                            <td>Ngày xử lý yêu cầu</td>
                                            <td>{{ $withDraw->completed_date ?? '' }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>Trạng thái</td>
                                        <td>
                                            @if($withDraw->status === 'Đang xử lý')
                                                <span class="badge bg-warning">Đang xử lý</span>
                                            @elseif($withDraw->status === 'Hoàn thành')
                                                <span class="badge bg-success">Hoàn thành</span>
                                            @elseif($withDraw->status === 'Đã từ chối')
                                                <span class="badge bg-danger">Đã từ chối</span>
                                            @elseif($withDraw->status === 'Chờ xử lý')
                                                <span class="badge bg-info">Chờ xử lý</span>
                                            @else
                                                <span class="badge bg-secondary">Không xác định</span>
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($withDraw->instructor_confirmation === 'confirmed')
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
                        @if($withDraw->admin_comment)
                            <div>
                                <textarea id="oldComment" readonly rows="3" style="resize: none"
                                          class="form-control mb-3"
                                          placeholder="Phản hồi trước đó">{{ $withDraw->admin_comment ?? '' }}</textarea>
                            </div>
                        @endif
                        <input id="withdrawal_id" type="hidden" value="{{ $withDraw->id }}">
                        <div class="mb-2">
                            <div class="d-flex justify-content-end">
                                <button type="button" id="loadOldComment" class="btn btn-sm btn-secondary mb-2">Lấy phản
                                    hồi trước đó
                                </button>
                            </div>
                            <textarea id="comment" rows="3" style="resize: none"
                                      class="form-control mb-3"
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
            $('#loadOldComment').on('click', function () {
                var oldComment = $('#oldComment').val();

                $('#comment').val(oldComment);
            });

            $('#confirmPayment').on('click', function () {
                const comment = $("#comment").val();

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
                confirmButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');

                $.ajax({
                    type: 'POST',
                    url: $('#confirmForm').attr('action'),
                    data: {
                        withdrawal_id: $('#withdrawal_id').val(),
                        admin_comment: comment,
                    },
                    success: function (response) {
                        if (response.status === true) {
                            Swal.fire({
                                title: 'Thao tác thành công!',
                                text: 'Xác nhận thanh toán thành công',
                                icon: 'success'
                            }).then(() => {
                                $('#exampleModal').modal('hide');
                                window.location.href = "{{ route('admin.withdrawals.index') }}";
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
        })
    </script>
@endpush
