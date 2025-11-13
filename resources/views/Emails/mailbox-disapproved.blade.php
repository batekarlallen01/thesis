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
        .incorrect-list { list-style: none; padding: 0; margin: 15px 0; }
        .incorrect-list li { padding: 10px; margin: 8px 0; background: white; border-left: 3px solid #dc2626; border-radius: 4px; display: flex; align-items: center; }
        .incorrect-list li:before { content: "✗"; color: #dc2626; font-weight: bold; margin-right: 10px; font-size: 18px; }
        .info-box { background: #f9fafb; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #6b7280; }
        .info-box strong { color: #1f2937; }
        .resubmit-box { background: #eff6ff; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6; margin: 20px 0; }
        .resubmit-box h3 { color: #1e40af; margin-top: 0; }
        .other-reason-box { background: #fffbeb; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #f59e0b; }
        .other-reason-box strong { color: #92400e; }
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
                <p style="font-size: 15px; line-height: 1.8; margin-bottom: 10px;">
                    <strong>The following documents are incorrect, incomplete, or missing:</strong>
                </p>
                
                @if(!empty($incorrectDocuments))
                <ul class="incorrect-list">
                    @foreach($incorrectDocuments as $doc)
                    <li>{{ $doc }}</li>
                    @endforeach
                </ul>
                @endif
                
                @if(!empty($otherReason))
                <div class="other-reason-box">
                    <strong>📝 Additional Notes:</strong>
                    <p style="margin: 10px 0 0 0; line-height: 1.6;">{{ $otherReason }}</p>
                </div>
                @endif
            </div>
            
            <div class="info-box">
                <strong>📋 Service Requested:</strong> {{ $submission->service_type_name }}<br>
                <strong>👤 Applicant Type:</strong> {{ ucfirst($submission->applicant_type) }}<br>
                <strong>🔢 PIN Code:</strong> {{ $submission->pin_code }}<br>
                <strong>📅 Disapproved:</strong> {{ $submission->disapproved_at->format('M d, Y h:i A') }}
            </div>
            
            <div class="resubmit-box">
                <h3>🔄 What to Do Next:</h3>
                <ol style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
                    <li><strong>Review the list above</strong> to identify which documents need correction</li>
                    <li><strong>Prepare the correct documents</strong> according to the requirements</li>
                    <li><strong>Ensure all documents are:</strong>
                        <ul style="margin: 5px 0; padding-left: 20px;">
                            <li>Complete and properly filled out</li>
                            <li>Properly authenticated or notarized (if required)</li>
                            <li>Valid and not expired</li>
                            <li>Clear and legible copies</li>
                        </ul>
                    </li>
                    <li><strong>Submit a new application</strong> through our online portal</li>
                    <li><strong>Use the new PIN code</strong> to drop off corrected documents</li>
                </ol>
            </div>
            
            <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; border-left: 4px solid #22c55e; margin: 20px 0;">
                <strong>💡 Need Assistance?</strong>
                <p style="margin: 10px 0 0 0;">Visit our office during business hours for guidance on preparing the correct documents. Our staff will be happy to help you understand the requirements.</p>
            </div>
            
            <p style="text-align: center; margin-top: 25px; color: #6b7280; font-size: 14px;">
                We apologize for any inconvenience. Please ensure all documents are correct before resubmitting.<br>
                Thank you for your understanding and cooperation.
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