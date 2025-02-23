<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Minh Email</title>
</head>
<body style="margin:0; padding:0; background-color:#f9f9f9; font-family:Arial, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9f9f9; padding:20px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; padding:30px; border-radius:10px; box-shadow:0 4px 8px rgba(0,0,0,0.1); text-align:center;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center">
                            <img src="https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png" alt="CourseMeLy Logo" width="50" height="50">
                            <h1 style="color:#333; margin-top:10px;">CourseMeLy</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <h2 style="color:#000;">Xin chào {{$user->name ?? 'Bạn'}},</h2>
                            <p style="color:#555; font-size:16px;">
                                Bạn chỉ còn một bước nữa để truy cập vào hàng ngàn khóa học trên CourseMeLy.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="left" style="padding: 10px 30px;">
                            <strong style="font-size:16px;">✨ Lợi ích sau khi xác minh email:</strong>
                            <ul style="color:#555; font-size:16px; padding-left:20px;">
                                <li>✅ Truy cập đầy đủ các khóa học miễn phí & trả phí</li>
                                <li>✅ Nhận chứng chỉ hoàn thành khóa học</li>
                                <li>✅ Nhận thông tin cập nhật về các chương trình khuyến mãi & sự kiện</li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:20px;">
                            <p style="font-size:16px; color:#555;">Hãy nhấp vào nút bên dưới để hoàn tất đăng ký:</p>
                            <a href="{{$verificationUrl ?? '#'}}" style="display:inline-block; background-color:#28a745; color:#fff; padding:12px 24px; font-size:16px; text-decoration:none; border-radius:5px; font-weight:bold;">
                                🔥 Xác Minh Email Ngay
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding-top:20px;">
                            <p style="font-size:14px; color:#777;">
                                Nếu bạn không tạo tài khoản này, hãy bỏ qua email này.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top:20px;">
                            <p style="font-size:14px; color:#777;">&copy; 2025 CourseMeLy. Mọi quyền được bảo lưu.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
