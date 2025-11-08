<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 10px 0 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .badge { display: inline-block; background: #10b981; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .qr-section { background: #f0fdf4; padding: 25px; margin: 20px 0; border-radius: 12px; text-align: center; border: 3px dashed #10b981; }
        .qr-section h3 { color: #059669; margin-top: 0; }
        .qr-section img { max-width: 220px; margin: 15px 0; border: 4px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; }
        .info-box { background: #f9fafb; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #3b82f6; }
        .info-box strong { color: #1f2937; }
        .steps { background: #fffbeb; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 20px 0; }
        .steps h3 { color: #92400e; margin-top: 0; }
        .steps ol { margin: 10px 0; padding-left: 20px; }
        .steps li { margin: 8px 0; }
        .warning { background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 20px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Documents Approved!</h1>
            <p>North Caloocan City Hall - IoT Mailbox System</p>
        </div>
        
        <div class="content">
            <span class="badge">APPROVED</span>
            
            <p style="font-size: 16px; margin-top: 15px;">Dear <strong>{{ $submission->full_name }}</strong>,</p>
            
            <p>Great news! Your documents have been <strong>physically verified and approved</strong> by our staff.</p>
            
            <div class="qr-section">
                <h3 style="margin-bottom: 5px;">🎫 Your Queue Entry QR Code</h3>
                <p style="font-size: 14px; color: #059669; margin: 5px 0;">Scan this at our kiosk to enter the queue</p>
                
                @if($submission->qr_image_path)
                    <img src="{{ $submission->qr_image_url }}" alt="Queue Entry QR Code">
                @endif
                
                <p style="font-size: 13px; color: #f59e0b; font-weight: bold; margin: 10px 0;">⏰ Valid for 24 hours</p>
                <p style="font-size: 12px; color: #6b7280;">Expires: {{ $submission->qr_expires_at->format('F d, Y - h:i A') }}</p>
            </div>
            
            <div class="info-box">
                <strong>📋 Service:</strong> {{ $submission->service_type_name }}<br>
                <strong>👤 Applicant:</strong> {{ ucfirst($submission->applicant_type) }}<br>
                <strong>📄 Copies:</strong> {{ $submission->number_of_copies }}<br>
                <strong>📅 Approved:</strong> {{ $submission->approved_at->format('M d, Y h:i A') }}<br>
                <strong>💰 Fee:</strong> ₱{{ number_format($submission->getFeeAmount(), 2) }}
            </div>
            
            <div class="steps">
                <h3>📍 Next Steps:</h3>
                <ol>
                    <li><strong>Visit North Caloocan City Hall</strong> within 24 hours</li>
                    <li><strong>Go to the QR Scanner Kiosk</strong> at the entrance</li>
                    <li><strong>Scan the QR code</strong> shown above or attached to this email</li>
                    <li><strong>Get your queue number</strong> and wait for your turn</li>
                    <li><strong>Proceed to the counter</strong> when called</li>
                </ol>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important Reminders:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>QR code is valid for <strong>24 hours only</strong></li>
                    <li>One-time use - expires after scanning</li>
                    <li>Bring payment for processing fees</li>
                    <li>Arrive during office hours (Mon-Fri, 8AM-5PM)</li>
                </ul>
            </div>
            
            <p style="text-align: center; margin-top: 25px; color: #059669; font-weight: bold;">
                Thank you for using our IoT Mailbox System! 🎉
            </p>
        </div>
        
        <div class="footer">
            <p><strong>North Caloocan City Hall</strong><br>
            Assessor's Office - Queue Management System</p>
            <p style="margin: 10px 0;">This is an automated email. Please do not reply.</p>
            <p>For inquiries, visit our office during business hours.</p>
        </div>
    </div>
</body>
</html>