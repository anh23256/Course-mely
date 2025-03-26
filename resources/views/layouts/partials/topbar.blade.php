@vite('resources/js/app.js')

@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css"/>
@endpush

<div class="layout-width">
    <div class="navbar-header">
        <div class="d-flex">
            <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon">
                <span class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-md-block">
                <div class="position-relative">
                    <input type="text" class="form-control" placeholder="Search..." autocomplete="off"
                           id="search-options" value="">
                    <span class="mdi mdi-magnify search-widget-icon"></span>
                    <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                          id="search-close-options"></span>
                </div>
                <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
                    <div data-simplebar style="max-height: 320px;">
                        <!-- item-->
                        <div class="dropdown-header">
                            <h6 class="text-overflow text-muted mb-0 text-uppercase">Recent Searches</h6>
                        </div>

                        <div class="dropdown-item bg-transparent text-wrap">
                            <a href="index.html" class="btn btn-soft-secondary btn-sm rounded-pill">how to
                                setup <i class="mdi mdi-magnify ms-1"></i></a>
                            <a href="index.html" class="btn btn-soft-secondary btn-sm rounded-pill">buttons
                                <i class="mdi mdi-magnify ms-1"></i></a>
                        </div>
                        <!-- item-->
                        <div class="dropdown-header mt-2">
                            <h6 class="text-overflow text-muted mb-1 text-uppercase">Pages</h6>
                        </div>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <i class="ri-bubble-chart-line align-middle fs-18 text-muted me-2"></i>
                            <span>Analytics Dashboard</span>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <i class="ri-lifebuoy-line align-middle fs-18 text-muted me-2"></i>
                            <span>Help Center</span>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <i class="ri-user-settings-line align-middle fs-18 text-muted me-2"></i>
                            <span>My account settings</span>
                        </a>

                        <!-- item-->
                        <div class="dropdown-header mt-2">
                            <h6 class="text-overflow text-muted mb-2 text-uppercase">Members</h6>
                        </div>

                        <div class="notification-list">
                            <!-- item -->
                            <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                <div class="d-flex">
                                    <img src="{{ asset('assets/images/users/avatar-2.jpg') }}"
                                         class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                    <div class="flex-grow-1">
                                        <h6 class="m-0">Angela Bernier</h6>
                                        <span class="fs-11 mb-0 text-muted">Manager</span>
                                    </div>
                                </div>
                            </a>
                            <!-- item -->
                            <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                <div class="d-flex">
                                    <img src="{{ asset('assets/images/users/avatar-3.jpg') }}"
                                         class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                    <div class="flex-grow-1">
                                        <h6 class="m-0">David Grasso</h6>
                                        <span class="fs-11 mb-0 text-muted">Web Designer</span>
                                    </div>
                                </div>
                            </a>
                            <!-- item -->
                            <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                <div class="d-flex">
                                    <img src="{{ asset('assets/images/users/avatar-5.jpg') }}"
                                         class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                    <div class="flex-grow-1">
                                        <h6 class="m-0">Mike Bunch</h6>
                                        <span class="fs-11 mb-0 text-muted">React Developer</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="text-center pt-3 pb-1">
                        <a href="pages-search-results.html" class="btn btn-primary btn-sm">View All
                            Results <i class="ri-arrow-right-line ms-1"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="d-flex align-items-center">

            <div class="dropdown d-md-none topbar-head-dropdown header-item">
                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="bx bx-search fs-22"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                     aria-labelledby="page-header-search-dropdown">
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ..."
                                       aria-label="Recipient's username">
                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="ms-1 header-item d-none d-sm-flex">
                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                    <i class='bx bx-fullscreen fs-22'></i>
                </button>
            </div>

            <div class="ms-1 header-item d-none d-sm-flex">
                <button type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                    <i class='bx bx-moon fs-22'></i>
                </button>
            </div>

            <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-haspopup="true" aria-expanded="false">
                    <i class='bx bx-bell fs-22'></i>
                    <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger"
                          id="unread-notification-count"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                     aria-labelledby="page-header-notifications-dropdown">

                    <div class="dropdown-head bg-primary bg-pattern rounded-top">
                        <div class="p-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-16 fw-semibold text-white"> Tin tức </h6>
                                </div>
                                <div class="col-auto dropdown-tabs">
                                    <span id="unread-notification-count"
                                          class="badge bg-light-subtle text-body fs-13"></span>
                                </div>
                            </div>
                        </div>

                        <div class="px-2 pt-2">
                            <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom" data-dropdown-tabs="true"
                                id="notificationItemsTab" role="tablist">
                                <li class="nav-item waves-effect waves-light">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#all-noti-tab"
                                       role="tab" aria-selected="true">
                                        Thông báo
                                    </a>
                                </li>
                                <li class="nav-item waves-effect waves-light">
                                    <a class="nav-link" data-bs-toggle="tab" href="#alerts-tab" role="tab"
                                       aria-selected="false">
                                        Kiểm duyệt
                                    </a>
                                </li>
                                <li class="nav-item waves-effect waves-light">
                                    <a class="nav-link" data-bs-toggle="tab" href="#messages-tab" role="tab"
                                       aria-selected="false">
                                        Tin nhắn
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>

                    <div class="tab-content position-relative" id="notificationItemsTabContent">
                        <div class="tab-pane fade show active py-2 ps-2" id="all-noti-tab" role="tabpanel">
                            <div data-simplebar style="max-height: 300px;" class="pe-2" id="notification-data">
                            </div>
                        </div>

                        <div class="tab-pane fade py-2 ps-2" id="alerts-tab" role="tabpanel"
                             aria-labelledby="alerts-tab">
                            <div data-simplebar style="max-height: 300px; overflow-y: auto;" class="pe-2"
                                 id="approvals-notifications-container">
                            </div>
                        </div>

                        <div class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel"
                             aria-labelledby="messages-tab">
                            <div data-simplebar style="max-height: 300px;" class="pe-2"
                                 id="messages-notifications-container">
                            </div>
                        </div>

                        <div class="notification-actions" id="notification-actions">
                            <div class="d-flex text-muted justify-content-center">
                                Select
                                <div id="select-content" class="text-body fw-semibold px-1">0</div>
                                Result
                                <button type="button" class="btn btn-link link-danger p-0 ms-3"
                                        data-bs-toggle="modal" data-bs-target="#removeNotificationModal">Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dropdown ms-sm-3 header-item topbar-user">
                <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <span class="d-flex align-items-center">
                        <img class="rounded-circle header-profile-user" src="{{ Auth::user()->avatar ?? '' }}"
                             alt="Header Avatar" crossorigin="anonymous">
                        <span class="text-start ms-xl-2">
                            <span
                                class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ Auth::user()->name ?? '' }}</span>
                            <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">
                                {{ Auth::check() && Auth::user()->roles->count() > 0 ? Str::ucfirst(Auth::user()->roles->first()->name) : '' }}
                            </span>
                        </span>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <h6 class="dropdown-header">Xin chào {{ Auth::user()->name ?? '' }}!</h6>
                    <a class="dropdown-item" href="{{ route('admin.administrator.profile') }}"><i
                            class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                            class="align-middle">Thông cá cá nhân</span></a>
                    <a class="dropdown-item" href="apps-chat.html"><i
                            class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i>
                        <span class="align-middle">Messages</span></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('admin.wallets.index') }}"><i
                            class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span
                            class="align-middle">Balance :
                            <b>{{ number_format(Auth::user()->wallet->balance ?? 0) }} VND</b></span></a>
                    <a class="dropdown-item" href="pages-profile-settings.html"><span
                            class="badge bg-success-subtle text-success mt-1 float-end">New</span><i
                            class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span
                            class="align-middle">Settings</span></a>
                    <a class="dropdown-item" href="{{ route('admin.logout') }}"><i
                            class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                        <span class="align-middle" data-key="t-logout">Đăng xuất</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/locale/vi.js"></script>
    <script>
        moment.locale('vi');

        $(document).ready(function () {
            $('.dropdown-item[href="{{ route('admin.logout') }}"]').on('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Xác nhận đăng xuất',
                    text: 'Bạn có chắc chắn muốn đăng xuất?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Đăng xuất',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('admin.logout') }}";
                    }
                });
            });


            Echo.connector.pusher.connection.bind('connected', function () {
                console.log('Pusher đã kết nối thành công');
            });

            function triggerBellAnimation() {
                const $bellIcon = $('#page-header-notifications-dropdown i');

                $bellIcon.addClass('shake-animation');

                setTimeout(() => {
                    $bellIcon.removeClass('shake-animation');
                }, 500);
            }

            const userID = `{{ Auth::check() ? Auth::user()->id : '' }}`;
            if (!userID) {
                console.log('Người dùng chưa đăng nhập.');
                return;
            }

            function fetchNotifications(data) {

                $.ajax({
                    url: '/admin/notifications',
                    type: 'GET',
                    ...(data && Object.keys(data).length > 0 ? {
                        data: data
                    } : {}),
                    success: function (response) {
                        if (response?.data) {
                            const {
                                notifications,
                                unread_notifications_count
                            } = response.data;

                            $('#messages-notifications-container').empty();
                            $('#notification-data').empty();
                            $('#approvals-notifications-container').empty();

                            $.each(notifications, function (_, notification) {
                                renderNotification(notification, false);
                            });

                            updateUnreadCount(unread_notifications_count);

                            if (response.data && response.data.notifications.length > 0) {
                                $('#notification-data').append(`
                                        <div class="col-12 col-md-12">
                                            <div class="d-flex mt-4 justify-content-center">
                                                <a class="text-primary text-decoration-underline" data-notification-type="user_buy_course" id="load-more">Xem thêm</a>
                                            </div>
                                        </div>
                                    `);
                                $('#approvals-notifications-container').append(`
                                        <div class="col-12 col-md-12">
                                            <div class="d-flex mt-4 justify-content-center">
                                                <a class="text-primary text-decoration-underline"
                                                    data-notification-type="register_course-register_instructor" id="load-more">
                                                    Xem thêm
                                                </a>
                                            </div>
                                        </div>
                                    `);
                                $('#messages-notifications-container').append(`
                                        <div class="col-12 col-md-12">
                                            <div class="d-flex mt-4 justify-content-center">
                                                <a class="text-primary text-decoration-underline" data-notification-type="receive_message" id="load-more">Xem thêm</a>
                                            </div>
                                        </div>
                                    `);
                            }
                        }
                    },
                    error: function (error) {
                        console.error('Có lỗi xảy ra khi tải thông báo:', error);
                    }
                });
            }

            function updateUnreadCount(count) {
                const $countElement = $('#unread-notification-count');
                if (count > 0) {
                    if(count > 99){
                        $countElement.text('99+').show();
                    }else{
                        $countElement.text(count).show();
                    }
                } else {
                    $countElement.hide();
                }
            }

            function renderNotification(notification, isRealTime) {
                if (!notification?.data) return;
                const {
                    type,
                    message,
                    url
                } = notification.data;
                let title = 'Thông báo mới';
                let thumbnail = '';

                if (type === 'user_buy_course') {
                    title = 'Mua khoá học';
                    thumbnail = notification.data.user_avatar ||
                        'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';
                }

                if (type === 'withdrawal') {
                    title = 'Yêu cầu rút tiền';
                    thumbnail = notification.data.instructor_avatar ||
                        'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';
                }
                if (type === 'receive_message') {
                    title = 'Tin nhắn';
                    thumbnail = notification.data.message_user_avatar ||
                        'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';
                }
                if (type === 'register_course') {
                    title = notification.data.course_name || 'Khóa học';
                    thumbnail = notification.data.course_thumbnail;
                } else if (type === 'register_instructor') {
                    title = notification.data.user_name || 'Giảng viên';
                    thumbnail =
                        'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';
                } else if (type === 'post_submitted') {
                    title = notification.data.user_name || 'Giảng viên';
                    thumbnail =
                        'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';
                }

                const isChecked = notification.read_at ? 'checked' : '';
                const timeFormatted = notification?.created_at ? moment(notification?.created_at).fromNow() :
                    'Không xác định';

                const notificationItem = `
        <div id="notification-${notification.id}" class="text-reset notification-item d-block dropdown-item notification-check" data-notification-id=${notification.id} data-isChecked=${isChecked}>
            <div class="d-flex">
                <img src="${thumbnail}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                <div class="flex-grow-1">
                    <a href="${url || '#'}" class="stretched-link-${notification.id}">
                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">${title}</h6>
                    </a>
                    <div class="fs-13 text-muted">
                        <p class="mb-1">${message || ''}</p>
                    </div>
                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                        <span><i class="mdi mdi-clock-outline"></i> ${timeFormatted}</span>
                    </p>
                </div>
            </div>
        </div>
    `;

                if (type === 'register_course' || type === 'register_instructor' || type === 'withdrawal' || type === 'post_submitted') {
                    if (isRealTime) {
                        $('#approvals-notifications-container').prepend(notificationItem);
                    } else {
                        $('#approvals-notifications-container').append(notificationItem);
                    }
                } else if (type === 'user_buy_course') {
                    if (isRealTime) {
                        $('#notification-data').prepend(notificationItem);
                    } else {
                        $('#notification-data').append(notificationItem);
                    }
                } else if (type === "receive_message") {
                    if (isRealTime) {
                        $('#messages-notifications-container').prepend(notificationItem);
                    } else {
                        $('#messages-notifications-container').append(notificationItem);
                    }
                }

                if (isChecked) {
                    $(`#notification-${notification.id}`).css('background-color',
                        '#f5f5f5');
                } else {
                    $(`#notification-${notification.id}`).css('background-color', '');
                }
            }

            $(document).on('click', '.notification-check', function (e) {
                e.preventDefault();
                const notificationId = $(this).data('notification-id');
                const isChecked = $(this).data('ischecked');

                const readed = "{{ now() }}";
                const urlRedirect = $(`.stretched-link-${notificationId}`).attr('href');

                if (urlRedirect == "#") {
                    return;
                }

                if (isChecked || !notificationId) {
                    window.location.href = urlRedirect;
                    return;
                }
                const url = `/admin/notifications/${notificationId}`;

                const data = {
                    read_at: readed
                };

                $.ajax({
                    url: url,
                    type: 'PUT',
                    data: data,
                    success: function (response) {
                        window.location.href = urlRedirect;
                    },
                    error: function (error) {
                        console.error('Có lỗi xảy ra khi cập nhật trạng thái đọc:', error);
                    }
                });
            });

            let countNotificationsData = {
                approval: {
                    type: ["register_course", "register_instructor", "withdrawal"],
                    count: 10
                },
                message: {
                    type: ["receive_message"],
                    count: 10
                },
                buycourse: {
                    type: ["user_buy_course"],
                    count: 10
                }
            };

            $(document).on('click', '#load-more', function () {
                const notificationType = $(this).data('notification-type');

                if (notificationType === "user_buy_course") {
                    countNotificationsData.buycourse.count += 5;
                } else if (notificationType === "receive_message") {
                    countNotificationsData.message.count += 5;
                } else {
                    countNotificationsData.approval.count += 5;
                }

                fetchNotifications({
                    count_notifications: countNotificationsData
                });
            });

            window.Echo.private(`App.Models.User.${userID}`)
                .notification((notification) => {
                    console.log('Thông báo mới:', notification);
                    triggerBellAnimation()

                    Toastify({
                        text: `🔔 ${notification.message}`,
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "#2196F3",
                            color: "#fff",
                            borderRadius: "5px",
                            padding: "10px",
                        }
                    }).showToast();

                    renderNotification(notification, true);
                    fetchNotifications();
                });

            fetchNotifications();
        });
    </script>
@endpush
