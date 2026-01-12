<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .content h2 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .content p {
            color: #4a5568;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11);
            transition: all 0.3s ease;
        }
        
        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1);
        }
        
        .footer {
            background: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            color: #718096;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .warning {
            background: #fef5e7;
            border-left: 4px solid #f6ad55;
            padding: 16px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .warning p {
            color: #744210;
            margin: 0;
            font-size: 14px;
        }
        
        .link-text {
            word-break: break-all;
            color: #667eea;
            font-size: 14px;
            background: #f7fafc;
            padding: 12px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .button {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Password Reset Request</p>
        </div>
        
        <div class="content">
            <h2>Reset Your Password</h2>
            
            <p>Hello,</p>
            
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="button">Reset Password</a>
            </div>
            
            <div class="warning">
                <p><strong>Security Notice:</strong> This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes for your security.</p>
            </div>
            
            <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
            
            <div class="link-text">
                {{ $actionUrl }}
            </div>
            
            <p>If you did not request a password reset, no further action is required. Your password will remain unchanged.</p>
            
            <p>Best regards,<br>{{ config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>