<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response to Your Support Request - {{ $appName }}</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            border-left: 4px solid #28a745;
        }
        .original-request {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
        }
        .reply-content {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
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
        .cta-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: white;
            color: #007bff;
            text-decoration: none;
            border-radius: 25px;
            margin: 10px 0;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            background-color: #28a745;
            color: white;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .reply-from {
            font-size: 14px;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ We've Responded to Your Request</h1>
            <span class="status-badge">Reply Received</span>
        </div>

        <div class="shop-info">
            <h3>📋 Support Request Update</h3>
            <div class="meta-info">
                <div class="meta-item">
                    <strong>Shop</strong>
                    {{ $shopDomain }}
                </div>
                <div class="meta-item">
                    <strong>App</strong>
                    {{ $appName }}
                </div>
                <div class="meta-item">
                    <strong>Request ID</strong>
                    #{{ $expectationId }}
                </div>
                <div class="meta-item">
                    <strong>Replied At</strong>
                    {{ \Carbon\Carbon::parse($repliedAt)->format('M d, Y \a\t h:i A T') }}
                </div>
            </div>
        </div>

        <div class="original-request">
            <h3>📝 Your Original Request</h3>
            <p><strong>Submitted:</strong> {{ $expectation->created_at->format('M d, Y \a\t h:i A') }}</p>
            <p style="font-style: italic; margin: 15px 0 0 0;">
                "{{ $originalMessage }}"
            </p>
        </div>

        <div class="reply-content">
            <div class="reply-from">
                💬 Response from {{ $adminEmail }}
            </div>
            <p style="font-size: 16px; line-height: 1.6; margin: 0;">
                {{ $replyMessage }}
            </p>
        </div>

        <div class="cta-section">
            <h3 style="margin-top: 0;">📧 Need Further Assistance?</h3>
            <p style="margin: 15px 0;">
                <strong>Reply to this email to continue the conversation!</strong><br>
                Our support team will receive your response and get back to you promptly.
            </p>
            <p style="font-size: 14px; opacity: 0.9;">
                You can also visit the support page in your app to see all conversations and submit new requests.
            </p>
        </div>

        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #856404;">💡 Quick Tip</h4>
            <p style="margin-bottom: 0; color: #856404;">
                For faster support, simply <strong>reply to this email</strong> instead of creating a new support request.
                This keeps all your conversation in one thread!
            </p>
        </div>

        <div class="footer">
            <p>
                <strong>{{ $appName }} Support Team</strong><br>
                We're here to help you succeed with {{ $appName }}
            </p>
            <p style="font-size: 12px; margin-top: 15px;">
                This email was sent because you submitted a support request. If you have questions,
                reply to this email or contact us at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
            </p>
            <p style="font-size: 11px; color: #999; margin-top: 10px;">
                Support Request #{{ $expectationId }} | {{ $appName }} by Best Decoders
            </p>
        </div>
    </div>
</body>
</html>