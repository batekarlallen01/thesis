<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 10px 0 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .badge { display: inline-block; background: #3b82f6; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .info-box { background: #f9fafb; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #3b82f6; }
        .info-box strong { color: #1f2937; }
        .steps-box { background: #eff6ff; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6; margin: 20px 0; }
        .steps-box h3 { color: #1e40af; margin-top: 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
                    <h1 style="margin: 0;">North Caloocan City Hall - Assessors Department</h1>
     <img src="{{ $message->embed(public_path('img/mainlogo.png')) }}" 
     alt="North Caloocan City Hall"
     style="width: 80px; height: 80px; display: block; margin: 10px auto;">
            <p>📝 Application Received</p>
        </div>
        
        <div class="content">
            <span class="badge">UNDER REVIEW</span>
            
            <p style="font-size: 16px; margin-top: 15px;">Dear <strong>{{ $preReg->full_name }}</strong>,</p>
            
            <p>Thank you for submitting your pre-registration application. We have successfully received your request and documents.</p>
            
            <div class="info-box">
                <strong>📋 Service Requested:</strong> {{ $preReg->service_type_name }}<br>
                <strong>👤 Applicant Type:</strong> {{ ucfirst($preReg->applicant_type) }}<br>
                <strong>📅 Submitted:</strong> {{ $preReg->created_at->format('M d, Y h:i A') }}
            </div>
            
            <div class="steps-box">
                <h3>⏳ What Happens Next?</h3>
                <ol style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
                    <li><strong>Review Process:</strong> Our staff will review your submitted documents</li>
                    <li><strong>Verification:</strong> We'll verify that all required documents are complete and correct</li>
                    <li><strong>Decision:</strong> You'll receive an email with the result (usually within 1-2 business days)</li>
                </ol>
            </div>
            
            <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; border-left: 4px solid #22c55e; margin: 20px 0;">
                <strong>✅ If Approved:</strong>
                <p style="margin: 10px 0 0 0;">You'll receive a <strong>QR code</strong> via email. Use this QR code to join the queue when you visit City Hall to claim your document.</p>
            </div>
            
            <div style="background: #fef2f2; padding: 15px; border-radius: 8px; border-left: 4px solid #dc2626; margin: 20px 0;">
                <strong>❌ If Disapproved:</strong>
                <p style="margin: 10px 0 0 0;">You'll receive an email explaining which documents need correction. You can then resubmit with the correct documents.</p>
            </div>
            
            <p style="text-align: center; margin-top: 25px; color: #3b82f6; font-weight: bold;">
                Please wait for our review. We'll email you soon!
            </p>
        </div>
        
        <div class="footer">
            <p><strong>North Caloocan City Hall</strong><br>
            Assessor's Office</p>
            <p style="margin: 10px 0;"><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
            <p style="margin: 10px 0;">This is an automated email. Please do not reply.</p>
            <p>For inquiries, visit our office or call during business hours.</p>
        </div>
    </div>
</body>
</html>