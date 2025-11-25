<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .qr-container {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
        }
        .qr-code {
            max-width: 300px;
            height: auto;
            margin: 20px auto;
            border: none;
        }
        .success-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 16px;
            margin: 10px 0;
        }
        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .instructions {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">North Caloocan City Hall - Assessors Department</h1>
     <img src="<?php echo e($message->embed(public_path('img/mainlogo.png'))); ?>" 
     alt="North Caloocan City Hall"
     style="width: 80px; height: 80px; display: block; margin: 10px auto;">
        <p style="margin: 10px 0 0 0; opacity: 0.9;">✅ Application Approved!</p>
    </div>

    <div class="content">
        <p>Dear <strong><?php echo e($preReg->full_name); ?></strong>,</p>

        <div class="alert-success">
            <strong>🎉 Great news!</strong> Your pre-registration has been approved.
        </div>

        <p>Your application for <strong><?php echo e(ucwords(str_replace('_', ' ', $preReg->service_type))); ?></strong> has been reviewed and approved.</p>

        <div class="qr-container">
    <h3 style="color: #059669;">Your Queue QR Code</h3>
    <p>Show this QR code when you arrive at City Hall to enter the queue:</p>
    
    <?php if($preReg->qr_image_path): ?>
        <img src="<?php echo e($message->embed(storage_path('app/public/qrcodes/' . $preReg->qr_image_path))); ?>" alt="QR Code" class="qr-code">
    <?php else: ?>
        <p style="color: #dc2626; font-weight: bold;">
            ⚠️ Unable to load QR code. Please contact support.
        </p>
    <?php endif; ?>
    
    <p style="color: #dc2626; font-weight: bold; margin-top: 20px;">
        ⏰ Valid for 24 hours
    </p>
</div>

        <div class="instructions">
            <h3 style="color: #059669;">Instructions:</h3>
            <ol>
                <li><strong>Save this email</strong> or screenshot the QR code</li>
                <li><strong>Visit City Hall</strong> within 24 hours</li>
                <li><strong>Present your QR code</strong> at the registration desk</li>
                <li><strong>Wait for your turn</strong> — you'll be called via the queue system</li>
            </ol>
        </div>

        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>Bring all original documents for verification</li>
            <li>QR code can only be used once</li>
            <li>Arrive during office hours: Monday–Friday, 8:00 AM – 5:00 PM</li>
        </ul>

        <p style="margin-top: 30px;">See you soon at City Hall!</p>

        <p>Best regards,<br>
        <strong>North Caloocan City Hall</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; <?php echo e(date('Y')); ?> North Caloocan City Hall. All rights reserved.</p>
    </div>
</body>
</html><?php /**PATH C:\Users\kiosk\Documents\thesis\resources\views/emails/pre-registration-approved.blade.php ENDPATH**/ ?>