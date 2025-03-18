<script>
    var PATH_ROOT = "{{ env('APP_URL') }}";
</script>

<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('assets/js/plugins.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerIcon = document.getElementById('topnav-hamburger-icon');
        const customTextLogo = document.getElementById('custom-text-logo');

        hamburgerIcon.addEventListener('click', function() {
            if (customTextLogo.style.display === 'none') {
                customTextLogo.style.display = 'inline';
            } else {
                customTextLogo.style.display = 'none';
            }
        });
    });
</script>


<!-- App js -->
<script src="{{ asset('assets/js/app.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- JS for Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script !src="">
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(".sweet-confirm").click(function(event) {
            event.preventDefault();

            let deleteUrl = $(this).attr("href");

            Swal.fire({
                title: "Bạn có muốn xóa ?",
                text: "Bạn sẽ không thể khôi phục dữ liệu khi xoá!!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Đồng ý!!",
                cancelButtonText: "Huỷ!!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: deleteUrl,
                        success: function(data) {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Thao tác thành công!',
                                    text: data.message,
                                    icon: 'success'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            } else if (data.status === 'error') {
                                Swal.fire({
                                    title: "Thao tác thất bại!",
                                    text: data.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(data) {
                            console.log('Error:', data);
                            Swal.fire({
                                title: "Thao tác thất bại!",
                                text: data.responseJSON.message,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
<script>
    function showToast(type, message) {
        switch (type) {
            case 'success':
                toastr.success(message, {
                    positionClass: "toast-top-right",
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000
                });
                break;
            case 'error':
                toastr.error(message, {
                    positionClass: "toast-top-right",
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000
                });
                break;
            case 'warning':
                toastr.warning(message, {
                    positionClass: "toast-top-right",
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000
                });
                break;
        }
    }
    @isset($errors)
        @if (session('success'))
            showToast('success', "{{ session('success') }}");
        @elseif (session('error'))
            showToast('error', "{{ session('error') }}");
        @elseif ($errors->any())
            @foreach ($errors->all() as $warning)
                showToast('warning', "{{ $warning }}");
            @endforeach
        @endif
    @endisset

    function updateUserStatus(status) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "http://127.0.0.1:8000/admin/check-status-user",
            method: "POST",
            data: {
                status: status
            },
            success: function(response) {
                console.log("User status updated:", response.status);
            }
        });
    }
    document.addEventListener("visibilitychange", function() {
        if (document.hidden) {
            console.log("User đã chuyển tab, có thể giảm tần suất cập nhật.");
        } else {
            console.log("User quay lại tab, cập nhật trạng thái online.");
            updateUserStatus("online");
        }
    });


    updateUserStatus("online");

    setInterval(() => updateUserStatus("online"), 120000);

    $(window).on("beforeunload", function() {
        updateUserStatus("offline");
    });
</script>
