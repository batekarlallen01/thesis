<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>North Caloocan City Hall – Assessment Department</title>
  <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">

  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: Georgia, 'Times New Roman', Times, serif;
      color: white;
      overflow: hidden;
    }

    /* Full Background */
    .hero-bg {
      background-image: url('/img/bg1.jpg');
      background-size: cover;
      background-position: center;
      position: absolute;
      inset: 0;
      z-index: -1;
    }

    .overlay {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      z-index: -1;
    }

 

    #language-select {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: none;
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.9rem;
      backdrop-filter: blur(4px);
      cursor: pointer;
    }

    /* Main Content Centered */
    main {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 2rem;
      position: relative;
      z-index: 10;
    }

    h1 {
      font-family: 'Newsreader', serif;
      font-size: 2.8rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    p {
      font-size: 1.2rem;
      color: #e5e7eb;
      max-width: 700px;
      margin: 0 auto 2.5rem;
      line-height: 1.6;
    }

    /* Button Style (same as landing page) */
    .btn-large {
      background-color: #d97706; /* amber-600 */
      color: white;
      padding: 1rem 2.5rem;
      font-size: 1.2rem;
      font-weight: bold;
      border-radius: 1.5rem;
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    .btn-large:hover {
      background-color: #b45309;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
      h1 { font-size: 2.2rem; }
      p { font-size: 1rem; }
      .btn-large { font-size: 1.1rem; padding: 0.9rem 2rem; }
    }
  </style>
</head>
<body class="bg-gray-900 text-white">

  <!-- Background -->
  <div class="hero-bg"></div>
  <div class="overlay"></div>

  <!-- Main Content -->
  <main>
    <h1 data-i18n="select_service">Select a Service to Begin</h1>
    <p data-i18n="choose_option">
      Choose from the available services below to proceed with your request.
    </p>

    <!-- NORMAL SERVICE BUTTON -->
    <a href="{{ url('/pre-regform') }}" class="btn-large mb-4">
      <span data-i18n="normal_service">Pre-Registration</span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
      </svg>
    </a>

    <!-- PRE VALIDATION BUTTON -->
    <a href="{{ url('/mailform') }}" class="btn-large">
      <span data-i18n="pre_validation">Document Drop-off</span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
      </svg>
    </a>
  </main>



</body>
</html>