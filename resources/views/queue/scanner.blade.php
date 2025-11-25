<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">
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
        
        /* Back Button */
        .back-button {
            position: fixed;
            top: 2rem;
            left: 2rem;
            background: white;
            color: #dc6c3a;
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .back-button:active {
            transform: translateY(0);
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
        }
        
        .btn:hover {
            box-shadow: 0 6px 25px rgba(220, 108, 58, 0.4);
            transform: translateY(-2px);
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
        
        .debug-info {
            background: #e7f3ff;
            border: 2px solid #2196F3;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            font-family: monospace;
            font-size: 0.85rem;
            color: #0d47a1;
            word-break: break-all;
        }
        
        /* Idle Timer Display */
        .idle-timer {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: rgba(255,255,255,0.9);
            color: #dc6c3a;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
        }
        
        .idle-timer.warning {
            background: #fff3cd;
            color: #856404;
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
            .back-button {
                top: 1rem;
                left: 1rem;
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
            }
            
            .container {
                padding: 2.5rem 2rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .queue-number {
                font-size: 2.5rem;
            }
            
            .idle-timer {
                bottom: 1rem;
                right: 1rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <button class="back-button" onclick="goBackHome()">
        ← Back to Home
    </button>
    
    <!-- Idle Timer Display -->
    <div class="idle-timer" id="idle-timer">
        Returning to home in <span id="countdown">60</span>s
    </div>
    
    <div class="container">
        <div class="logo">
            📱
        </div>
        <h1>QR Code Scanner</h1>
        <p class="subtitle">Onsite Queue Entry System</p>
        
        <div id="status-area">
            <div class="status-box">
                <div class="status-icon scanning">📱</div>
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
        const idleTimerEl = document.getElementById('idle-timer');
        const countdownEl = document.getElementById('countdown');
        
        let buffer = '';
        let timeout;
        let isProcessing = false;
        
        // Idle timer configuration
        const IDLE_TIMEOUT = 60000; // 2 minutes in milliseconds
        const WARNING_TIME = 30000; // Show warning at 30 seconds remaining
        let idleTimer = null;
        let countdownInterval = null;
        let remainingTime = 60;

        // Initialize idle timer
        function startIdleTimer() {
            clearIdleTimer();
            remainingTime = 60;
            idleTimerEl.style.display = 'none';
            idleTimerEl.classList.remove('warning');
            
            idleTimer = setTimeout(() => {
                console.log('⏰ Idle timeout - returning to home');
                goBackHome();
            }, IDLE_TIMEOUT);
        }
        
        function clearIdleTimer() {
            if (idleTimer) {
                clearTimeout(idleTimer);
                idleTimer = null;
            }
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            idleTimerEl.style.display = 'none';
        }
        
        function resetIdleTimer() {
            startIdleTimer();
        }
        
        // Show countdown in last 30 seconds
        function showCountdown() {
            remainingTime = 30;
            idleTimerEl.style.display = 'block';
            idleTimerEl.classList.add('warning');
            countdownEl.textContent = remainingTime;
            
            countdownInterval = setInterval(() => {
                remainingTime--;
                countdownEl.textContent = remainingTime;
                
                if (remainingTime <= 0) {
                    clearInterval(countdownInterval);
                }
            }, 1000);
        }
        
        // Start countdown 30 seconds before timeout
        setTimeout(() => {
            showCountdown();
        }, IDLE_TIMEOUT - WARNING_TIME);
        
        // Reset timer on any user interaction
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetIdleTimer, true);
        });
        
        // Start initial timer
        startIdleTimer();

        // Keep input focused
        function maintainFocus() {
            if (document.activeElement !== scannerInput) {
                scannerInput.focus();
            }
        }
        
        setInterval(maintainFocus, 300);
        scannerInput.focus();

        // Go back to home function
        function goBackHome() {
            window.location.href = '/kiosk';
        }
        
        // Expose to window for button onclick
        window.goBackHome = goBackHome;

        // Handle keyboard input from scanner
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'BUTTON') return;
            
            if (isProcessing) {
                return;
            }
            
            if (e.key === 'Enter' && buffer.trim()) {
                e.preventDefault();
                const scannedData = buffer.trim();
                console.log('📱 SCANNER INPUT:', scannedData);
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
            resetIdleTimer(); // Reset timer on scan
            
            // Extract QR token from URL or use data directly
            let qrToken = extractToken(scannedData);
            
            console.log('🔍 EXTRACTED TOKEN:', qrToken);
            
            if (!qrToken) {
                showError('Invalid QR code format. Could not extract token.', scannedData);
                isProcessing = false;
                return;
            }

            showProcessing();

            try {
                // Step 1: Validate QR token
                console.log('✅ Step 1: Validating token...');
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
                console.log('Validation response:', validateData);

                if (!validateData.success) {
                    showError(validateData.message || 'QR code validation failed');
                    isProcessing = false;
                    return;
                }

                // Step 2: Process entry into queue
                console.log('✅ Step 2: Processing queue entry...');
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
                console.log('Entry response:', entryData);

                if (entryData.success) {
                    showSuccess(entryData.data);
                    // Auto return to home after 5 seconds on success
                    setTimeout(() => {
                        console.log('✅ Successful scan - returning to home');
                        goBackHome();
                    }, 5000);
                } else {
                    showError(entryData.message || 'Failed to enter queue');
                }

            } catch (error) {
                console.error('❌ Processing error:', error);
                showError('Network error. Please try again.');
            } finally {
                setTimeout(() => {
                    isProcessing = false;
                }, 5000);
            }
        }

        function extractToken(data) {
            console.log('🔎 Extracting token from:', data);
            console.log('   Length:', data.length);
            
            // Method 1: Extract from /queue/scan/{token} URL pattern
            if (data.includes('/queue/scan/')) {
                const match = data.match(/\/queue\/scan\/([a-zA-Z0-9]+)/);
                if (match && match[1]) {
                    console.log('✅ Token extracted from URL pattern:', match[1]);
                    return match[1];
                }
            }
            
            // Method 2: Check if it's already just the token (allow 20-64 chars)
            if (/^[a-zA-Z0-9]{20,64}$/.test(data)) {
                console.log('✅ Direct token detected:', data);
                return data;
            }
            
            // Method 3: Try to parse as full URL
            try {
                const url = new URL(data);
                console.log('   Parsed as URL - pathname:', url.pathname);
                
                // Extract from path
                const pathMatch = url.pathname.match(/\/queue\/scan\/([a-zA-Z0-9]+)/);
                if (pathMatch && pathMatch[1]) {
                    console.log('✅ Token from URL path:', pathMatch[1]);
                    return pathMatch[1];
                }
                
                // Try query parameter
                const tokenParam = url.searchParams.get('token');
                if (tokenParam) {
                    console.log('✅ Token from query param:', tokenParam);
                    return tokenParam;
                }
            } catch (e) {
                console.log('   Not a valid URL, checking other patterns...');
            }
            
            console.error('❌ Could not extract token from:', data);
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
                    <p style="font-size: 0.9rem; margin-top: 10px; color: #0f5132;">Returning to home in 5 seconds...</p>
                </div>
                
                <div class="queue-info">
                    <p style="color: #666; margin-bottom: 10px;">Queue Number</p>
                    <div class="queue-number">${data.queue_number}</div>
                    
                    <p style="color: #666; margin-top: 20px;"><strong>${data.full_name}</strong></p>
                    
                    <div class="priority-badge ${priorityClass}">
                        ${priorityIcon} ${data.priority_type} Priority
                    </div>
                </div>
                
                <button class="btn" onclick="goBackHome()">Return to Home Now</button>
            `;
        }

        function showError(message, debugData = null) {
            let debugHtml = '';
            if (debugData) {
                debugHtml = `
                    <div class="debug-info">
                        <strong>Debug Info:</strong><br>
                        Scanned Data: ${debugData}<br>
                        Length: ${debugData.length} characters
                    </div>
                `;
            }
            
            statusArea.innerHTML = `
                <div class="status-box error-box">
                    <div class="status-icon">❌</div>
                    <p><strong>Error</strong></p>
                    <p>${message}</p>
                </div>
                ${debugHtml}
                <button class="btn" onclick="resetScanner()">Try Again</button>
            `;
        }

        function resetScanner() {
            statusArea.innerHTML = `
                <div class="status-box">
                    <div class="status-icon scanning">📱</div>
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
            resetIdleTimer();
        }
        
        // Expose resetScanner globally for button onclick
        window.resetScanner = resetScanner;
        
        console.log('🚀 QR Scanner initialized and ready');
        console.log('📍 CSRF Token:', csrfToken ? 'Present' : 'Missing');
        console.log('⏰ Idle timeout: 60 seconds');
    </script>
</body>
</html>