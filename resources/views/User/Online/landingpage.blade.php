<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>North Caloocan City Hall – Assessment Department</title>
  <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    html, body {
      height: 100%;
      margin: 0;
      scroll-behavior: smooth;
    }
    main {
      height: 100vh;
      overflow-y: scroll;
      scroll-snap-type: y mandatory;
    }
    section {
      scroll-snap-align: start;
    }

    /* Styling for the bottom-right help button */
    #howToUseBtn {
      font-size: 1rem;
      line-height: 1.5;
      padding: 10px 16px;
      color: #fdfdfd;
      border-radius: 0.75rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      backdrop-filter: blur(4px);
    }

    #howToUseBtn:hover {
      color: #92400e;
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-white text-gray-900 relative">
  
  <!-- SCROLL INDICATOR -->
  <div id="scroll-indicator" class="fixed top-0 left-0 h-1 bg-amber-600 z-50 transition-all duration-200 ease-out" style="width: 0%"></div>

  <!-- HEADER -->
  
  <main>

    <!-- HERO SECTION -->
    <section class="relative h-screen w-full flex items-center justify-center bg-cover bg-center" style="background-image: url('/img/bg1.jpg');">
      <div class="absolute inset-0 bg-black bg-opacity-60 backdrop-blur-sm"></div>
      <div class="z-10 text-center text-white px-4">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-4 drop-shadow" data-i18n="welcome_to">Welcome to North Caloocan</h1>
        <p class="text-lg sm:text-xl font-light mb-8 drop-shadow" data-i18n="city_services">City Assessment Department Online Services</p>
        <button onclick="location.href='{{ url('/services') }}'"
          class="bg-amber-600 hover:bg-amber-700 px-8 py-4 text-lg font-semibold rounded-2xl shadow-lg 
                 transition duration-300 transform hover:scale-105 flex items-center justify-center gap-2 mx-auto">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
          <span data-i18n="get_started">GET STARTED</span>
        </button>
      </div>
    </section>

    <!-- HOW TO USE SECTION -->
    <section id="howToUseSection" class="py-16 px-6 bg-white">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-6 text-gray-800" data-i18n="how_to_use">How to Use This Portal?</h2>
        <p class="text-lg text-gray-700 leading-relaxed" data-i18n="how_to_use_desc">
          This portal lets you easily process your requests with the City Assessor’s Department of North Caloocan City Hall. There are two available services: Pre-Registration for Queuing and Drop-off of Requirements.

For Pre-Registration, choose your desired service, indicate if you are the owner, review the list of requirements, and fill out the online form. After submitting, you’ll receive a QR code via email. Bring and scan this QR code at the City Hall scanner to activate your queue number.

For Drop-off of Requirements, follow the same steps, but instead of a QR code, you’ll get a PIN passcode. Enter this passcode at the Requirements Drop Box to submit your documents. Once validated, you’ll receive an email confirmation—if your requirements are approved, a QR code for queuing will be included; if not, you’ll get a message stating that your documents are incomplete or invalid.
        </p>
      </div>
    </section>

    <!-- SERVICES SECTION -->
    <section class="py-16 px-6 bg-gray-100">
      <div class="max-w-6xl mx-auto text-center">
        <h2 class="text-3xl font-bold mb-10 text-gray-800" data-i18n="services">Available Services</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

          <!-- Service Cards -->
          @foreach ([
            ['icon' => 'M9 17v-2h6v2a2 2 0 002 2h-2a2 2 0 01-2-2zM7 7h10v4H7V7z', 'title' => 'tax_declaration', 'desc' => 'tax_declaration_desc'],
            ['icon' => 'M5 13l4 4L19 7', 'title' => 'no_improvement', 'desc' => 'no_improvement_desc'],
            ['icon' => 'M9 12l2 2 4-4', 'title' => 'property_holding', 'desc' => 'property_holding_desc'],
            ['icon' => 'M13 16h-1v-4h-1m1-4h.01', 'title' => 'non_property_holding', 'desc' => 'non_property_holding_desc']
          ] as $service)
            <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition transform hover:scale-105">
              <div class="text-amber-600 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="{{ $service['icon'] }}" />
                </svg>
              </div>
              <p class="text-lg font-medium text-gray-800 mb-2" data-i18n="{{ $service['title'] }}">{{ $service['title'] }}</p>
              <p class="text-sm text-gray-600" data-i18n="{{ $service['desc'] }}">{{ $service['desc'] }}</p>
            </div>
          @endforeach

        </div>
      </div>
    </section>

  </main>

  <!-- FIXED BUTTON: How to Use -->
  <div class="fixed bottom-6 right-6 z-30">
    <button id="howToUseBtn" class="flex items-center gap-2">
      <span data-i18n="how_to_use_button">How to use this portal❔</span>
    </button>
  </div>

  <!-- SCRIPTS -->
  <script>
    // Load Laravel Translations
    const translations = {
      en: @json(__('home')),
      tl: @json(__('home', [], 'tl'))
    };

    // Change language
    function changeLang(lang) {
      // Update all [data-i18n] elements
      document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[lang][key] !== undefined) {
          el.textContent = translations[lang][key];
        }
      });

      // Save to localStorage
      localStorage.setItem('siteLang', lang);
    }

    // Get saved language
    function getSavedLang() {
      return localStorage.getItem('siteLang') || 'en';
    }

    // Apply translations on load
    document.addEventListener('DOMContentLoaded', () => {
      const savedLang = getSavedLang();
      document.getElementById('language-select').value = savedLang;
      changeLang(savedLang);
    });

    // Scroll indicator
    const indicator = document.getElementById('scroll-indicator');
    const main = document.querySelector('main');

    main.addEventListener('scroll', () => {
      const scrollTop = main.scrollTop;
      const scrollHeight = main.scrollHeight - main.clientHeight;
      const scrollPercent = (scrollTop / scrollHeight) * 100;
      indicator.style.width = scrollPercent + "%";
    });

    // Scroll to How to Use section
    const howToUseBtn = document.getElementById('howToUseBtn');
    const howToUseSection = document.getElementById('howToUseSection');

    howToUseBtn.addEventListener('click', () => {
      howToUseSection.scrollIntoView({ behavior: 'smooth' });
    });
  </script>

</body>
</html>