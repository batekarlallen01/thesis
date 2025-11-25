<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - North Caloocan City Hall</title>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
  
  <style>
    body {
      background-image: url('/img/bg1.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
    }
    
    .card-button {
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
    }
    
    .card-button:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
    }
    
    .login-card {
      backdrop-filter: blur(20px);
      background: rgba(255, 255, 255, 0.98);
    }
    
    .gradient-overlay {
      background: linear-gradient(135deg, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.6) 50%, rgba(0, 0, 0, 0.75) 100%);
    }
    
    input:focus {
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
      box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
    }
    
    .error-alert {
      animation: shake 0.5s;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
      20%, 40%, 60%, 80% { transform: translateX(10px); }
    }
    
    .success-alert {
      animation: slideDown 0.5s;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative">
  
  <!-- Dark overlay with gradient -->
  <div class="absolute inset-0 gradient-overlay"></div>
  
  <div class="relative z-10 w-full max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

      <!-- Left Column: Logo Header + Two Buttons -->
      <div class="flex flex-col gap-6">
        <!-- Logo Header - No background, larger text -->
        <div class="text-center mb-6">
          <img src="/img/mainlogo.png" alt="North Caloocan City Hall Logo" class="w-16 h-16 mx-auto mb-2">
          <h1 class="text-white text-3xl font-bold drop-shadow-lg">North Caloocan City Hall</h1>
          <p class="text-white text-sm opacity-90 drop-shadow mt-1">Assessment Department</p>
        </div>
        
        <!-- Two Navigation Buttons -->
        <div class="grid grid-cols-2 gap-6">
          <!-- Live Queue Display Button -->
          <button onclick="window.location.href='/live-queue'"
                  class="card-button h-[350px] bg-gradient-to-br from-blue-700 to-blue-900 text-white rounded-3xl shadow-2xl flex flex-col items-center justify-center border-4 border-white group">
            <div class="text-center p-6 relative z-10">
              <!-- Icon -->
              <div class="relative mb-4">
                <div class="relative bg-white bg-opacity-10 w-20 h-20 rounded-full flex items-center justify-center mx-auto backdrop-blur-sm">
                  <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                  </svg>
                </div>
              </div>
              <h2 class="text-xl font-bold uppercase tracking-wide mb-1">Live Queue</h2>
              <h3 class="text-xl font-bold uppercase tracking-wide">Display</h3>
              <div class="mt-4 flex items-center justify-center gap-2 text-xs opacity-80">
                <span>View Real-time Queue Status</span>
                <svg class="w-4 h-4 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
              </div>
            </div>
          </button>
          
          <!-- Kiosk Home Button -->
          <button onclick="window.location.href='/kiosk'"
                  class="card-button h-[350px] bg-gradient-to-br from-blue-700 to-blue-900 text-white rounded-3xl shadow-2xl flex flex-col items-center justify-center border-4 border-white group">
            <div class="text-center p-6 relative z-10">
              <!-- Icon -->
              <div class="relative mb-4">
                <div class="relative bg-white bg-opacity-10 w-20 h-20 rounded-full flex items-center justify-center mx-auto backdrop-blur-sm">
                  <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                  </svg>
                </div>
              </div>
              <h2 class="text-xl font-bold uppercase tracking-wide mb-1">Kiosk</h2>
              <h3 class="text-xl font-bold uppercase tracking-wide">Home</h3>
              <div class="mt-4 flex items-center justify-center gap-2 text-xs opacity-80">
                <span>Access Self-Service Kiosk</span>
                <svg class="w-4 h-4 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
              </div>
            </div>
          </button>
        </div>
      </div>
      
      <!-- Right Column: Admin Login Card -->
      <div class="flex justify-center items-center">
        <div class="login-card w-full max-w-md rounded-3xl shadow-2xl p-10 border-4 border-white flex flex-col">
          
          <!-- Logo & Title -->
          <div class="text-center mb-6">
            <img src="/img/admin.png" alt="Admin Logo" class="w-24 h-24 mx-auto mb-4 rounded-full shadow-lg object-cover">
            <h1 class="text-2xl font-bold text-gray-800">Welcome Admin!</h1>
            <p class="text-gray-600 text-sm mt-1">Assessment Department Portal</p>
          </div>
          
          <!-- Error Messages -->
          <?php if($errors->any()): ?>
            <div class="mb-4 error-alert">
              <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium">
                      <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo e($error); ?>

                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- Success Messages -->
          <?php if(session('success')): ?>
            <div class="mb-4 success-alert">
              <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-green-700 font-medium">
                      <?php echo e(session('success')); ?>

                    </p>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
          
         <!-- Login Form -->
<form method="POST" action="<?php echo e(route('login')); ?>" class="flex-1 flex flex-col justify-between">
    <?php echo csrf_field(); ?>
    <div class="space-y-5">
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Username:</label>
            <input 
                type="text" 
                name="username"
                value="<?php echo e(old('username')); ?>"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-blue-600 transition-all duration-200 <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="Enter your username">
        </div>
        
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Password:</label>
            <div class="relative">
                <input 
                    type="password" 
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-blue-600 transition-all duration-200 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="Enter your password">
                <button 
                    type="button" 
                    onclick="togglePassword()"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="mt-6">
        <button 
            type="submit"
            class="w-full btn-primary text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg uppercase tracking-wide text-lg">
            Login
        </button>
    </div>
</form>
          
          <!-- Forgot Password Link -->
          <div class="mt-4 text-center">
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition">Forgot Password?</a>
          </div>
        </div>
      </div>
      
    </div>
  </div>
  
  <!-- Footer -->
  <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-20 text-center">
    <p class="text-white text-sm opacity-80 drop-shadow">© 2025 North Caloocan City Hall Assessment Department</p>
  </div>
  
  <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }
    
    // Auto-hide success messages after 5 seconds
    setTimeout(() => {
        const successAlert = document.querySelector('.success-alert');
        if (successAlert) {
            successAlert.style.transition = 'opacity 0.5s';
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 500);
        }
    }, 5000);
  </script>
</body>
</html><?php /**PATH C:\Users\kiosk\Documents\thesis\resources\views/Admin/adminhome.blade.php ENDPATH**/ ?>