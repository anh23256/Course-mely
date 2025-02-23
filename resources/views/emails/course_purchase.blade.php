<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận mua khóa học</title>
</head>
<body>
    <h1>Chào bạn {{ $student->name }},</h1>
    <p>Cảm ơn bạn đã mua khóa học "<strong>{{ $course->name }}</strong>".</p>
    <p>Giảng viên khóa học: {{ $course->user->name }}</p>
    <p>Số tiền thanh toán: {{ number_format($transaction->amount, 0, ',', '.') }} VND</p>
    <p>Mã giao dịch: {{ $transaction->id }}</p>
    <p>Chúc bạn học tập hiệu quả và gặt hái nhiều thành công!</p>
    <p>Trân trọng,<br>Đội ngũ CourseMeLy</p>
</body>
</html>
