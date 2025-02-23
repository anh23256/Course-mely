<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hóa đơn mua khóa học</title>
</head>

<body style="font-family: 'Roboto', sans-serif; margin: 10px 0; padding: 0;">
    <div
        style="width: 100%; max-width: 600px; margin: auto; background-color: #fff; padding: 20px; box-shadow: 0 3px 15px rgba(30,32,37,.06); border-radius: 7px;">
        <div style="text-align: center;">
            <div style="display: flex; gap: 10px;">
                <div
                    style="width: 40px; height: 40px; background-image: url('https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311680/logo-container_zi19ug.png'); 
                            background-size: contain; background-repeat: no-repeat; background-position: center;">
                </div>
                <span style="font-size: 24px; font-weight: bold; color: red;">CourseMeLy</span>
            </div>

            <table width="100%" cellspacing="0" cellpadding="0" style="margin: 20px 0;">
                <tr>
                    <td align="center">
                        <div
                            style="width: 50%; height: 250px; 
                                    background-image: url('https://res.cloudinary.com/dere3na7i/image/upload/c_thumb,w_200,g_face/v1740311681/unnamed_uigmjf.png'); 
                                    background-size: cover; background-position: center;">
                        </div>
                    </td>
                </tr>
            </table>
            <h1 style="color: #333; font-size: 28px; margin: 10px 0;">Bạn đã sẵn sàng để học chưa?</h1>
            <p style="font-size: 18px; color: #555; line-height: 1.5;">Hãy bắt đầu khóa học miễn phí của bạn ngay hôm
                nay<br>và xem việc học có thể đưa bạn đến đâu.</p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 10px; border: 1px solid #ddd;">
            <h1 style="text-align: center; font-weight: 600;">Hóa đơn thanh toán</h1>
            <p style="color: #878a99; font-size: 15px;">Xin chào, {{ $student->name }}</p>
            <p style="color: #878a99;">Đơn hàng của bạn đã được thanh toán thành công.</p>
            <table style="width:100%; border-collapse: collapse; margin-top: 15px;">
                <tr>
                    <th style="padding: 8px; text-align: left; border-bottom: 1px solid #e9ebec;">Mã đơn hàng</th>
                    <th style="padding: 8px; text-align: left; border-bottom: 1px solid #e9ebec;">Ngày đặt</th>
                    <th style="padding: 8px; text-align: left; border-bottom: 1px solid #e9ebec;">Phương thức thanh toán
                    </th>
                </tr>
                <tr>
                    <td style="padding: 8px;">{{ $transaction->id }}</td>
                    <td style="padding: 8px;">
                        {{ \Illuminate\Support\Carbon::parse($transaction->created_at)->locale('vi')->translatedFormat('d F Y') }}
                    </td>
                    <td style="padding: 8px;">VNPAY</td>

                </tr>
            </table>
            <h4 style="text-decoration: underline; margin-top: 20px;">Khóa học đã mua:</h4>
            <table style="width:100%; border-collapse: collapse; margin-top: 10px;">
                <tr>
                    <th style="padding: 8px; border-bottom: 1px solid #e9ebec;">Tên khóa học</th>
                    <th style="padding: 8px; border-bottom: 1px solid #e9ebec;">Giảng viên</th>
                </tr>
                <tr>
                    <td style="padding: 8px;">{{ $course->name }}</td>
                    <td style="padding: 8px;">{{ $course->user->name }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 8px; text-align: end; border-top: 1px solid #e9ebec;">Tổng cộng
                    </td>
                    <th style="padding: 8px; border-top: 1px solid #e9ebec;">
                        {{ number_format(round($course->price_sale, 2) ?? $course->price, 0, ',', '.') }} VND</th>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 8px; text-align: end;">Giảm giá</td>
                    <th style="padding: 8px;">
                        {{ number_format($invoice->amount - $invoice->final_amount, 0, ',', '.') }}
                        VND
                    </th>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 8px; text-align: end; border-top: 1px solid #e9ebec;">Tổng tiền
                    </td>
                    <th style="padding: 8px; border-top: 1px solid #e9ebec;">
                        {{ number_format($transaction->amount, 0, ',', '.') }} VND</th>
                </tr>
            </table>
            <p style="color: #878a99;">Cảm ơn bạn đã mua khóa học trên nền tảng của chúng tôi. Chúng tôi cam kết mang
                lại
                trải nghiệm học tập tốt nhất, giúp bạn phát triển kỹ năng và tiến xa hơn trong sự nghiệp.</p>
            <h4 style="text-align: end;"><span style="text-align: center">Trân trọng! <br> Đội ngũ CourseMeLy</span>
            </h4>
        </div>
    </div>
</body>

</html>
