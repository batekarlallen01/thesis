<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .pin-box {
            background: white;
            border: 3px dashed #667eea;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }
        .pin-code {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
        }
        .info-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-row {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            color: #666;
            font-size: 12px;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✓ Application Confirmed</h1>
        <p>North Caloocan City Hall - Document Drop-off</p>
    </div>

    <div class="content">
        <p>Dear <strong>{{ $submission->full_name }}</strong>,</p>
        
        <p>Thank you for submitting your document drop-off application. Your request has been received and is being processed.</p>

        <div class="pin-box">
            <p style="margin: 0; font-size: 14px; color: #666; text-transform: uppercase;">Your 6-Digit PIN Code</p>
            <div class="pin-code">{{ $submission->pin_code }}</div>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">Use this PIN to unlock the IoT mailbox and collect your documents</p>
        </div>

        <div class="alert">
            <strong>🔒 Important Security Notice:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Keep this PIN code secure and confidential</li>
                <li>Do not share your PIN with anyone</li>
                <li>You will need this PIN to unlock the IoT mailbox once your documents are ready</li>
                <li>The PIN is valid only for your application</li>
            </ul>
        </div>

        <h3 style="color: #667eea;">📋 Application Details</h3>
        <div class="info-table">
            <div class="info-row">
                <span class="info-label">Application ID:</span>
                <span>#{{ str_pad($submission->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Service Type:</span>
                <span>{{ $submission->service_type_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Applicant Type:</span>
                <span>{{ ucfirst($submission->applicant_type) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Number of Copies:</span>
                <span>{{ $submission->number_of_copies }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fee Amount:</span>
                <span>₱{{ number_format($submission->getFeeAmount(), 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Submitted:</span>
                <span>{{ $submission->created_at->format('F d, Y h:i A') }}</span>
            </div>
        </div>

        <h3 style="color: #667eea;">📌 Next Steps</h3>
        <ol style="line-height: 1.8;">
            <li><strong>Bring Required Documents</strong> - Visit North Caloocan City Hall with all required documents</li>
            <li><strong>Pay Processing Fee</strong> - Pay ₱{{ number_format($submission->getFeeAmount(), 2) }} at the cashier</li>
            <li><strong>Wait for Notification</strong> - You'll receive an email when your documents are ready</li>
            <li><strong>Use Your PIN</strong> - Go to the IoT mailbox, enter your 6-digit PIN code to unlock and collect your documents</li>
        </ol>

        <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 0;"><strong>📞 Need Help?</strong></p>
            <p style="margin: 5px 0 0 0; font-size: 14px;">
                Contact us: (02) 1234-5678 | info@caloocan.gov.ph<br>
                Office Hours: Monday to Friday, 8:00 AM - 5:00 PM
            </p>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated message from North Caloocan City Hall<br>
        Please do not reply to this email</p>
        <p>© 2025 North Caloocan City Government. All rights reserved.</p>
    </div>
</body>
</html>