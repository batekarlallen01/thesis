<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kiosk Home</title>
    <style>
        .font-georgia { font-family: Georgia, 'Times New Roman', Times, serif; }
        body { font-family: Georgia, 'Times New Roman', Times, serif; background: #dc6c3a; margin: 0; height: 100vh; position: relative; overflow: hidden; }

        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; 
            background-image: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 50%), 
                              radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%); 
            pointer-events: none; }

        .logo { width: 100px; height: 100px; object-fit: contain; margin: 0 auto 1rem; display: block; filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1)); animation: fadeInDown 0.6s ease-out; }
        .card { border: none; border-radius: 28px; padding: 2.5rem 2rem; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 380px; max-width: 480px; width: 100%; position: relative; overflow: hidden; animation: fadeInUp 0.6s ease-out; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
        .card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: #dc6c3a; transform: scaleX(0); transition: transform 0.3s ease; }
        .card:hover::before { transform: scaleX(1); }
        .card:hover { transform: translateY(-12px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .card:active { transform: translateY(-8px); }
        .card-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4rem; justify-content: center; margin-top: 2rem; max-width: 1200px; width: 100%; padding: 0 2rem; }
        .icon { width: 90px; height: 90px; display: flex; align-items: center; justify-content: center; border-radius: 24px; margin-bottom: 1.5rem; position: relative; transition: all 0.3s ease; }
        .icon img { width: 50px; height: 50px; transition: transform 0.3s ease; }
        .btn { padding: 0.8rem 2.5rem; font-weight: bold; font-size: 1rem; border-radius: 12px; transition: all 0.3s ease; margin-top: 1.5rem; width: fit-content; border: none; cursor: pointer; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .btn span { position: relative; z-index: 1; }
        .btn-orange { background: #dc6c3a; color: white; }
        .btn-orange:hover { box-shadow: 0 6px 25px rgba(220,108,58,0.4); transform: translateY(-2px); }
        .card:hover .icon { transform: scale(1.1) rotate(5deg); }
        .card:hover h3 { color: #dc6c3a; }
        h2 { font-size: 2rem; margin-bottom: 0; text-align: center; color: white; font-weight: bold; text-shadow: 0 2px 4px rgba(0,0,0,0.1); animation: fadeIn 0.6s ease-out 0.2s both; }
        h3 { font-size: 1.5rem; margin: 0; color: #1f2937; font-weight: bold; transition: color 0.3s ease; }
        p { font-size: 1rem; color: #6b7280; text-align: center; margin: 0.8rem 0 0 0; line-height: 1.5; }
        #datetime { background: rgba(255,255,255,0.95); padding: 1rem 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-family: 'Times New Roman', Times, serif; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); animation: slideInRight 0.6s ease-out; }
        #datetime div { line-height: 1.4; }
        #datetime .date { font-size: 0.95rem; color: #6b7280; font-weight: 500; }
        #datetime .time { font-size: 1.3rem; color: #1f2937; font-weight: bold; letter-spacing: 0.5px; }

        /* Full-screen queue closed overlay */
        #queue-closed-overlay { 
            display: none; 
            position: fixed; 
            inset: 0; 
            background: linear-gradient(135deg, rgba(220, 108, 58, 0.95) 0%, rgba(185, 85, 45, 0.95) 100%);
            z-index: 9999; 
            color: white; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            text-align: center; 
            padding: 2rem; 
            animation: fadeIn 0.5s ease-out;
            overflow-y: auto;
        }
        #queue-closed-overlay .icon-large {
            font-size: 8rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: pulse 2s ease-in-out infinite;
        }
        #queue-closed-overlay h1 { 
            font-size: 3.5rem; 
            margin-bottom: 1rem; 
            font-weight: bold;
            text-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        #queue-closed-overlay h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: white;
            font-weight: bold;
        }
        #queue-closed-overlay p { 
            font-size: 1.5rem; 
            line-height: 1.8;
            max-width: 700px;
            color: rgba(255,255,255,0.95);
        }
        #queue-closed-overlay .reason {
            background: rgba(255,255,255,0.2);
            padding: 1rem 2rem;
            border-radius: 12px;
            margin-top: 2rem;
            font-size: 1.2rem;
            backdrop-filter: blur(10px);
        }
        .recommendation-box {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            max-width: 600px;
            width: 100%;
        }
        .recommendation-box h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: white;
        }
        .recommendation-box p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.95);
        }
        .recommendation-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }
        .recommendation-item {
            display: flex;
            align-items: start;
            gap: 1rem;
        }
        .recommendation-item span {
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .recommendation-item strong {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 1.05rem;
        }
        .recommendation-item small {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @media (max-width:768px) {
            .card { padding: 2.5rem 2rem; min-height: 380px; max-width: 100%; }
            .card-container { grid-template-columns: 1fr; gap: 2rem; }
            .icon { width: 80px; height: 80px; }
            .icon img { width: 40px; height: 40px; }
            h2 { font-size: 2rem; }
            h3 { font-size: 1.5rem; }
            p { font-size: 1rem; }
            #datetime { padding: 0.75rem 1.25rem; }
            #datetime .time { font-size: 1.1rem; }
            #queue-closed-overlay .icon-large { font-size: 5rem; }
            #queue-closed-overlay h1 { font-size: 2.5rem; }
            #queue-closed-overlay h3 { font-size: 1.1rem; }
            #queue-closed-overlay p { font-size: 1.2rem; }
            .recommendation-box { padding: 1rem; }
            .recommendation-item strong { font-size: 1rem; }
            .recommendation-item small { font-size: 0.9rem; }
        }
    </style>
</head>
<body>

    <!-- Date & Time -->
    <div id="datetime" style="position: absolute; top: 1.5rem; right: 1.5rem; text-align: right; z-index: 50;">
        <div class="date">Loading...</div>
        <div class="time">--:--:--</div>
    </div>

    <!-- Main Content -->
    <main style="flex-grow: 1; max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh;">

        <!-- Logo & Title -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <img src="/img/mainlogo.png" alt="City Hall Logo" class="logo">
            <h2>Choose a service to get started</h2>
        </div>

        <!-- Card Container -->
        <div class="card-container">
            <!-- Scan QR Code Card -->
            <div class="card">
                <div class="icon">
                    <img src="/img/scanner.png" alt="Scan QR Code" style="width: 50px; height: 50px;">
                </div>
                <h3>Scan QR Code</h3>
                <p>Scan your pre-registered QR code to proceed quickly with your transaction.</p>
                <button onclick="checkCutOff('/queue/scanner')" class="btn btn-orange"><span>Start Scanning</span></button>
            </div>

            <!-- Kiosk Application Card -->
            <div class="card">
                <div class="icon">
                    <img src="/img/kiosk.png" alt="Kiosk Application" style="width: 50px; height: 50px;">
                </div>
                <h3>Kiosk Application</h3>
                <p>Apply directly using the kiosk for walk-in services and assistance.</p>
                <button onclick="checkCutOff('/kioskform')" class="btn btn-orange"><span>Start Application</span></button>
            </div>
        </div>
    </main>

    <!-- Queue Closed Overlay -->
    <div id="queue-closed-overlay">
        <div class="icon-large">🚫</div>
        <h1>Queue Closed</h1>
        <p id="closed-message">Today's queue is now closed.<br>Please come back tomorrow.</p>
        <div class="reason" id="closed-reason"></div>
        
        <!-- Recommendation Section -->
        <div class="recommendation-box">
            <h3>💡 Skip the Queue Tomorrow!</h3>
            <p>Use our <strong>Online Pre-Registration</strong> system to save time:</p>
            <div class="recommendation-list">
                <div class="recommendation-item">
                    <span>🌐</span>
                    <div>
                        <strong>Visit our website:</strong>
                        <small>Search "North Caloocan City Hall Pre-Registration"</small>
                    </div>
                </div>
                <div class="recommendation-item">
                    <span>📱</span>
                    <div>
                        <strong>Scan QR Code:</strong>
                        <small>Look for QR codes posted around the office</small>
                    </div>
                </div>
                <div class="recommendation-item">
                    <span>✅</span>
                    <div>
                        <strong>Benefits:</strong>
                        <small>Register from home, upload documents in advance, receive QR code via email</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Date & Time
    function updateDateTime() {
        const now = new Date();
        const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        document.querySelector('#datetime .date').textContent = now.toLocaleDateString(undefined, dateOptions);
        document.querySelector('#datetime .time').textContent = now.toLocaleTimeString();
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // Cut-off check function
    async function checkCutOff(redirectUrl) {
        try {
            const res = await fetch('/queue/check');
            const data = await res.json();

            if (!data.allowed) {
                showClosedOverlay(data);
                return;
            }

            // Proceed if allowed
            window.location.href = redirectUrl;
        } catch(err) {
            console.error('Queue check error:', err);
            alert("Unable to check queue status. Please try again.");
        }
    }

    // Show the closed overlay with appropriate message
    function showClosedOverlay(data) {
        const overlay = document.getElementById('queue-closed-overlay');
        const messageEl = document.getElementById('closed-message');
        const reasonEl = document.getElementById('closed-reason');

        // Customize message based on reason
        if (data.reason === 'capacity') {
            messageEl.innerHTML = "Today's queue is full (100 applicants reached).<br>Please come back tomorrow.";
            reasonEl.textContent = "📊 Daily capacity limit reached";
        } else if (data.reason === 'time_cutoff') {
            messageEl.innerHTML = "Queue has closed for today (4:45 PM cutoff).<br>Please come back tomorrow.";
            reasonEl.textContent = "⏰ Operating hours ended";
        } else if (data.reason === 'early_cutoff') {
            messageEl.innerHTML = "Queue closed early due to high volume.<br>Please come back tomorrow.";
            reasonEl.textContent = "⚠️ Early closure - high demand";
        } else {
            messageEl.innerHTML = data.message || "Queue is closed for today.<br>Please come back tomorrow.";
            reasonEl.textContent = "";
        }

        overlay.style.display = 'flex';
    }

    // Auto-check queue status every 5 seconds
    async function autoCheckQueue() {
        try {
            const res = await fetch('/queue/check');
            const data = await res.json();
            
            if (!data.allowed) {
                showClosedOverlay(data);
            }
        } catch(err) {
            console.error('Auto queue check error:', err);
        }
    }

    // Initial check on page load
    autoCheckQueue();
    
    // Repeat check every 5 seconds
    setInterval(autoCheckQueue, 5000);
    </script>
</body>
</html><?php /**PATH C:\Users\kiosk\Documents\thesis\resources\views/User/Kiosk/kioskhome.blade.php ENDPATH**/ ?>