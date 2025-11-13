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
        .footer { background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Request Approved!</h1>
            <p>North Caloocan City Hall - Document Request</p>
        </div>
        
        <div class="content">
            <span class="badge">APPROVED</span>
            
            <p style="font-size: 16px; margin-top: 15px;">Dear <strong>{{ $preReg->full_name }}</strong>,</p>
            
            <p>Your request has been <strong>approved</strong>.</p>
            
            <div class="qr-section">
                <h3>🎫 Your QR Code for Queueing</h3>
                
                @if($preReg->qr_image_path)
                    <img src="{{ $message->embed(storage_path('app/public/qrcodes/' . $preReg->qr_image_path)) }}" alt="Queue QR Code">
                @else
                    <p style="color: #dc2626;">QR Code generation in progress. Please check your email again in a few moments.</p>
                @endif
                
                <p style="font-size: 14px; color: #059669; margin: 15px 0;">
                    <strong>Please go to the City Hall to claim your requested file.</strong>
                </p>
                <p style="font-size: 13px; color: #6b7280;">
                    Scan this QR code at our kiosk when you arrive.
                </p>
            </div>
            
            <div class="info-box">
                <strong>📋 Service Requested:</strong> {{ $preReg->service_type_name }}<br>
                <strong>📅 Approved:</strong> {{ now()->format('M d, Y h:i A') }}
            </div>
            
            <div class="steps">
                <h3>📍 What to do next:</h3>
                <ol>
                    <li>Visit <strong>North Caloocan City Hall</strong></li>
                    <li>Scan your QR code at the kiosk</li>
                    <li>Get your queue number</li>
                    <li>Wait for your turn to be called</li>
                    <li>Claim your requested file at the counter</li>
                </ol>
            </div>
            
            <p style="text-align: center; margin-top: 25px; color: #059669; font-weight: bold;">
                Thank you!
            </p>
        </div>
        
        <div class="footer">
            <p><strong>North Caloocan City Hall</strong><br>
            Assessor's Office</p>
            <p style="margin: 10px 0;"><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>