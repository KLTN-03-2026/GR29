<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác nhận đặt lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #e74c3c;
            font-weight: bold;
        }
        .content {
            text-align: center;
        }
        .content p {
            color: #555;
            line-height: 1.6;
            margin: 15px 0;
        }
        .otp-code {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            padding: 20px 30px;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            color: #856404;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SOS_GR29</h1>
            <p>Hệ thống điều phối cứu hộ thông minh trong thiên tai dựa trên phân tích mức độ khẩn cấp</p>
        </div>
        
        <div class="content">
            <p>Xin chào {{ $hoTen ?? 'bạn' }},</p>
            <p>Mã xác nhận để đặt lại mật khẩu của bạn là:</p>
            
            <div class="otp-code">{{ $maOtp }}</div>
            
            <p>Mã này có hiệu lực trong <strong>5 phút</strong>.</p>
            
            <div class="warning">
                ⚠️ <strong>Lưu ý:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi ngay.
            </div>
        </div>
        
        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống SOS_GR29.</p>
            <p>&copy; {{ date('Y') }} SOS_GR29 - Khóa luận tốt nghiệp</p>
        </div>
    </div>
</body>
</html>