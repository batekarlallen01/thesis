<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kiosk Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-georgia { 
            font-family: Georgia, 'Times New Roman', Times, serif; 
        }
        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
            background: #dc6c3a;
            margin: 0;
            min-height: 100vh;
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
        
        .logo {
            width: 140px;
            height: 140px;
            object-fit: contain;
            margin: 0 auto 1.5rem;
            display: block;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            animation: fadeInDown 0.6s ease-out;
        }
        
        .card {
            border: none;
            border-radius: 28px;
            padding: 4rem 3.5rem;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 520px;
            max-width: 480px;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: #dc6c3a;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .card:hover::before {
            transform: scaleX(1);
        }
        
        .card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .card:active {
            transform: translateY(-8px);
        }
        
        .card-container {
            display: flex;
            gap: 3rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .icon {
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            margin-bottom: 2.5rem;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .icon::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 20px;
            padding: 4px;
            background: linear-gradient(135deg, rgba(220, 108, 58, 0.2), rgba(220, 108, 58, 0.2));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .card:hover .icon::after {
            opacity: 1;
        }
        
        .card:hover .icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .icon img {
            width: 64px;
            height: 64px;
            transition: transform 0.3s ease;
        }
        
        .card:hover .icon img {
            transform: scale(1.1);
        }
        
        .btn {
            padding: 1rem 3rem;
            font-weight: bold;
            font-size: 1.1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-top: 2rem;
            width: fit-content;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
        
        .btn span {
            position: relative;
            z-index: 1;
        }
        
        .btn-orange {
            background: #dc6c3a;
            color: white;
        }
        
        .btn-orange:hover {
            box-shadow: 0 6px 25px rgba(220, 108, 58, 0.4);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        h2 {
            font-size: 2.5rem;
            margin-bottom: 0;
            text-align: center;
            color: white;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.6s ease-out 0.2s both;
        }
        
        h3 {
            font-size: 1.75rem;
            margin: 0;
            color: #1f2937;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        
        .card:hover h3 {
            color: #dc6c3a;
        }
        
        p {
            font-size: 1.1rem;
            color: #6b7280;
            text-align: center;
            margin: 1rem 0 0 0;
            line-height: 1.6;
        }

        #datetime {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-family: 'Times New Roman', Times, serif;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            animation: slideInRight 0.6s ease-out;
        }
        
        #datetime div {
            line-height: 1.4;
        }
        
        #datetime .date {
            font-size: 0.95rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        #datetime .time {
            font-size: 1.3rem;
            color: #1f2937;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
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
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .card:nth-child(2) {
            animation-delay: 0.1s;
        }

        @media (max-width: 768px) {
            .card {
                padding: 2.5rem 2rem;
                min-height: 380px;
                max-width: 100%;
            }
            .card-container {
                flex-direction: column;
                gap: 2rem;
            }
            .icon {
                width: 80px;
                height: 80px;
            }
            .icon img {
                width: 40px;
                height: 40px;
            }
            h2 {
                font-size: 2rem;
            }
            h3 {
                font-size: 1.5rem;
            }
            p {
                font-size: 1rem;
            }
            #datetime {
                padding: 0.75rem 1.25rem;
            }
            #datetime .time {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <!-- Time and Date Display (Top Right Corner) -->
    <div id="datetime" class="absolute top-6 right-6 text-right z-50">
        <div class="date">Loading...</div>
        <div class="time">--:--:--</div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-6 py-12 sm:px-8 lg:px-12 max-w-6xl flex flex-col items-center justify-center">

        <!-- Logo & Title -->
        <div class="text-center mb-10">
            <img 
                src="{{ asset('img/mainlogo.png') }}" 
                alt="City Hall Logo" 
                class="logo"
            >
            <h2 class="text-4xl font-georgia font-bold">Choose a service to get started</h2>
        </div>

        <!-- Card Container -->
        <div class="card-container">
            <!-- Scan QR Code Card -->
            <div class="card">
                <div class="icon">
                    <img src="{{ asset('img/scanner.png') }}" alt="Scan QR Code" style="width: 55px; height: 55px;">
                </div>
                <h3>Scan QR Code</h3>
                <p>Scan your pre-registered QR code to proceed quickly with your transaction.</p>
                <button onclick="window.location.href='/queue/scanner'" class="btn btn-orange"><span>Start Application</span>
            </div>

            <!-- Kiosk Application Card -->
            <div class="card">
                <div class="icon">
                    <img src="{{ asset('img/kiosk.png') }}"  alt="Kiosk Application" style="width: 55px; height: 55px;">
                </div>
                <h3>Kiosk Application</h3>
                <p>Apply directly using the kiosk for walk-in services and assistance.</p>
               <button onclick="window.location.href='/kioskform'" class="btn btn-orange"><span>Start Application</span>
            </div>
        </div>
    </main>

    <!-- JavaScript for Time & Keyboard Shortcuts -->
    <script>
        function updateDateTime() {
            const now = new Date();

            const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = now.toLocaleDateString(undefined, dateOptions);

            const timeStr = now.toLocaleTimeString();

            document.querySelector('#datetime .date').textContent = dateStr;
            document.querySelector('#datetime .time').textContent = timeStr;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '1') {
                e.preventDefault();
                alert('Scan QR Code shortcut');
            } else if (e.ctrlKey && e.key === '2') {
                e.preventDefault();
                alert('Kiosk Application shortcut');
            }
        });
    </script>
</body>
</html>