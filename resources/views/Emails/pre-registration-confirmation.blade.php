<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #1e40af;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            margin: 10px 0;
        }
        .qr-code {
            margin: 20px 0;
            text-align: center;
        }
        .qr-code img {
            max-width: 200px;
            height: auto;
            border: 1px solid #ddd;
            padding: 4px;
        }
        .note {
            background-color: #fefcbf;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
            border-left: 4px solid #d97706;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #666666;
            text-align: center;
        }
        .footer strong {
            color: #1e40af;
        }
        .top-link {
            text-align: right;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .top-link a {
            color: #1e40af;
            text-decoration: none;
            font-weight: bold;
        }
        .highlight {
            background-color: #eff6ff;
            padding: 3px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- View in browser -->
        <div class="top-link">
            <a href="{{ route('queue.scan', ['token' => $preReg->qr_token]) }}" target="_blank">▶️ View & Enter Queue Online</a>
        </div>

        <h2>Hello {{ $preReg->full_name }},</h2>

        <p>Thank you for submitting your <strong>{{ ucfirst(str_replace('_', ' ', $preReg->service_type)) }}</strong> application.</p>

        <p><strong>Application ID:</strong> <span class="highlight">{{ $preReg->id }}</span></p>

        <p><strong>Submitted On:</strong> {{ now()->format('F d, Y - h:i A') }}</p>

        <p>Please present the QR code below at North Caloocan City Hall to proceed with your application:</p>

        <div class="qr-code">
            <img src="{{ $message->embed(storage_path('app/public/qrcodes/' . $preReg->qr_image_path)) }}" alt="QR Code" width="200">
        </div>

        <p>This QR code is also attached to this email for your reference. It is valid until 
            <strong>{{ \Carbon\Carbon::parse($preReg->qr_expires_at)->format('F d, Y - h:i A') }}</strong>.
        </p>

        <div class="note">
            <p><strong>Important:</strong> Ensure you bring all required documents listed in the 
                <strong>{{ ucfirst(str_replace('_', ' ', $preReg->service_type)) }}</strong> checklist to City Hall, along with this QR code.</p>
        </div>

        <div class="footer">
            <p>Regards,<br><strong>North Caloocan City Hall</strong></p>
            <p>If you have any questions, contact us at <a href="mailto:support@ncchad.gov.ph">support@ncchad.gov.ph</a>.</p>
        </div>
    </div>
</body>
</html>