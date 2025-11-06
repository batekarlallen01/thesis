<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>QR Scanner - Queue Entry</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
            background: #dc6c3a;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }
        
        /* Animated background pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 28px;
            padding: 4rem 3.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            color: #333;
            position: relative;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: #dc6c3a;
            border-radius: 28px 28px 0 0;
        }
        
        .logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeInDown 0.6s ease-out;
        }
        
        .logo img {
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #dc6c3a;
            font-weight: bold;
        }
        
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .status-box {
            background: #f0f4ff;
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            border-left: 4px solid #dc6c3a;
        }
        
        .status-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }
        
        .scanning {
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }
        
        .success-box {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .error-box {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .queue-info {
            background: #f9fafb;
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .queue-number {
            font-size: 3.5rem;
            font-weight: bold;
            color: #dc6c3a;
            margin: 10px 0;
        }
        
        .priority-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
            font-size: 1rem;
        }
        
        .priority-pwd {
            background: #cfe2ff;
            color: #084298;
        }
        
        .priority-senior {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .priority-regular {
            background: #e2e3e5;
            color: #41464b;
        }
        
        .btn {
            background: #dc6c3a;
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(220, 108, 58, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            box-shadow: 0 6px 25px rgba(220, 108, 58, 0.4);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn span {
            position: relative;
            z-index: 1;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #dc6c3a;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        #scanner-input {
            position: absolute;
            left: -9999px;
            opacity: 0;
            width: 1px;
            height: 1px;
        }
        
        .instruction {
            background: #fff3cd;
            color: #856404;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
            font-size: 1rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 2.5rem 2rem;
            }
            
            .logo {
                width: 90px;
                height: 90px;
                font-size: 45px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .subtitle {
                font-size: 1rem;
            }
            
            .status-icon {
                font-size: 48px;
            }
            
            .queue-number {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('img/scanner.png') }}" alt="QR Scanner" style="width: 70px; height: 70px;">
        </div>
        <h1>QR Code Scanner</h1>
        <p class="subtitle">Onsite Queue Entry System</p>
        
        <div id="status-area">
            <div class="status-box">
                <div class="status-icon scanning">
                    <img src="{{ asset('img/scanner.png') }}" alt="Scanning" style="width: 64px; height: 64px;">
                </div>
                <p><strong>Ready to Scan</strong></p>
                <p>Please present your QR code to the scanner</p>
            </div>
            
            <div class="instruction">
                💡 The scanner will automatically detect and process your QR code
            </div>
        </div>
        
        <input type="text" id="scanner-input" autocomplete="off" />
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const statusArea = document.getElementById('status-area');
        const scannerInput = document.getElementById('scanner-input');
        
        let buffer = '';
        let timeout;
        let isProcessing = false;

        // Keep input focused
        function maintainFocus() {
            if (document.activeElement !== scannerInput) {
                scannerInput.focus();
            }
        }
        
        setInterval(maintainFocus, 300);
        scannerInput.focus();

        // Handle keyboard input from scanner
        document.addEventListener('keydown', (e) => {
            // Don't interfere with normal form inputs if any exist
            if (e.target.tagName === 'BUTTON') return;
            
            if (isProcessing) {
                return;
            }
            
            if (e.key === 'Enter' && buffer.trim()) {
                e.preventDefault();
                const scannedData = buffer.trim();
                processScannedData(scannedData);
                buffer = '';
            } else if (e.key.length === 1) {
                buffer += e.key;
            }

            clearTimeout(timeout);
            timeout = setTimeout(() => { 
                buffer = ''; 
            }, 1000);
        });

        async function processScannedData(scannedData) {
            isProcessing = true;
            
            // Extract QR token from URL or use data directly
            let qrToken = extractToken(scannedData);
            
            if (!qrToken) {
                showError('Invalid QR code format. Could not extract token.');
                isProcessing = false;
                return;
            }

            showProcessing();

            try {
                // Step 1: Validate QR token
                const validateResponse = await fetch('/api/qr/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_token: qrToken })
                });
                
                const validateData = await validateResponse.json();

                if (!validateData.success) {
                    showError(validateData.message || 'QR code validation failed');
                    isProcessing = false;
                    return;
                }

                // Step 2: Process entry into queue
                const entryResponse = await fetch('/api/qr/entry', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_token: qrToken })
                });
                
                const entryData = await entryResponse.json();

                if (entryData.success) {
                    showSuccess(entryData.data);
                } else {
                    showError(entryData.message || 'Failed to enter queue');
                }

            } catch (error) {
                console.error('Processing error:', error);
                showError('Network error. Please try again.');
            } finally {
                setTimeout(() => {
                    isProcessing = false;
                }, 5000);
            }
        }

        function extractToken(data) {
            // Method 1: Extract from /queue/scan/{token} URL pattern
            if (data.includes('/queue/scan/')) {
                const match = data.match(/\/queue\/scan\/([a-zA-Z0-9]+)/);
                if (match && match[1]) {
                    return match[1];
                }
            }
            
            // Method 2: Check if it's already just the token (32 alphanumeric characters)
            if (/^[a-zA-Z0-9]{32}$/.test(data)) {
                return data;
            }
            
            // Method 3: Try to extract token from any URL with 'token' parameter
            try {
                const url = new URL(data);
                const tokenParam = url.searchParams.get('token');
                if (tokenParam) {
                    return tokenParam;
                }
            } catch (e) {
                // Not a valid URL, continue
            }
            
            return null;
        }

        function showProcessing() {
            statusArea.innerHTML = `
                <div class="status-box">
                    <div class="spinner"></div>
                    <p><strong>Processing QR Code...</strong></p>
                    <p>Please wait</p>
                </div>
            `;
        }

        function showSuccess(data) {
            const priorityClass = data.priority_type === 'PWD' ? 'priority-pwd' : 
                                 data.priority_type === 'Senior' ? 'priority-senior' : 
                                 'priority-regular';
            
            const priorityIcon = data.priority_type === 'PWD' ? '♿' : 
                                data.priority_type === 'Senior' ? '👴' : '👤';
            
            statusArea.innerHTML = `
                <div class="status-box success-box">
                    <div class="status-icon">✅</div>
                    <p><strong>Successfully Added to Queue!</strong></p>
                </div>
                
                <div class="queue-info">
                    <p style="color: #666; margin-bottom: 10px;">Queue Number</p>
                    <div class="queue-number">${data.queue_number}</div>
                    
                    <p style="color: #666; margin-top: 20px;"><strong>${data.full_name}</strong></p>
                    <p style="color: #888; font-size: 0.9rem;">${data.service_type}</p>
                    
                    <div class="priority-badge ${priorityClass}">
                        ${priorityIcon} ${data.priority_type} Priority
                    </div>
                </div>
                
                <button class="btn" onclick="resetScanner()"><span>Scan Next Person</span></button>
            `;
        }

        function showError(message) {
            statusArea.innerHTML = `
                <div class="status-box error-box">
                    <div class="status-icon">❌</div>
                    <p><strong>Error</strong></p>
                    <p>${message}</p>
                </div>
                
                <button class="btn" onclick="resetScanner()"><span>Try Again</span></button>
            `;
        }

        function resetScanner() {
            statusArea.innerHTML = `
                <div class="status-box">
                    <div class="status-icon scanning">
                        <img src="{{ asset('img/scanner.png') }}" alt="Scanning" style="width: 64px; height: 64px;">
                    </div>
                    <p><strong>Ready to Scan</strong></p>
                    <p>Please present your QR code to the scanner</p>
                </div>
                
                <div class="instruction">
                    💡 The scanner will automatically detect and process your QR code
                </div>
            `;
            buffer = '';
            scannerInput.value = '';
            scannerInput.focus();
            isProcessing = false;
        }
        
        // Expose resetScanner globally for button onclick
        window.resetScanner = resetScanner;
    </script>
</body>
</html>