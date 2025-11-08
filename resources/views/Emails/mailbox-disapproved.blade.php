<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 10px 0 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .badge { display: inline-block; background: #dc2626; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .reason-box { background: #fef2f2; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #dc2626; }
        .reason-box h3 { color: #991b1b; margin-top: 0; }
        .info-box { background: #f9fafb; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #6b7280; }
        .info-box strong { color: #1f2937; }
        .resubmit-box { background: #eff6ff; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6; margin: 20px 0; }
        .resubmit-box h3 { color: #1e40af; margin-top: 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Submission Disapproved</h1>
            <p>North Caloocan City Hall - IoT Mailbox System</p>
        </div>
        
        <div class="content">
            <span class="badge">DISAPPROVED</span>
            
            <p style="font-size: 16px; margin-top: 15px;">Dear <strong>{{ $submission->full_name }}</strong>,</p>
            
            <p>We regret to inform you that your document submission has been <strong>disapproved</strong> after physical verification.</p>
            
            <div class="reason-box">
                <h3>📋 Reason for Disapproval:</h3>
                <p style="font-size: 15px; line-height: 1.8;">
                    <strong>Incomplete or incorrect requirements submitted.</strong>
                </p>
                <p style="margin-top: 15px; font-size: 14px;">
                    After reviewing your documents, our staff found that the submitted requirements do not meet the criteria for processing. This may include:
                </p>
                <ul style="margin: 10px 0; padding-left: 20px; font-size: 14px;">
                    <li>Missing required documents</li>
                    <li>Incomplete information on forms</li>
                    <li>Documents not properly authenticated</li>
                    <li>Expired or invalid identification</li>
                    <li>Incorrect document type submitted</li>
                </ul>
            </div>
            
            <div class="info-box">
                <strong>📋 Service Requested:</strong> {{ $submission->service_type_name }}<br>
                <strong>👤 Applicant:</strong> {{ ucfirst($submission->applicant_type) }}<br>
                <strong>📧 PIN Code:</strong> {{ $submission->pin_code }}<br>
                <strong>📅 Disapproved:</strong> {{ $submission->disapproved_at->format('M d, Y h:i A') }}
            </div>
            
            <div class="resubmit-box">
                <h3>🔄 What to Do Next:</h3>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Review the requirements</strong> for {{ $submission->service_type_name }}</li>
                    <li><strong>Prepare the complete documents</strong> as specified</li>
                    <li><strong>Submit a new application</strong> through our online portal</li>
                    <li><strong>Use the new PIN code</strong> to drop off corrected documents</li>
                </ol>
                <p style="margin-top: 15px; font-size: 14px;">
                    <strong>Need help?</strong> Visit our office during business hours for assistance in preparing your documents.
                </p>
            </div>
            
            <div style="background: #fffbeb; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 20px 0;">
                <strong>💡 Tip:</strong> You can visit our office to inquire about the specific documents that need correction before resubmitting.
            </div>
            
            <p style="text-align: center; margin-top: 25px; color: #6b7280;">
                We apologize for any inconvenience. Thank you for your understanding.
            </p>
        </div>
        
        <div class="footer">
            <p><strong>North Caloocan City Hall</strong><br>
            Assessor's Office - IoT Mailbox System</p>
            <p style="margin: 10px 0;"><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
            <p style="margin: 10px 0;">This is an automated email. Please do not reply.</p>
            <p>For inquiries, visit our office or call during business hours.</p>
        </div>
    </div>
</body>
</html>