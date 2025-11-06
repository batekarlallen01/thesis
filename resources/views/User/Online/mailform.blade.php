<!DOCTYPE html>
<html lang="en" x-data="mailboxApp()">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document Drop-off Application - North Caloocan City Hall</title>
  <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
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
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-50 min-h-screen" x-data="mailboxApp()">

  <div class="max-w-4xl mx-auto mt-4 p-6 md:p-8">
    <div class="text-center mb-8">
      <img src="{{ asset('img/mainlogo.png') }}" alt="North Caloocan City Hall" class="w-16 h-16 mx-auto mb-3">
      <h1 class="text-3xl font-bold text-gray-800">Document Drop-off Application</h1>
      <p class="text-gray-600 mt-2">Complete the form to begin your application</p>
    </div>

    <div class="bg-white shadow-lg rounded-xl p-6 md:p-8 space-y-6">

      <!-- Personal Information Section -->
      <div class="bg-purple-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-purple-900 mb-4">PERSONAL INFORMATION:</h2>
        
        <div class="space-y-4">
          <!-- Full Name -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" x-model="form.ownerName" placeholder="Enter full name"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
          </div>

          <!-- Age -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Age <span class="text-red-500">*</span>
            </label>
            <input type="number" x-model.number="form.age" min="1" max="120" placeholder="Enter your age"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <p x-show="form.age && form.age >= 60" class="text-sm text-green-600 font-semibold mt-2">
              ✓ Senior Citizen - You will receive priority service
            </p>
          </div>
        </div>
      </div>

      <!-- PWD Section -->
      <div class="bg-blue-50 p-6 rounded-lg border-2 border-blue-200">
        <h2 class="text-xl font-semibold text-blue-900 mb-4">PWD INFORMATION:</h2>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">
              Are you a PWD (Person with Disability) beneficiary? <span class="text-red-500">*</span>
            </label>
            <div class="space-y-2">
              <label class="flex items-center space-x-3 cursor-pointer">
                <input type="radio" name="isPwd" value="no" x-model="form.isPwdString" @change="form.pwdId = ''"
                       class="form-radio text-blue-600">
                <span class="font-medium text-gray-800">No</span>
              </label>
              
              <label class="flex items-center space-x-3 cursor-pointer">
                <input type="radio" name="isPwd" value="yes" x-model="form.isPwdString"
                       class="form-radio text-blue-600">
                <span class="font-medium text-gray-800">Yes, I am a PWD beneficiary</span>
              </label>
            </div>
          </div>

          <!-- PWD ID Field -->
          <div x-show="form.isPwdString === 'yes'" x-transition class="pl-8">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              PWD ID Number <span class="text-red-500">*</span>
            </label>
            <input type="text" x-model="form.pwdId" placeholder="Enter your PWD ID number"
                   class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
        </div>
      </div>

      <!-- Applicant Type -->
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

      <!-- Request Type Section -->
      <div class="bg-indigo-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-indigo-900 mb-4">REQUEST FOR:</h2>
        
        <div class="space-y-3">
          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="tax_declaration" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800 group-hover:text-indigo-600">
                Certified True Copy of Tax Declaration (TD)
              </span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱100.00' : '₱50.00'"></p>
            </div>
          </label>

          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="no_improvement" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800 group-hover:text-indigo-600">
                Certification of No Improvement
              </span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱100.00' : '₱50.00'"></p>
            </div>
          </label>

          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="property_holdings" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800 group-hover:text-indigo-600">
                Certification of Property Holdings
              </span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱100.00' : '₱50.00'"></p>
            </div>
          </label>

          <label class="flex items-start space-x-3 cursor-pointer group">
            <input type="radio" name="requestType" value="non_property_holdings" x-model="form.requestType" class="mt-1 form-radio text-indigo-600">
            <div class="flex-1">
              <span class="font-medium text-gray-800 group-hover:text-indigo-600">
                Certification of Non-property Holdings
              </span>
              <p class="text-sm text-gray-600" x-text="form.applicantType === 'representative' ? '₱120.00' : '₱70.00'"></p>
            </div>
          </label>
        </div>

        <template x-if="!form.applicantType">
          <p class="text-sm text-red-600 mt-3 flex items-center gap-1">
            Please select applicant type first to see pricing
          </p>
        </template>
      </div>

      <!-- Number of Copies -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          Number of Copies <span class="text-red-500">*</span>
        </label>
        <input type="number" x-model="form.numberOfCopies" min="1" 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
      </div>

      <!-- Property Index Number (PIN) -->
      <div class="bg-yellow-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-yellow-900 mb-4">PROPERTY INDEX NUMBER (PIN):</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Land PIN</label>
            <input type="text" x-model="form.pinLand" placeholder="Enter land PIN"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Building PIN</label>
            <input type="text" x-model="form.pinBuilding" placeholder="Enter building PIN"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Machinery PIN</label>
            <input type="text" x-model="form.pinMachinery" placeholder="Enter machinery PIN"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
          </div>
        </div>
      </div>

      <!-- Purpose -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          PURPOSE <span class="text-red-500">*</span>
        </label>
        <textarea x-model="form.purpose" placeholder="State the purpose of this request" rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
      </div>

      <!-- Government Issued ID -->
      <div class="bg-blue-50 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-blue-900 mb-4">GOVERNMENT ISSUED ID:</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              ID Type <span class="text-red-500">*</span>
            </label>
            <select x-model="form.govtIdType"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
            <label class="block text-sm font-medium text-gray-700 mb-2">
              ID Number <span class="text-red-500">*</span>
            </label>
            <input type="text" x-model="form.govtIdNumber" placeholder="Enter ID number"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Issued at</label>
            <input type="text" x-model="form.issuedAt" placeholder="Place where ID was issued"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Issued on</label>
            <input type="date" x-model="form.issuedOn"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
        </div>
      </div>

      <!-- Address -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          ADDRESS <span class="text-red-500">*</span>
        </label>
        <textarea x-model="form.address" placeholder="Enter complete address" rows="2"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          EMAIL ADDRESS <span class="text-red-500">*</span>
        </label>
        <input type="email" x-model="form.email" placeholder="Enter email"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
      </div>

      <!-- Submit Button -->
      <div class="pt-6 border-t">
        <button @click="showRequirementsModal" type="button"
                :disabled="!isFormValid"
                :class="{'bg-gray-400 cursor-not-allowed': !isFormValid, 'bg-indigo-600 hover:bg-indigo-700': isFormValid}"
                class="w-full text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
          Submit Request
        </button>
      </div>

      <div class="p-4 bg-gray-100 rounded-lg">
        <p class="text-sm text-gray-600">
          <strong>Note:</strong> All fields marked with (*) are required. Please ensure all information is accurate before submitting.
        </p>
      </div>
    </div>
  </div>

  <!-- Requirements Modal -->
  <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-700/60 z-50 flex items-center justify-center px-4">
    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="w-full max-w-4xl h-[90vh] bg-[#f5f5f4] border border-white rounded-2xl p-6 shadow-xl flex flex-col overflow-hidden">

      <!-- Modal Header -->
      <div class="mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-black font-serif mb-1">Required Documents</h1>
        <p class="text-sm text-gray-800">Please prepare the following documents for your application.</p>
      </div>

      <!-- Role Display -->
      <div class="mb-4 p-3 bg-white rounded-lg border border-gray-300">
        <p class="text-sm font-medium text-gray-700">
          Selected Role: <span class="text-indigo-600 font-semibold" x-text="form.applicantType === 'owner' ? 'Property Owner' : 'Authorized Representative'"></span>
        </p>
      </div>

      <!-- Additional for Owner Only -->
      <template x-if="form.applicantType === 'owner'">
        <div class="mb-4 space-y-3">
          <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded-md shadow-sm animate-fade-in">
            <strong>Owner's Valid ID</strong>
            <p class="text-green-700 text-xs mt-1">Government-issued photo ID (e.g., Driver's License, Passport, National ID).</p>
          </div>
        </div>
      </template>

      <!-- Additional for Representative Only -->
      <template x-if="form.applicantType === 'representative'">
        <div class="mb-4 space-y-3">
          <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded-md shadow-sm animate-fade-in">
            <strong>Special Power of Attorney (SPA)</strong>
            <p class="text-blue-700 text-xs mt-1">Must be notarized and include authority to apply for tax declaration.</p>
          </div>
          <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded-md shadow-sm animate-fade-in" style="animation-delay: 0.05s;">
            <strong>Representative's Valid ID</strong>
            <p class="text-blue-700 text-xs mt-1">Government-issued photo ID (e.g., Driver's License, Passport).</p>
          </div>
        </div>
      </template>

      <!-- Document List -->
      <div class="flex-1 min-h-0 overflow-y-auto pr-2 space-y-3 text-black text-base leading-relaxed">
        <h3 class="font-semibold text-lg text-gray-800 mb-2 sticky top-0 bg-[#f5f5f4] py-2">Common Required Documents:</h3>
        
        <div class="space-y-3">
          <div class="bg-white p-3 rounded-md shadow-sm animate-fade-in">
            Request for Issuance of Updated Tax Declaration form
          </div>
          <div class="bg-white p-3 rounded-md shadow-sm animate-fade-in" style="animation-delay: 0.05s;">
            Title (Certified True Xerox Copy)
          </div>
          <div class="bg-white p-3 rounded-md shadow-sm animate-fade-in" style="animation-delay: 0.1s;">
            Updated Real Property Tax Payment (Amilyar)
          </div>
          <div class="bg-white p-3 rounded-md shadow-sm animate-fade-in" style="animation-delay: 0.15s;">
            Latest Tax Declaration (TD/OHA)
          </div>
          <div class="bg-white p-3 rounded-md shadow-sm animate-fade-in" style="animation-delay: 0.2s;">
            Deed of Sale / Extra Judicial Settlement / Partition Agreement
          </div>
          <div class="bg-white p-3 rounded-md shadow-sm animate-fade-in" style="animation-delay: 0.25s;">
            Transfer Tax Receipt
          </div>
          <div class="bg-white p-3 rounded-md shadow-sm animate-fade-in" style="animation-delay: 0.3s;">
            Certificate Authorizing Registration (CAR) from BIR
          </div>
        </div>
      </div>

      <!-- Modal Footer Buttons -->
      <div class="pt-4 border-t border-gray-300 mt-4 flex gap-3">
        <button @click="showModal = false" type="button"
                class="flex-1 px-6 py-3 text-lg font-semibold rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 transition-all duration-300">
          Go Back
        </button>
        <button @click="confirmAndSubmit" type="button"
                :disabled="isSubmitting"
                :class="{'opacity-50 cursor-not-allowed': isSubmitting}"
                class="flex-1 px-6 py-3 text-lg font-semibold rounded-md text-white bg-amber-600 hover:bg-amber-700 transition-all duration-300 flex items-center justify-center gap-2">
          <span x-show="!isSubmitting">I Have These Documents</span>
          <span x-show="isSubmitting" class="flex items-center gap-2">
            <svg class="spinner w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
          </span>
        </button>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div x-show="isSubmitting" x-transition class="fixed inset-0 bg-black bg-opacity-70 z-[60] flex items-center justify-center">
    <div class="bg-white rounded-xl p-8 shadow-2xl text-center max-w-sm">
      <div class="mb-4">
        <svg class="spinner w-16 h-16 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Processing Your Request</h3>
      <p class="text-gray-600 mb-4">Please wait while we submit your application</p>
      <div class="flex justify-center gap-2">
        <div class="w-3 h-3 bg-indigo-600 rounded-full pulse-dot"></div>
        <div class="w-3 h-3 bg-indigo-600 rounded-full pulse-dot"></div>
        <div class="w-3 h-3 bg-indigo-600 rounded-full pulse-dot"></div>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div x-show="successModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center">
      <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-green-600 mb-2">Application Submitted!</h2>
      <p class="text-gray-600 mb-6">Your PIN has been sent to your email. Bring your documents and use the PIN to access the mailbox.</p>
      <button @click="window.location.href='/'" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
        Return Home
      </button>
    </div>
  </div>

  <script>
    function mailboxApp() {
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
          govtIdType: '',
          govtIdNumber: '',
          issuedAt: '',
          issuedOn: '',
          address: '',
          email: ''
        },
        showModal: false,
        successModal: false,
        isSubmitting: false,

        get isFormValid() {
          const baseValid = this.form.requestType && 
                 this.form.applicantType && 
                 this.form.numberOfCopies && 
                 this.form.ownerName && 
                 this.form.age &&
                 this.form.isPwdString &&
                 this.form.purpose && 
                 this.form.govtIdType && 
                 this.form.govtIdNumber && 
                 this.form.address &&
                 this.form.email;
          
          if (this.form.isPwdString === 'yes') {
            return baseValid && this.form.pwdId;
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

        async confirmAndSubmit() {
          this.showModal = false;
          this.isSubmitting = true;

          try {
            const response = await fetch('/api/mailbox-submission', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                service_type: this.form.requestType,
                applicant_type: this.form.applicantType,
                number_of_copies: parseInt(this.form.numberOfCopies),
                full_name: this.form.ownerName,
                age: parseInt(this.form.age),
                is_pwd: this.form.isPwdString === 'yes',
                pwd_id: this.form.isPwdString === 'yes' ? this.form.pwdId : null,
                pin_land: this.form.pinLand || null,
                pin_building: this.form.pinBuilding || null,
                pin_machinery: this.form.pinMachinery || null,
                purpose: this.form.purpose,
                govt_id_type: this.form.govtIdType,
                govt_id_number: this.form.govtIdNumber,
                issued_at: this.form.issuedAt,
                issued_on: this.form.issuedOn,
                address: this.form.address,
                email: this.form.email
              })
            });

            this.isSubmitting = false;

            if (response.ok) {
              this.successModal = true;
            } else {
              const error = await response.json().catch(() => ({}));
              alert('Submission failed: ' + (error.message || 'Unknown error'));
            }
          } catch (err) {
            this.isSubmitting = false;
            console.error('Network error:', err);
            alert('Failed to submit. Check your connection and try again.');
          }
        }
      };
    }
  </script>
</body>
</html>