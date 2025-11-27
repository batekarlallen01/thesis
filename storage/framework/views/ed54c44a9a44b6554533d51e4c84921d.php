<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kiosk Application - North Caloocan City Hall</title>
  <link rel="icon" href="<?php echo e(asset('img/mainlogo.png')); ?>" type="image/png">

  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    * { transition: all 0.2s ease; }
    .spinner { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .pulse-dot { animation: pulse 1.5s ease-in-out infinite; }
    .pulse-dot:nth-child(2) { animation-delay: 0.2s; }
    .pulse-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes pulse { 0%, 100% { opacity: 0.3; transform: scale(0.8); } 50% { opacity: 1; transform: scale(1.2); } }
    input:focus, button:focus, select:focus, textarea:focus { outline: none; }
    .priority-badge { animation: pulse-glow 2s ease-in-out infinite; }
    @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 5px rgba(16, 185, 129, 0.5); } 50% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.8); } }
    
    /* Back Button */
    .back-button {
      position: fixed;
      top: 2rem;
      left: 2rem;
      background: white;
      color: #4f46e5;
      border: 2px solid #4f46e5;
      padding: 0.75rem 1.5rem;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      z-index: 1000;
    }
    
    .back-button:hover {
      background: #4f46e5;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
    }
    
    /* Idle Timer Display */
    .idle-timer {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: rgba(255,255,255,0.95);
      color: #4f46e5;
      padding: 0.75rem 1.25rem;
      border-radius: 12px;
      font-size: 0.9rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      z-index: 1000;
      display: none;
      border: 2px solid #e0e7ff;
    }
    
    .idle-timer.warning {
      background: #fef3c7;
      color: #92400e;
      border-color: #fbbf24;
      animation: pulse-warning 1s ease-in-out infinite;
    }
    
    @keyframes pulse-warning {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    @media (max-width: 768px) {
      .back-button {
        top: 1rem;
        left: 1rem;
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
      }
      
      .idle-timer {
        bottom: 1rem;
        right: 1rem;
        font-size: 0.8rem;
        padding: 0.6rem 1rem;
      }
    }
  </style>

  <!-- DEFINE ALPINE FUNCTION BEFORE Alpine.js LOADS -->
  <script>
    window.taxDeclarationApp = function() {
      return {
        form: {
          requestType: '',
          applicantType: '',
          numberOfCopies: '',
          ownerName: '',
          age: null,
          isPwdString: 'no',
          pwdId: '',
          pinLand: '',
          pinBuilding: '',
          pinMachinery: '',
          purpose: '',
          // Owner's ID (always required)
          ownerGovtIdType: '',
          ownerGovtIdNumber: '',
          ownerIssuedAt: '',
          ownerIssuedOn: '',
          // Representative's ID (only when representative is selected)
          repGovtIdType: '',
          repGovtIdNumber: '',
          repIssuedAt: '',
          repIssuedOn: '',
          address: '',
        },
        showModal: false,
        successModal: false,
        isSubmitting: false,
        queueNumber: '',
        currentDateTime: '',
        
        // Idle timer properties
        idleTimer: null,
        countdownInterval: null,
        remainingTime: 120,
        IDLE_TIMEOUT: 120000, // 2 minutes
        WARNING_TIME: 30000, // 30 seconds

        init() {
          this.startIdleTimer();
          this.setupIdleListeners();
        },

        startIdleTimer() {
          this.clearIdleTimer();
          this.remainingTime = 120;
          const timerEl = document.getElementById('idle-timer');
          if (timerEl) {
            timerEl.style.display = 'none';
            timerEl.classList.remove('warning');
          }
          
          this.idleTimer = setTimeout(() => {
            console.log('⏰ Idle timeout - returning to home');
            this.goBackHome();
          }, this.IDLE_TIMEOUT);
          
          // Show countdown at 30 seconds
          setTimeout(() => {
            this.showCountdown();
          }, this.IDLE_TIMEOUT - this.WARNING_TIME);
        },
        
        clearIdleTimer() {
          if (this.idleTimer) {
            clearTimeout(this.idleTimer);
            this.idleTimer = null;
          }
          if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
          }
        },
        
        showCountdown() {
          const timerEl = document.getElementById('idle-timer');
          const countdownEl = document.getElementById('countdown');
          if (!timerEl || !countdownEl) return;
          
          this.remainingTime = 30;
          timerEl.style.display = 'block';
          timerEl.classList.add('warning');
          countdownEl.textContent = this.remainingTime;
          
          this.countdownInterval = setInterval(() => {
            this.remainingTime--;
            countdownEl.textContent = this.remainingTime;
            
            if (this.remainingTime <= 0) {
              clearInterval(this.countdownInterval);
            }
          }, 1000);
        },
        
        resetIdleTimer() {
          this.startIdleTimer();
        },
        
        setupIdleListeners() {
          ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, () => this.resetIdleTimer(), true);
          });
        },

        get priorityStatus() {
          if (this.form.isPwdString === 'yes' && this.form.pwdId) {
            return 'PWD';
          }
          if (this.form.age && this.form.age >= 60) {
            return 'Senior';
          }
          return 'Regular';
        },

        get isFormValid() {
          const baseValid = this.form.requestType && 
                 this.form.applicantType && 
                 this.form.numberOfCopies && 
                 this.form.ownerName && 
                 this.form.age &&
                 this.form.isPwdString &&
                 this.form.purpose && 
                 this.form.ownerGovtIdType && 
                 this.form.ownerGovtIdNumber && 
                 this.form.address;
          
          // Check PWD ID if applicable
          if (this.form.isPwdString === 'yes' && !this.form.pwdId) {
            return false;
          }

          // Check representative ID if representative is selected
          if (this.form.applicantType === 'representative') {
            return baseValid && this.form.repGovtIdType && this.form.repGovtIdNumber;
          }

          return baseValid;
        },

        showRequirementsModal() {
          if (!this.isFormValid) {
            alert('Please fill in all required fields marked with *');
            return;
          }
          this.showModal = true;
        },

        getServiceTypeName(serviceType) {
          const serviceNames = {
            'tax_declaration': 'Tax Declaration (TD)',
            'no_improvement': 'No Improvement',
            'property_holdings': 'Property Holdings',
            'non_property_holdings': 'Non-property Holdings'
          };
          return serviceNames[serviceType] || serviceType;
        },

        async confirmAndSubmit() {
          this.showModal = false;
          this.isSubmitting = true;

          try {
            const response = await fetch('/api/kiosk/entry', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                full_name: this.form.ownerName,
                age: this.form.age,
                is_pwd: this.form.isPwdString === 'yes',
                pwd_id: this.form.isPwdString === 'yes' ? this.form.pwdId : null,
                applicant_type: this.form.applicantType,
                service_type: this.form.requestType,
                number_of_copies: parseInt(this.form.numberOfCopies),
                pin_land: this.form.pinLand,
                pin_building: this.form.pinBuilding,
                pin_machinery: this.form.pinMachinery,
                purpose: this.form.purpose,
                address: this.form.address,
                // Owner's ID
                govt_id_type: this.form.ownerGovtIdType,
                govt_id_number: this.form.ownerGovtIdNumber,
                issued_at: this.form.ownerIssuedAt,
                issued_on: this.form.ownerIssuedOn,
                // Representative's ID (if applicable)
                rep_govt_id_type: this.form.applicantType === 'representative' ? this.form.repGovtIdType : null,
                rep_govt_id_number: this.form.applicantType === 'representative' ? this.form.repGovtIdNumber : null,
                rep_issued_at: this.form.applicantType === 'representative' ? this.form.repIssuedAt : null,
                rep_issued_on: this.form.applicantType === 'representative' ? this.form.repIssuedOn : null,
              })
            });

            this.isSubmitting = false;

            if (response.ok) {
              const data = await response.json();
              console.log('Success response:', data);
              this.queueNumber = data.data.queue_number;
              this.successModal = true;
              
              // Auto return to home after 5 seconds on success
              setTimeout(() => {
                console.log('✅ Successful submission - returning to home');
                this.goBackHome();
              }, 5000);
            } else {
              const errorData = await response.json().catch(() => ({}));
              console.error('Server error:', errorData);
              
              let errorMessage = 'Submission failed';
              if (errorData.errors) {
                const errors = Object.values(errorData.errors).flat();
                errorMessage = errors.join('\n');
              } else if (errorData.message) {
                errorMessage = errorData.message;
              }
              alert(errorMessage);
            }
          } catch (err) {
            this.isSubmitting = false;
            console.error('Network error:', err);
            alert('Failed to submit. Check your connection and try again.');
          }
        },

        resetForm() {
          this.form = {
            requestType: '',
            applicantType: '',
            numberOfCopies: '',
            ownerName: '',
            age: null,
            isPwdString: 'no',
            pwdId: '',
            pinLand: '',
            pinBuilding: '',
            pinMachinery: '',
            purpose: '',
            ownerGovtIdType: '',
            ownerGovtIdNumber: '',
            ownerIssuedAt: '',
            ownerIssuedOn: '',
            repGovtIdType: '',
            repGovtIdNumber: '',
            repIssuedAt: '',
            repIssuedOn: '',
            address: '',
          };
          this.successModal = false;
          this.queueNumber = '';
          window.scrollTo(0, 0);
        }
      };
    };
  </script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-50 min-h-screen" x-data="taxDeclarationApp()" x-init="init()">

  <!-- Back Button -->
  <button class="back-button" @click="goBackHome()">
    ← Back to Home
  </button>
  
  <!-- Idle Timer Display -->
  <div class="idle-timer" id="idle-timer">
    Returning to home in <span id="countdown">120</span>s
  </div>

  <div class="max-w-4xl mx-auto mt-4 p-6 md:p-8">
    <div class="text-center mb-8">
      <img src="<?php echo e(asset('img/mainlogo.png')); ?>" alt="North Caloocan City Hall" class="w-16 h-16 mx-auto mb-3">
      <h1 class="text-3xl font-bold text-gray-800">Kiosk Application</h1>
      <p class="text-gray-600 mt-2">Complete the form to begin your application</p>
      
      <div x-show="priorityStatus !== 'Regular'" class="mt-4 inline-block">
        <span class="priority-badge px-4 py-2 rounded-full text-sm font-semibold"
              :class="{
                'bg-green-100 text-green-800 border-2 border-green-500': priorityStatus === 'Senior',
                'bg-blue-100 text-blue-800 border-2 border-blue-500': priorityStatus === 'PWD'
              }">
          ⭐ <span x-text="priorityStatus"></span> Citizen - Priority Access
        </span>
      </div>
    </div>

    <div class="bg-white shadow-lg rounded-xl p-6 md:p-8 space-y-6">
      <!-- PERSONAL INFORMATION -->
      <div class="bg-purple-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-purple-900 mb-4">PERSONAL INFORMATION:</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
            <input type="text" x-model="form.ownerName" placeholder="Enter full name"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Age <span class="text-red-500">*</span></label>
            <input type="number" x-model.number="form.age" min="1" max="120" placeholder="Enter your age"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <p x-show="form.age && form.age >= 60" class="text-sm text-green-600 font-semibold mt-2">
              ✓ Senior Citizen - You will receive priority service
            </p>
          </div>
        </div>
      </div>

      <!-- PWD INFORMATION -->
      <div class="bg-blue-50 p-6 rounded-lg border-2 border-blue-200">
        <h2 class="text-xl font-semibold text-blue-900 mb-4">PWD INFORMATION:</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Are you a PWD (Person with Disability) beneficiary? <span class="text-red-500">*</span></label>
            <div class="space-y-2">
              <label class="flex items-center space-x-3 cursor-pointer">
                <input type="radio" name="isPwd" value="no" x-model="form.isPwdString" @change="form.pwdId = ''" class="form-radio text-blue-600">
                <span class="font-medium text-gray-800">No</span>
              </label>
              <label class="flex items-center space-x-3 cursor-pointer">
                <input type="radio" name="isPwd" value="yes" x-model="form.isPwdString" class="form-radio text-blue-600">
                <span class="font-medium text-gray-800">Yes, I am a PWD beneficiary</span>
              </label>
            </div>
          </div>
          <div x-show="form.isPwdString === 'yes'" x-transition class="pl-8">
            <label class="block text-sm font-semibold text-gray-700 mb-2">PWD ID Number <span class="text-red-500">*</span></label>
            <input type="text" x-model="form.pwdId" placeholder="Enter your PWD ID number"
                   class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
        </div>
      </div>

      <!-- APPLICANT TYPE -->
      <div class="bg-green-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-green-900 mb-4">APPLICANT TYPE:</h2>
        <div class="space-y-3">
          <label class="flex items-center space-x-3 cursor-pointer">
            <input type="radio" name="applicantType" value="owner" x-model="form.applicantType" class="form-radio text-green-600">
            <span class="font-medium text-gray-800">Owner</span>
          </label>
          <label class="flex items-center space-x-3 cursor-pointer">
            <input type="radio" name="applicantType" value="representative" x-model="form.applicantType" class="form-radio text-green-600">
            <span class="font-medium text-gray-800">Representative with SPA or Authorization</span>
          </label>
        </div>
      </div>

      <!-- REQUEST FOR -->
      <div class="bg-indigo-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-indigo-900 mb-4">REQUEST FOR:</h2>
        <div class="space-y-3">
          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="tax_declaration" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800">Certified True Copy of Tax Declaration (TD)</span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱100.00' : '₱50.00'"></p>
            </div>
          </label>
          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="no_improvement" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800">Certification of No Improvement</span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱100.00' : '₱50.00'"></p>
            </div>
          </label>
          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="property_holdings" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800">Certification of Property Holdings</span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱100.00' : '₱50.00'"></p>
            </div>
          </label>
          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="non_property_holdings" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800">Certification of Non-property Holdings</span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱120.00' : '₱70.00'"></p>
            </div>
          </label>
        </div>
      </div>

      <!-- NUMBER OF COPIES -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Copies <span class="text-red-500">*</span></label>
        <input type="number" x-model="form.numberOfCopies" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
      </div>

      <!-- PROPERTY INDEX NUMBER -->
      <div class="bg-yellow-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-yellow-900 mb-4">PROPERTY INDEX NUMBER (PIN):</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Land PIN</label>
            <input type="text" x-model="form.pinLand" placeholder="Enter land PIN" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Building PIN</label>
            <input type="text" x-model="form.pinBuilding" placeholder="Enter building PIN" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Machinery PIN</label>
            <input type="text" x-model="form.pinMachinery" placeholder="Enter machinery PIN" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
          </div>
        </div>
      </div>

      <!-- PURPOSE -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">PURPOSE <span class="text-red-500">*</span></label>
        <textarea x-model="form.purpose" placeholder="State the purpose of this request" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
      </div>

      <!-- OWNER'S GOVERNMENT ISSUED ID (Always Required) -->
      <div class="bg-blue-50 p-6 rounded-lg border-2 border-blue-300">
        <h2 class="text-xl font-semibold text-blue-900 mb-2">PROPERTY OWNER'S GOVERNMENT ISSUED ID:</h2>
        <p class="text-sm text-blue-700 mb-4">📋 Owner's identification is always required</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Owner's ID Type <span class="text-red-500">*</span></label>
            <select x-model="form.ownerGovtIdType" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select ID Type</option>
              <option value="drivers_license">Driver's License</option>
              <option value="passport">Passport</option>
              <option value="umid">UMID</option>
              <option value="sss">SSS ID</option>
              <option value="philhealth">PhilHealth ID</option>
              <option value="postal">Postal ID</option>
              <option value="voters">Voter's ID</option>
              <option value="national_id">National ID</option>
              <option value="prc">PRC ID</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Owner's ID Number <span class="text-red-500">*</span></label>
            <input type="text" x-model="form.ownerGovtIdNumber" placeholder="Enter owner's ID number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Issued at</label>
            <input type="text" x-model="form.ownerIssuedAt" placeholder="Place where ID was issued" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Issued on</label>
            <input type="date" x-model="form.ownerIssuedOn" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
        </div>
      </div>

      <!-- REPRESENTATIVE'S GOVERNMENT ISSUED ID (Only when representative is selected) -->
      <div x-show="form.applicantType === 'representative'" x-transition class="bg-amber-50 p-6 rounded-lg border-2 border-amber-300">
        <h2 class="text-xl font-semibold text-amber-900 mb-2">REPRESENTATIVE'S GOVERNMENT ISSUED ID:</h2>
        <p class="text-sm text-amber-700 mb-4">👤 Representative's identification is required when applying on behalf of the owner</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Representative's ID Type <span class="text-red-500">*</span></label>
            <select x-model="form.repGovtIdType" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
              <option value="">Select ID Type</option>
              <option value="drivers_license">Driver's License</option>
              <option value="passport">Passport</option>
              <option value="umid">UMID</option>
              <option value="sss">SSS ID</option>
              <option value="philhealth">PhilHealth ID</option>
              <option value="postal">Postal ID</option>
              <option value="voters">Voter's ID</option>
              <option value="national_id">National ID</option>
              <option value="prc">PRC ID</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Representative's ID Number <span class="text-red-500">*</span></label>
            <input type="text" x-model="form.repGovtIdNumber" placeholder="Enter representative's ID number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Issued at</label>
            <input type="text" x-model="form.repIssuedAt" placeholder="Place where ID was issued" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Issued on</label>
            <input type="date" x-model="form.repIssuedOn" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
          </div>
        </div>
      </div>

      <!-- ADDRESS -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">ADDRESS <span class="text-red-500">*</span></label>
        <textarea x-model="form.address" placeholder="Enter complete address" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
      </div>

      <!-- SUBMIT BUTTON -->
      <div class="pt-6 border-t">
        <button @click="showRequirementsModal" type="button"
                :disabled="!isFormValid"
                :class="{'bg-gray-400 cursor-not-allowed': !isFormValid, 'bg-indigo-600 hover:bg-indigo-700': isFormValid}"
                class="w-full text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
          Submit
        </button>
      </div>
    </div>
  </div>

  <!-- Requirements Modal -->
  <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-700/60 z-50 flex items-center justify-center px-4">
    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="w-full max-w-4xl h-[90vh] bg-[#f5f5f4] border border-white rounded-2xl p-6 shadow-xl flex flex-col overflow-hidden">

      <div class="mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-black font-serif mb-1">Required Documents</h1>
        <p class="text-sm text-gray-800">Please prepare the following documents for your application.</p>
      </div>

      <div class="mb-4 p-3 bg-white rounded-lg border border-gray-300">
        <p class="text-sm font-medium text-gray-700">
          Selected Role: <span class="text-indigo-600 font-semibold" x-text="form.applicantType === 'owner' ? 'Property Owner' : 'Authorized Representative'"></span>
        </p>
      </div>

      <template x-if="form.applicantType === 'owner'">
        <div class="mb-4 space-y-3">
          <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded-md shadow-sm animate-fade-in">
            <strong>Owner's Valid ID</strong>
            <p class="text-green-700 text-xs mt-1">Government-issued photo ID of the property owner</p>
          </div>
        </div>
      </template>

      <template x-if="form.applicantType === 'representative'">
        <div class="mb-4 space-y-3">
          <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded-md shadow-sm">
            <strong>Owner's Valid ID</strong>
            <p class="text-blue-700 text-xs mt-1">Government-issued photo ID of the property owner</p>
          </div>
          <div class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded-md shadow-sm">
            <strong>Representative's Valid ID</strong>
            <p class="text-amber-700 text-xs mt-1">Government-issued photo ID of the representative</p>
          </div>
          <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded-md shadow-sm">
            <strong>Special Power of Attorney (SPA)</strong>
            <p class="text-red-700 text-xs mt-1">Must be notarized</p>
          </div>
        </div>
      </template>

      <div class="flex-1 min-h-0 overflow-y-auto pr-2 space-y-3">
        <div class="bg-white p-3 rounded-md shadow-sm">Request for Issuance of Updated Tax Declaration</div>
        <div class="bg-white p-3 rounded-md shadow-sm">Title (Certified True Xerox Copy)</div>
        <div class="bg-white p-3 rounded-md shadow-sm">Updated Real Property Tax Payment</div>
        <div class="bg-white p-3 rounded-md shadow-sm">Latest Tax Declaration (TD/OHA)</div>
      </div>

      <div class="pt-4 border-t border-gray-300 mt-4 flex gap-3">
        <button @click="showModal = false" type="button" class="flex-1 px-6 py-3 text-lg font-semibold rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 transition-all">Go Back</button>
        <button @click="confirmAndSubmit" type="button" :disabled="isSubmitting" :class="{'opacity-50 cursor-not-allowed': isSubmitting}" class="flex-1 px-6 py-3 text-lg font-semibold rounded-md text-white bg-amber-600 hover:bg-amber-700 transition-all flex items-center justify-center gap-2">
          <span x-show="!isSubmitting">I Have These Documents</span>
          <span x-show="isSubmitting">Processing...</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div x-show="successModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-[70] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-8 text-center">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
      <h2 class="text-3xl font-bold text-green-600 mb-2">Added to Queue!</h2>
      <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-4">
        <p class="text-sm text-gray-600 mb-2">Your Queue Number:</p>
        <p class="text-5xl font-bold text-blue-600" x-text="queueNumber"></p>
      </div>
      <div x-show="priorityStatus !== 'Regular'" class="mb-4">
        <span class="px-4 py-2 rounded-full text-sm font-semibold inline-block" :class="{'bg-green-100 text-green-800': priorityStatus === 'Senior', 'bg-blue-100 text-blue-800': priorityStatus === 'PWD'}">
          ⭐ <span x-text="priorityStatus"></span> Citizen - Priority Queue
        </span>
      </div>
      <p class="text-gray-600 mb-4">Please wait for your number to be called.</p>
      <p class="text-sm text-indigo-600 font-semibold mb-6">✓ Receipt has been printed</p>
      <button @click="resetForm()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-medium transition">
        Submit Another Application
      </button>
    </div>
  </div>

</body>
</html><?php /**PATH C:\Users\kiosk\Documents\thesis\resources\views/User/Kiosk/kioskapplicationform.blade.php ENDPATH**/ ?>