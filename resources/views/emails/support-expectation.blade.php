<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Support Request - {{ $appName }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .shop-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .expectation-content {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .meta-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .meta-item {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .meta-item strong {
            display: block;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .priority {
            display: inline-block;
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 New Support Request</h1>
            <span class="priority">Action Required</span>
        </div>

        <div class="shop-info">
            <h3>📊 Request Details</h3>
            <div class="meta-info">
                <div class="meta-item">
                    <strong>Shop Domain</strong>
                    {{ $shopDomain }}
                </div>
                <div class="meta-item">
                    <strong>App Name</strong>
                    {{ $appName }}
                </div>
                <div class="meta-item">
                    <strong>User Email</strong>
                    <a href="mailto:{{ $userEmail }}">{{ $userEmail }}</a>
                </div>
                <div class="meta-item">
                    <strong>Submitted At</strong>
                    {{ $submittedAt->format('M d, Y \a\t h:i A T') }}
                </div>
            </div>
        </div>

        <div class="expectation-content">
            <h3>💬 Customer Message</h3>
            <p style="font-size: 16px; line-height: 1.6; margin: 0;">
                {{ $expectationMessage }}
            </p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <p><strong>Reply to this support request using the admin console:</strong></p>
            <code style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; display: block; margin: 10px 0;">
                php artisan support:reply {{ $expectation->id }} "Your reply message here"
            </code>
        </div>

        <div class="footer">
            <p>
                <strong>{{ $appName }} Support System</strong><br>
                This email was automatically generated when a customer submitted a support request.<br>
                Expectation ID: #{{ $expectation->id }}
            </p>
            <p style="font-size: 12px; margin-top: 15px;">
                Please respond to this request within 24 hours to maintain our support quality standards.
            </p>
        </div>
    </div>
</body>
</html>