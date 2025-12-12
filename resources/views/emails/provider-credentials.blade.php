<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Provider Account</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .email-body {
            padding: 40px 30px;
        }
        .welcome-text {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .credentials-box p {
            margin: 10px 0;
            font-size: 15px;
        }
        .credentials-box strong {
            color: #667eea;
            display: inline-block;
            min-width: 100px;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            background: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
            margin-left: 10px;
            border: 1px solid #dee2e6;
        }
        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .security-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 14px;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .footer-links {
            margin-top: 15px;
        }
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🎉 Welcome to Isaiah Nail Bar</h1>
        </div>
        
        <div class="email-body">
            <p class="welcome-text">Hello <strong>{{ $provider->name }}</strong>,</p>
            
            <p>Your provider account has been created! We're excited to have you as part of our team at Isaiah Nail Bar.</p>
            
            <p>Here are your login credentials:</p>
            
            <div class="credentials-box">
                <p>
                    <strong>Email:</strong>
                    <span class="credential-value">{{ $provider->email }}</span>
                </p>
                <p>
                    <strong>Password:</strong>
                    <span class="credential-value">{{ $password }}</span>
                </p>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="login-button">Login to Your Account</a>
            </div>
            
            <div class="security-notice">
                <strong>🔒 Security Reminder:</strong><br>
                For your security, please change your password after your first login. Keep your credentials confidential and never share them with anyone.
            </div>
            
            <p style="margin-top: 30px;">If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <p style="margin-top: 20px;">Best regards,<br><strong>Isaiah Nail Bar Team</strong></p>
        </div>
        
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Isaiah Nail Bar. All rights reserved.</p>
            <div class="footer-links">
                <a href="{{ url('/') }}">Visit Website</a> |
                <a href="{{ url('/contact') }}">Contact Support</a>
            </div>
        </div>
    </div>
</body>
</html>
