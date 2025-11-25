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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .reasons-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
        }
        .reason-item {
            padding: 10px;
            margin: 5px 0;
            background: #fef2f2;
            border-radius: 4px;
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
             <p style="margin: 10px 0 0 0; opacity: 0.9;">❌ Application Needs Revision</p>
    </div>

    <div class="content">
        <p>Dear <strong><?php echo e($preReg->full_name); ?></strong>,</p>

        <div class="alert-error">
            <strong>⚠️ Action Required:</strong> Your pre-registration needs to be revised.
        </div>

        <p>We've reviewed your application for <strong><?php echo e(ucwords(str_replace('_', ' ', $preReg->service_type))); ?></strong>, but unfortunately we cannot proceed due to the following issues:</p>

        <div class="reasons-box">
            <h3 style="margin-top: 0; color: #dc2626;">Issues Found:</h3>
            
            <?php if(!empty($incorrectDocuments)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php $__currentLoopData = $incorrectDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="reason-item">❌ <?php echo e($doc); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>

            <?php if($otherReason): ?>
                <div style="margin-top: 15px; padding: 15px; background: #fef2f2; border-radius: 4px;">
                    <strong>Additional Notes:</strong>
                    <p style="margin: 5px 0 0 0;"><?php echo e($otherReason); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <h3>What to do next:</h3>
        <ol>
            <li><strong>Review the issues</strong> listed above</li>
            <li><strong>Prepare correct documents</strong> according to requirements</li>
            <li><strong>Submit a new application</strong> with the corrected information</li>
        </ol>

        <p style="background: #dbeafe; padding: 15px; border-radius: 4px; border-left: 4px solid #3b82f6;">
            <strong>💡 Tip:</strong> Make sure all documents are clear, complete, and match the requirements before resubmitting.
        </p>

        <p style="margin-top: 30px;">If you have questions about the required documents, please contact City Hall for assistance.</p>

        <p>Best regards,<br>
        <strong>North Caloocan City Hall</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; <?php echo e(date('Y')); ?> North Caloocan City Hall. All rights reserved.</p>
    </div>
</body>
</html><?php /**PATH C:\Users\kiosk\Documents\thesis\resources\views/emails/pre-registration-disapproved.blade.php ENDPATH**/ ?>