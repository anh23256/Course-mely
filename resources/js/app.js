import './bootstrap';
Echo.channel('conversation.' + conversationId)
    .listen('MessageSent', (event) => {
        // Cập nhật danh sách tin nhắn ngay lập tức
        $('#messagesList').append(`
            <div class="message">
                <strong>${event.message.user.name}</strong>: ${event.message.message}
            </div>
        `);
    });
