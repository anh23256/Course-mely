@vite(['resources/js/app.js'])
@extends('layouts.app')
@push('page-css')
    <!-- glightbox css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/glightbox/css/glightbox.min.css') }}">
    <style>
        .file-input {
            display: none;
        }
    </style>
@endpush
@php
    $title = 'Chat';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
            <div class="chat-leftsidebar">
                <div class="px-4 pt-4 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="mb-4">Chats</h5>
                        </div>
                        <div aria-hidden="true" aria-labelledby="addGroupModalLabel" class="modal fade" id="addGroupModal"
                            role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-lg d-flex align-items-center justify-content-center h-100">
                                <div class="modal-content rounded-3 shadow-lg">
                                    <div class="modal-header bg-primary text-white rounded-top p-3">
                                        <h5 class="modal-title text-white" id="addGroupModalLabel">
                                            Thêm nhóm
                                        </h5>
                                        <button aria-label="Close" class="close text-white" data-dismiss="modal"
                                            type="button">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-body p-4 bg-light rounded-bottom">
                                        <form id="createGroupChatForm">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label for="groupName" class="font-weight-bold">Tên nhóm</label>
                                                <input class="form-control py-2" name="name" id="groupName"
                                                    placeholder="Nhập tên nhóm" type="text" />
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="groupType" class="font-weight-bold">Chọn kiểu nhóm</label>
                                                <select class="form-select py-2" name="type" id="groupType">
                                                    <option value="#">Chọn kiểu nhóm</option>
                                                    <option value="1">Personal</option>
                                                    <option value="2">Group</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="groupMembers" class="font-weight-bold">Add Members</label>
                                                <select tabindex="-1" id="groupMembers" name="members[]"
                                                    class="form-select py-2" multiple="multiple">
                                                    @foreach ($admins as $admin)
                                                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="btn btn-primary w-100 py-2" type="submit">
                                                Add Group
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="flex-shrink-0">
                            <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                title="Add Contact">

                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-soft-success btn-sm" data-toggle="modal"
                                    data-target="#addGroupModal">
                                    <i class="ri-add-line align-bottom"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="search-box">
                        <input type="text" class="form-control bg-light border-light" placeholder="Search here...">
                        <i class="ri-search-2-line search-icon"></i>
                    </div>
                </div> <!-- .p-4 -->

                <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#chats" role="tab">
                            Chats
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#contacts" role="tab">
                            Contacts
                        </a>
                    </li>
                </ul>

                <div class="tab-content text-muted">
                    <div class="tab-pane active" id="chats" role="tabpanel">
                        <div class="chat-room-list pt-3" data-simplebar>
                            <div class="d-flex align-items-center px-4 mb-2">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0 fs-11 text-muted text-uppercase">Direct Messages</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                        title="New Message">

                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-soft-success btn-sm shadow-none">
                                            <i class="ri-add-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-message-list">

                                <ul class="list-unstyled chat-list chat-user-list" id="userList">

                                </ul>
                            </div>

                            <div class="d-flex align-items-center px-4 mt-4 pt-2 mb-2">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0 fs-11 text-muted text-uppercase">Channels</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                        title="Create group">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-soft-success btn-sm">
                                            <i class="ri-add-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-message-list">

                                <ul class="list-unstyled chat-list chat-user-list mb-0" id="conversationList">
                                    @foreach ($channels as $channel)
                                        <li class="">
                                            <a href="#" class="unread-msg-user group-button"
                                                data-channel-id="{{ $channel->id }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 chat-user-img align-self-center me-2 ms-0">
                                                        <div class="avatar-xxs">
                                                            <div class="avatar-title bg-light rounded-circle text-body">#
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <p class="text-truncate mb-0">
                                                            {{ $channel->name }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach

                                </ul>
                            </div>
                            <!-- End chat-message-list -->
                        </div>
                    </div>
                    <div class="tab-pane" id="contacts" role="tabpanel">
                        <div class="chat-room-list pt-3" data-simplebar>
                            <div class="sort-contact">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end tab contact -->
            </div>
            <!-- end chat leftsidebar -->
            <!-- Start User chat -->
            <div class="user-chat w-100 overflow-hidden">

                <div class="chat-content d-lg-flex">
                    <!-- start chat conversation section -->
                    <div class="w-100 overflow-hidden position-relative">
                        <!-- conversation user -->
                        <div class="position-relative">


                            <div class="position-relative" id="users-chat">
                                <div class="p-3 user-chat-topbar">
                                    <div class="row align-items-center">
                                        <div class="col-sm-4 col-8">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 d-block d-lg-none me-3">
                                                    <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1"><i
                                                            class="ri-arrow-left-s-line align-bottom"></i></a>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="flex-shrink-0 chat-user-img online user-own-img align-self-center me-3 ms-0">
                                                            <img src="{{ asset('assets/images/users/multi-user.jpg') }}"
                                                                class="rounded-circle avatar-xs" alt="">
                                                            <span class="user-status"></span>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden" id="groupInfo">
                                                            <h5 class="text-truncate mb-0 fs-16">
                                                                <a class="text-reset username" id="name"></a>
                                                            </h5>
                                                            <p class="text-truncate text-muted fs-14 mb-0 userStatus">
                                                                <small id="memberCount"></small>
                                                            </p>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-8 col-4">
                                            <ul class="list-inline user-chat-nav text-end mb-0">
                                                <li class="list-inline-item m-0">
                                                    <div class="dropdown">
                                                        <button class="btn btn-ghost-secondary btn-icon" type="button"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i data-feather="search" class="icon-sm"></i>
                                                        </button>
                                                        <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                                            <div class="p-2">
                                                                <div class="search-box">
                                                                    <input type="text"
                                                                        class="form-control bg-light border-light"
                                                                        placeholder="Search here..."
                                                                        onkeyup="searchMessages()" id="searchMessage">
                                                                    <i class="ri-search-2-line search-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>

                                                <li class="list-inline-item d-none d-lg-inline-block m-0">
                                                    <button type="button" class="btn btn-ghost-secondary btn-icon"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#userProfileCanvasExample"
                                                        aria-controls="userProfileCanvasExample">
                                                        <i data-feather="info" class="icon-sm"></i>
                                                    </button>
                                                </li>

                                                <li class="list-inline-item m-0">
                                                    <div class="dropdown">
                                                        <button class="btn btn-ghost-secondary btn-icon" type="button"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i data-feather="more-vertical" class="icon-sm"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item d-block d-lg-none user-profile-show"
                                                                href="#"><i
                                                                    class="ri-user-2-fill align-bottom text-muted me-2"></i>
                                                                View Profile</a>
                                                            <a class="dropdown-item" href="#"><i
                                                                    class="ri-inbox-archive-line align-bottom text-muted me-2"></i>
                                                                Archive</a>
                                                            <a class="dropdown-item" href="#"><i
                                                                    class="ri-mic-off-line align-bottom text-muted me-2"></i>
                                                                Muted</a>
                                                            <a class="dropdown-item" href="#"><i
                                                                    class="ri-delete-bin-5-line align-bottom text-muted me-2"></i>
                                                                Delete</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <!-- end chat user head -->
                                <div class="chat-conversation p-3 p-lg-4 " id="chatBox" data-simplebar>
                                    <div id="elmLoader">
                                        <div class="spinner-border text-primary avatar-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled chat-conversation-list" id="messagesList">

                                    </ul>
                                    <!-- end chat-conversation-list -->
                                </div>
                                <div class="alert alert-warning alert-dismissible copyclipboard-alert px-4 fade show "
                                    id="copyClipBoard" role="alert">
                                    Message copied
                                </div>
                            </div>

                            {{-- <div class="position-relative" id="channel-chat">
                                <div class="p-3 user-chat-topbar">
                                    <div class="row align-items-center">
                                        <div class="col-sm-4 col-8">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 d-block d-lg-none me-3">
                                                    <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1"><i
                                                            class="ri-arrow-left-s-line align-bottom"></i></a>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="flex-shrink-0 chat-user-img online user-own-img align-self-center me-3 ms-0">
                                                            <img src="{{ asset('assets/images/users/avatar-2.jpg') }}"
                                                                class="rounded-circle avatar-xs" alt="">
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <h5 class="text-truncate mb-0 fs-16"><a
                                                                    class="text-reset username" data-bs-toggle="offcanvas"
                                                                    href="#userProfileCanvasExample"
                                                                    aria-controls="userProfileCanvasExample">Lisa
                                                                    Parker</a></h5>
                                                            <p class="text-truncate text-muted fs-14 mb-0 userStatus">
                                                                <small>24 Members</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-8 col-4">
                                            <ul class="list-inline user-chat-nav text-end mb-0">
                                                <li class="list-inline-item m-0">
                                                    <div class="dropdown">
                                                        <button class="btn btn-ghost-secondary btn-icon" type="button"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i data-feather="search" class="icon-sm"></i>
                                                        </button>
                                                        <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                                            <div class="p-2">
                                                                <div class="search-box">
                                                                    <input type="text"
                                                                        class="form-control bg-light border-light"
                                                                        placeholder="Search here..."
                                                                        onkeyup="searchMessages()" id="searchMessage">
                                                                    <i class="ri-search-2-line search-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>

                                                <li class="list-inline-item d-none d-lg-inline-block m-0">
                                                    <button type="button" class="btn btn-ghost-secondary btn-icon"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#userProfileCanvasExample"
                                                        aria-controls="userProfileCanvasExample">
                                                        <i data-feather="info" class="icon-sm"></i>
                                                    </button>
                                                </li>

                                                <li class="list-inline-item m-0">
                                                    <div class="dropdown">
                                                        <button class="btn btn-ghost-secondary btn-icon" type="button"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i data-feather="more-vertical" class="icon-sm"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item d-block d-lg-none user-profile-show"
                                                                href="#"><i
                                                                    class="ri-user-2-fill align-bottom text-muted me-2"></i>
                                                                View Profile</a>
                                                            <a class="dropdown-item" href="#"><i
                                                                    class="ri-inbox-archive-line align-bottom text-muted me-2"></i>
                                                                Archive</a>
                                                            <a class="dropdown-item" href="#"><i
                                                                    class="ri-mic-off-line align-bottom text-muted me-2"></i>
                                                                Muted</a>
                                                            <a class="dropdown-item" href="#"><i
                                                                    class="ri-delete-bin-5-line align-bottom text-muted me-2"></i>
                                                                Delete</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <!-- end chat user head -->
                                <div class="chat-conversation p-3 p-lg-4" id="chat-conversation" data-simplebar>
                                    <ul class="list-unstyled chat-conversation-list" id="channel-conversation">
                                    </ul>
                                    <!-- end chat-conversation-list -->

                                </div>
                                <div class="alert alert-warning alert-dismissible copyclipboard-alert px-4 fade show "
                                    id="copyClipBoardChannel" role="alert">
                                    Message copied
                                </div>
                            </div> --}}

                            <!-- end chat-conversation -->

                            <div class="chat-input-section p-3 p-lg-4">

                                <form id="chatinput-form" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-0 align-items-center">
                                        <div class="col-auto">
                                            <div class="chat-input-links me-2">
                                                <div class="links-list-item">
                                                    <button type="button"
                                                        class="btn btn-link text-decoration-none emoji-btn"
                                                        id="emoji-btn">
                                                        <i class="bx bx-smile align-middle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-link text-decoration-none"
                                                        id="upload-btn">
                                                        <i class="bx bx-paperclip align-middle"></i>
                                                    </button>

                                                    <input type="file" id="file-input" style="display: none;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="chat-input-feedback">
                                                Please Enter a Message
                                            </div>
                                            <input type="text" class="form-control chat-input bg-light border-light"
                                                id="messageInput" placeholder="Type your message..." autocomplete="off">
                                        </div>
                                        <div class="col-auto">
                                            <div class="chat-input-links ms-2">
                                                <div class="links-list-item">
                                                    <button type="submit" id="sendMessageButton"
                                                        class="btn btn-success chat-send waves-effect waves-light">
                                                        <i class="ri-send-plane-2-fill align-bottom"></i>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </form>
                            </div>

                            <div class="replyCard">
                                <div class="card mb-0">
                                    <div class="card-body py-3">
                                        <div class="replymessage-block mb-0 d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="conversation-name"></h5>
                                                <p class="mb-0"></p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <button type="button" id="close_toggle"
                                                    class="btn btn-sm btn-link mt-n2 me-n3 fs-18">
                                                    <i class="bx bx-x align-middle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end chat-wrapper -->

    </div>
@endsection

@push('page-scripts')
    <script>
        var APP_URL = "{{ env('APP_URL') . '/' }}";
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="{{ asset('assets/libs/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/libs/fg-emoji-picker/fgEmojiPicker.js') }}"></script>
    <script>
        function initIcons() {
            document.addEventListener("DOMContentLoaded", function() {
                let emojiButton = document.getElementById("emoji-btn");
                if (!emojiButton) {
                    console.error("Không tìm thấy nút emoji-btn!");
                    return;
                }

                let emojiPicker = new FgEmojiPicker({
                    trigger: [".emoji-btn"],
                    removeOnSelection: false,
                    closeButton: true,
                    position: ["top", "right"],
                    preFetch: true,
                    dir: "assets/js/pages/plugins/json",
                    insertInto: document.querySelector(".chat-input"),
                });

                emojiButton.addEventListener("click", function() {
                    setTimeout(function() {
                        let pickerEl = document.querySelector(".fg-emoji-picker");
                        if (pickerEl) {
                            let leftPos = parseInt(window.getComputedStyle(pickerEl).left) || 0;
                            pickerEl.style.left = `${leftPos - 40}px`;
                        } else {
                            console.error("Không tìm thấy phần tử fg-emoji-picker!");
                        }
                    }, 100);
                });

                console.log("Hàm initIcons đã chạy thành công!");
            });
        }
        initIcons();

        $(document).ready(function() {
            $("#upload-btn").click(function() {
                $("#file-input").click();
            });
        });
    </script>
    <script>
        var currentConversationId = null;
        $(document).ready(function() {
            $('#createGroupChatForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize(); // Lấy dữ liệu từ form
                $.ajax({
                    url: "{{ route('admin.chats.create') }}",
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.status == 'success') {
                            // Cập nhật lại dữ liệu nhóm và admin trên giao diện
                            $('#conversationList').html(response.data.channels);
                            alert(response.message); // Hiển thị thông báo thành công
                            window.location.href = "{{ route('admin.chats.index') }}";
                        } else {
                            alert(response.message); // Hiển thị thông báo lỗi
                        }
                    },
                    error: function() {
                        alert("Có lỗi xảy ra!"); // Hiển thị lỗi
                    }
                });
            });

            $('#conversationList a').click(function(event) {
                event.preventDefault(); // Ngừng hành động mặc định của liên kết

                var channelId = $(this).data('channel-id'); // Lấy ID của nhóm chat

                // Gửi yêu cầu AJAX để lấy thông tin nhóm
                $.ajax({
                    url: "{{ route('admin.chats.getGroupInfo') }}", // Endpoint API để lấy thông tin nhóm
                    method: 'GET',
                    data: {
                        id: channelId
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.status === 'success') {
                            // Cập nhật tên nhóm và số thành viên
                            $('#name').text(response.data.name);
                            $('#memberCount').text(response.data.memberCount);
                            loadMessages(response.data.group.id);
                        } else {
                            alert('Không thể lấy thông tin nhóm');
                        }
                    },
                    error: function() {
                        alert('Có lỗi xảy ra trong quá trình lấy dữ liệu');
                    }
                });
            });
            // Khi người dùng chọn một nhóm
            $('.group-button').click(function() {
                currentConversationId = $(this).data('channel-id'); // Lấy ID nhóm đã chọn
                console.log('Đã chọn nhóm với ID:', currentConversationId);
                window.Echo.private('conversation.' + currentConversationId)
                    .listen('GroupMessageSent', function(event) {
                        loadMessages(currentConversationId);
                    });
            });

            // Khi người dùng nhấn gửi tin nhắn
            $('#sendMessageButton').click(function(e) {
                e.preventDefault();
                var content = $('#messageInput').val();
                var parentId = $('#parentMessageId')
                    .val(); // Nếu đây là tin nhắn trả lời, lấy ID của tin nhắn cha
                var type = 'text'; // Hoặc 'image', 'file', tùy thuộc vào loại tin nhắn
                var metaData = null; // Nếu có dữ liệu bổ sung (ví dụ: hình ảnh, file...)
                if (currentConversationId && content) {
                    // Gửi tin nhắn vào nhóm hiện tại
                    $.ajax({
                        url: "{{ route('admin.chats.sendGroupMessage') }}",
                        method: 'POST',
                        data: {
                            conversation_id: currentConversationId,
                            content: content,
                            parent_id: parentId, // Nếu có
                            type: type,
                            meta_data: metaData,
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#messageInput').val(''); // Xóa nội dung nhập
                                loadMessages(
                                    currentConversationId); // Tải lại tin nhắn của nhóm
                            }
                        }
                    });
                } else {
                    alert("Vui lòng chọn nhóm và nhập tin nhắn!");
                }
                // $('#sendMessageButton').click(function() {
                //     var content = $('#messageInput').val();
                //     var conversationId = $(this).data('conversation-id'); // ID của nhóm chat hiện tại
                //     var parentId = $('#parentMessageId').val(); // Nếu đây là tin nhắn trả lời, lấy ID của tin nhắn cha
                //     var type = 'text'; // Hoặc 'image', 'file', tùy thuộc vào loại tin nhắn
                //     var metaData = null; // Nếu có dữ liệu bổ sung (ví dụ: hình ảnh, file...)
                //     console.log('Conversation ID:', conversationId); // Kiểm tra giá trị của conversationId
                //     if (content) {
                //         $.ajax({
                //             url: "{{ route('admin.chats.sendGroupMessage') }}",
                //             method: 'POST',
                //             data: {
                //                 conversation_id: conversationId,
                //                 content: content,
                //                 parent_id: parentId, // Nếu có
                //                 type: type,
                //                 meta_data: metaData,
                //                 _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
                //             },
                //             success: function(response) {
                //                 if (response.status === 'success') {
                //                     $('#messageInput').val(''); // Xóa nội dung nhập
                //                     loadMessages(conversationId); // Tải lại tin nhắn
                //                 }
                //             }
                //         });
                //     }


                // Lấy và hiển thị tin nhắn
                // function loadMessages(currentConversationId) {

                //     $.get('admin.chats.getGroupMessages. ' + currentConversationId, function(response) {
                //         if (response.status === 'success') {
                //             $('#messagesList').html(''); // Xóa danh sách tin nhắn cũ
                //             response.messages.forEach(function(message) {
                //                 var messageHtml = `
            //         <div class="message">
            //             <strong>${message.sender.name}</strong>: ${message.message}
            //             <div class="message-meta">
            //                 Type: ${message.type}
            //             </div>
            //         </div>
            //     `;
                //                 if (message.meta_data) {
                //                     messageHtml += `
            //             <div class="message-meta">
            //                 Meta Data: ${message.meta_data}
            //             </div>
            //         `;
                //                 }
                //                 $('#messagesList').append(messageHtml);
                //             });
                //         }
                //     });
                // }
            });

            function loadMessages(conversationId) {
                $.get('http://127.0.0.1:8000/admin/chats/get-messages/' + conversationId, function(response) {
                    if (response.status === 'success') {

                        $('#messagesList').html(''); // Xóa danh sách tin nhắn cũ

                        const messagesHtml = response.messages.map(message => {

                            return `
            <div class="message">
                <strong>${message.sender?.name || 'Người dùng ẩn danh'}</strong>: ${message.content}
                <div class="message-meta">
                    Type: ${message.type || 'text'}
                </div>
                ${message.meta_data ? `
                                                                    <div class="message-meta">
                                                                        Meta Data: ${message.meta_data}
                                                                    </div>
                                                                ` : ''}
            </div>
        `;
                        }).join(''); // Chuyển mảng thành chuỗi HTML

                        $('#elmLoader').hide();
                        $('#messagesList').append(messagesHtml);
                    } else {
                        $('#elmLoader').show();
                    }
                });
            }

        });
    </script>
@endpush
