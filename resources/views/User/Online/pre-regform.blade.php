<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="{{ asset('img/mainlogo.png') }}" type="image/png">
  <title>Pre-Registration - North Caloocan City Hall</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    * {
      transition: all 0.2s ease;
    }
    .spinner {
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    .upload-area {
      border: 2px dashed #cbd5e1;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    .upload-area:hover {
      border-color: #6366f1;
      background-color: #eef2ff;
    }
    .upload-area.drag-over {
      border-color: #4f46e5;
      background-color: #e0e7ff;
      transform: scale(1.02);
    }
    .upload-area.has-file {
      border-color: #10b981;
      background-color: #d1fae5;
    }
    .image-preview {
      position: relative;
      overflow: hidden;
    }
    .remove-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      background: rgba(239, 68, 68, 0.95);
      color: white;
      border-radius: 50%;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 20px;
      font-weight: bold;
    }
    .remove-btn:hover {
      background: rgba(220, 38, 38, 1);
      transform: scale(1.1);
    }
    .step-indicator {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 2rem;
    }
    .step {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      background: #e5e7eb;
      color: #6b7280;
    }
    .step.active {
      background: #4f46e5;
      color: white;
    }
    .step.completed {
      background: #10b981;
      color: white;
    }
    .step-line {
      width: 60px;
      height: 2px;
      background: #e5e7eb;
    }
    .step-line.active {
      background: #4f46e5;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-50 min-h-screen" x-data="registrationApp()">
  <div class="max-w-4xl mx-auto mt-8 p-6 md:p-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <img src="{{ asset('img/mainlogo.png') }}" alt="North Caloocan City Hall" class="w-16 h-16 mx-auto mb-3">
      <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Pre-Registration</h1>
      <p class="text-gray-600 mt-2">North Caloocan City Hall</p>
    </div>

    <!-- Step Indicator -->
    <div class="step-indicator mb-8">
      <div class="step" :class="{ 'active': step === 1, 'completed': step > 1 }">1</div>
      <div class="step-line" :class="{ 'active': step > 1 }"></div>
      <div class="step" :class="{ 'active': step === 2, 'completed': step > 2 }">2</div>
      <div class="step-line" :class="{ 'active': step > 2 }"></div>
      <div class="step" :class="{ 'active': step === 3 }">3</div>
    </div>

    <div class="bg-white shadow-2xl rounded-2xl p-8 md:p-10">
      <!-- STEP 1: Request Type & Applicant Type -->
      <div x-show="step === 1" x-transition>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Application Details</h2>
        <p class="text-gray-600 mb-6">Select the type of document and who is applying</p>

        <!-- Request Type -->
        <div class="mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-4">What are you requesting?</h3>
          <div class="space-y-4">
            <label class="block cursor-pointer">
              <div class="border-2 rounded-lg p-5 hover:border-indigo-500 hover:bg-indigo-50 transition"
                   :class="{ 'border-indigo-600 bg-indigo-50': form.requestType === 'tax_declaration' }">
                <div class="flex items-start gap-4">
                  <input type="radio" name="requestType" value="tax_declaration" 
                         x-model="form.requestType" class="mt-1 w-5 h-5 text-indigo-600">
                  <div class="flex-1">
                    <h4 class="font-semibold text-lg text-gray-800">Certified True Copy of Tax Declaration (TD)</h4>
                    <p class="text-sm text-gray-600 mt-1">Official certified copy of property tax declaration</p>
                  </div>
                </div>
              </div>
            </label>
            <label class="block cursor-pointer">
              <div class="border-2 rounded-lg p-5 hover:border-indigo-500 hover:bg-indigo-50 transition"
                   :class="{ 'border-indigo-600 bg-indigo-50': form.requestType === 'no_improvement' }">
                <div class="flex items-start gap-4">
                  <input type="radio" name="requestType" value="no_improvement" 
                         x-model="form.requestType" class="mt-1 w-5 h-5 text-indigo-600">
                  <div class="flex-1">
                    <h4 class="font-semibold text-lg text-gray-800">Certification of No Improvement</h4>
                    <p class="text-sm text-gray-600 mt-1">Certificate stating no improvements on the property</p>
                  </div>
                </div>
              </div>
            </label>
            <label class="block cursor-pointer">
              <div class="border-2 rounded-lg p-5 hover:border-indigo-500 hover:bg-indigo-50 transition"
                   :class="{ 'border-indigo-600 bg-indigo-50': form.requestType === 'property_holdings' }">
                <div class="flex items-start gap-4">
                  <input type="radio" name="requestType" value="property_holdings" 
                         x-model="form.requestType" class="mt-1 w-5 h-5 text-indigo-600">
                  <div class="flex-1">
                    <h4 class="font-semibold text-lg text-gray-800">Certification of Property Holdings</h4>
                    <p class="text-sm text-gray-600 mt-1">Certificate of owned properties within the city</p>
                  </div>
                </div>
              </div>
            </label>
            <label class="block cursor-pointer">
              <div class="border-2 rounded-lg p-5 hover:border-indigo-500 hover:bg-indigo-50 transition"
                   :class="{ 'border-indigo-600 bg-indigo-50': form.requestType === 'non_property_holdings' }">
                <div class="flex items-start gap-4">
                  <input type="radio" name="requestType" value="non_property_holdings" 
                         x-model="form.requestType" class="mt-1 w-5 h-5 text-indigo-600">
                  <div class="flex-1">
                    <h4 class="font-semibold text-lg text-gray-800">Certification of Non-property Holdings</h4>
                    <p class="text-sm text-gray-600 mt-1">Certificate stating no property ownership in the city</p>
                  </div>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Applicant Type -->
        <div class="border-t-2 border-gray-200 pt-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-4">Who is applying?</h3>
          <div class="space-y-4">
            <label class="block cursor-pointer">
              <div class="border-2 rounded-lg p-6 hover:border-green-500 hover:bg-green-50 transition"
                   :class="{ 'border-green-600 bg-green-50': form.applicantType === 'owner' }">
                <div class="flex items-start gap-4">
                  <input type="radio" name="applicantType" value="owner" 
                         x-model="form.applicantType" class="mt-1 w-5 h-5 text-green-600">
                  <div class="flex-1">
                    <h4 class="font-semibold text-xl text-gray-800">Property Owner</h4>
                    <p class="text-gray-600 mt-2">I am the owner of the property</p>
                    <div class="mt-3 p-3 bg-white rounded border border-green-200">
                      <p class="text-sm font-semibold text-gray-700 mb-1">Required Document:</p>
                      <ul class="text-sm text-gray-600 space-y-1">
                        <li>✓ Owner's Valid Government-Issued ID</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </label>
            <label class="block cursor-pointer">
              <div class="border-2 rounded-lg p-6 hover:border-blue-500 hover:bg-blue-50 transition"
                   :class="{ 'border-blue-600 bg-blue-50': form.applicantType === 'representative' }">
                <div class="flex items-start gap-4">
                  <input type="radio" name="applicantType" value="representative" 
                         x-model="form.applicantType" class="mt-1 w-5 h-5 text-blue-600">
                  <div class="flex-1">
                    <h4 class="font-semibold text-xl text-gray-800">Authorized Representative</h4>
                    <p class="text-gray-600 mt-2">I am applying on behalf of the owner with proper authorization</p>
                    <div class="mt-3 p-3 bg-white rounded border border-blue-200">
                      <p class="text-sm font-semibold text-gray-700 mb-1">Required Documents:</p>
                      <ul class="text-sm text-gray-600 space-y-1">
                        <li>✓ Owner's Valid Government-Issued ID</li>
                        <li>✓ Special Power of Attorney (SPA) - Notarized</li>
                        <li>✓ Representative's Valid Government-Issued ID</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </label>
          </div>
        </div>

        <div class="mt-8 flex justify-end">
          <button @click="nextStep()" 
                  :disabled="!form.requestType || !form.applicantType"
                  :class="(form.requestType && form.applicantType) ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                  class="px-8 py-3 text-white font-semibold rounded-lg shadow-lg transition">
            Next Step →
          </button>
        </div>
      </div>

      <!-- STEP 2: Upload Required Documents -->
      <div x-show="step === 2" x-transition>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Upload Required Documents</h2>
        <p class="text-gray-600 mb-6">Please attach clear photos or scans of ALL required documents (JPG, PNG - Max 5MB each)</p>

        <div class="space-y-6">

          <!-- Role-Specific Documents Header -->
          <div class="bg-gradient-to-r from-purple-50 to-blue-50 p-4 rounded-lg border-2 border-purple-200 mb-6">
            <h3 class="font-bold text-lg text-purple-900 mb-2">
              <span x-text="form.applicantType === 'owner' ? '👤 Owner Documents' : '👥 Representative Documents'"></span>
            </h3>
          </div>

          <!-- Owner Documents -->
          <template x-if="form.applicantType === 'owner'">
            <div>
              <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded">
                <h3 class="font-semibold text-green-900 mb-2">📄 Owner's Valid Government-Issued ID</h3>
                <p class="text-sm text-green-800">Upload a clear photo of your ID (Driver's License, Passport, National ID, etc.)</p>
              </div>
              <div class="upload-area rounded-xl p-8 text-center"
                   :class="{ 'has-file': uploads.ownerIdImage }"
                   @click="$refs.ownerIdInput.click()"
                   @dragover.prevent="handleDragOver($event)"
                   @dragleave.prevent="handleDragLeave($event)"
                   @drop.prevent="handleDrop($event, 'ownerIdImage')">
                <template x-if="!uploads.ownerIdImage">
                  <div>
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-lg font-semibold text-gray-700">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500 mt-2">JPG or PNG (Max 5MB)</p>
                  </div>
                </template>
                <template x-if="uploads.ownerIdImage">
                  <div class="image-preview">
                    <img :src="previews.ownerIdImage" class="max-h-64 mx-auto rounded-lg shadow-md">
                    <div class="remove-btn" @click.stop="removeFile('ownerIdImage')">✕</div>
                    <p class="text-sm text-green-700 font-semibold mt-3">✓ Owner's ID uploaded successfully</p>
                  </div>
                </template>
              </div>
              <input type="file" x-ref="ownerIdInput" @change="handleFileSelect($event, 'ownerIdImage')" accept="image/jpeg,image/jpg,image/png" class="hidden">
            </div>
          </template>

          <!-- Representative Documents -->
          <template x-if="form.applicantType === 'representative'">
            <div class="space-y-6">
              <!-- Owner's ID -->
              <div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                  <h3 class="font-semibold text-blue-900 mb-2">📄 Owner's Valid Government-Issued ID</h3>
                  <p class="text-sm text-blue-800">Upload a clear photo of the property owner's ID</p>
                </div>
                <div class="upload-area rounded-xl p-8 text-center"
                     :class="{ 'has-file': uploads.ownerIdImage }"
                     @click="$refs.ownerIdInput.click()"
                     @dragover.prevent="handleDragOver($event)"
                     @dragleave.prevent="handleDragLeave($event)"
                     @drop.prevent="handleDrop($event, 'ownerIdImage')">
                  <template x-if="!uploads.ownerIdImage">
                    <div>
                      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <p class="text-lg font-semibold text-gray-700">Click to upload or drag and drop</p>
                      <p class="text-sm text-gray-500 mt-2">JPG or PNG (Max 5MB)</p>
                    </div>
                  </template>
                  <template x-if="uploads.ownerIdImage">
                    <div class="image-preview">
                      <img :src="previews.ownerIdImage" class="max-h-64 mx-auto rounded-lg shadow-md">
                      <div class="remove-btn" @click.stop="removeFile('ownerIdImage')">✕</div>
                      <p class="text-sm text-green-700 font-semibold mt-3">✓ Owner's ID uploaded successfully</p>
                    </div>
                  </template>
                </div>
                <input type="file" x-ref="ownerIdInput" @change="handleFileSelect($event, 'ownerIdImage')" accept="image/jpeg,image/jpg,image/png" class="hidden">
              </div>

              <!-- SPA -->
              <div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                  <h3 class="font-semibold text-blue-900 mb-2">📄 Special Power of Attorney (SPA)</h3>
                  <p class="text-sm text-blue-800">Upload a notarized SPA showing authority to process this request</p>
                </div>
                <div class="upload-area rounded-xl p-8 text-center"
                     :class="{ 'has-file': uploads.spaImage }"
                     @click="$refs.spaInput.click()"
                     @dragover.prevent="handleDragOver($event)"
                     @dragleave.prevent="handleDragLeave($event)"
                     @drop.prevent="handleDrop($event, 'spaImage')">
                  <template x-if="!uploads.spaImage">
                    <div>
                      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <p class="text-lg font-semibold text-gray-700">Click to upload or drag and drop</p>
                      <p class="text-sm text-gray-500 mt-2">JPG or PNG (Max 5MB)</p>
                    </div>
                  </template>
                  <template x-if="uploads.spaImage">
                    <div class="image-preview">
                      <img :src="previews.spaImage" class="max-h-64 mx-auto rounded-lg shadow-md">
                      <div class="remove-btn" @click.stop="removeFile('spaImage')">✕</div>
                      <p class="text-sm text-green-700 font-semibold mt-3">✓ SPA uploaded successfully</p>
                    </div>
                  </template>
                </div>
                <input type="file" x-ref="spaInput" @change="handleFileSelect($event, 'spaImage')" accept="image/jpeg,image/jpg,image/png" class="hidden">
              </div>

              <!-- Representative's ID -->
              <div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                  <h3 class="font-semibold text-blue-900 mb-2">📄 Representative's Valid ID</h3>
                  <p class="text-sm text-blue-800">Upload a clear photo of your government-issued ID</p>
                </div>
                <div class="upload-area rounded-xl p-8 text-center"
                     :class="{ 'has-file': uploads.repIdImage }"
                     @click="$refs.repIdInput.click()"
                     @dragover.prevent="handleDragOver($event)"
                     @dragleave.prevent="handleDragLeave($event)"
                     @drop.prevent="handleDrop($event, 'repIdImage')">
                  <template x-if="!uploads.repIdImage">
                    <div>
                      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <p class="text-lg font-semibold text-gray-700">Click to upload or drag and drop</p>
                      <p class="text-sm text-gray-500 mt-2">JPG or PNG (Max 5MB)</p>
                    </div>
                  </template>
                  <template x-if="uploads.repIdImage">
                    <div class="image-preview">
                      <img :src="previews.repIdImage" class="max-h-64 mx-auto rounded-lg shadow-md">
                      <div class="remove-btn" @click.stop="removeFile('repIdImage')">✕</div>
                      <p class="text-sm text-green-700 font-semibold mt-3">✓ Representative's ID uploaded successfully</p>
                    </div>
                  </template>
                </div>
                <input type="file" x-ref="repIdInput" @change="handleFileSelect($event, 'repIdImage')" accept="image/jpeg,image/jpg,image/png" class="hidden">
              </div>
            </div>
          </template>

          <!-- Common Required Documents -->
          <div class="bg-gradient-to-r from-orange-50 to-yellow-50 p-4 rounded-lg border-2 border-orange-200 mt-8 mb-6">
            <h3 class="font-bold text-lg text-orange-900 mb-2">📋 Common Required Documents</h3>
            <p class="text-sm text-orange-800">The following documents are required for all applicants</p>
          </div>

          <!-- Dynamic Loop Through Common Docs - FIXED -->
          <template x-for="doc in commonDocs" :key="doc.field">
            <div>
              <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-4 rounded">
                <h3 class="font-semibold text-orange-900 mb-2" x-text="doc.label"></h3>
                <p class="text-sm text-orange-800" x-text="doc.desc"></p>
              </div>
              <div class="upload-area rounded-xl p-6 text-center"
                   :class="{ 'has-file': uploads[doc.field] }"
                   @click="triggerFileInput(doc.field)"
                   @dragover.prevent="handleDragOver($event)"
                   @dragleave.prevent="handleDragLeave($event)"
                   @drop.prevent="handleDrop($event, doc.field)">
                <template x-if="!uploads[doc.field]">
                  <div>
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-sm font-semibold text-gray-700">Click or drag to upload</p>
                  </div>
                </template>
                <template x-if="uploads[doc.field]">
                  <div class="image-preview">
                    <img :src="previews[doc.field]" class="max-h-48 mx-auto rounded-lg shadow">
                    <div class="remove-btn" @click.stop="removeFile(doc.field)">✕</div>
                    <p class="text-sm text-green-700 font-semibold mt-2">✓ Uploaded</p>
                  </div>
                </template>
              </div>
              <input type="file" 
                     :id="'input-' + doc.field"
                     @change="handleFileSelect($event, doc.field)" 
                     accept="image/jpeg,image/jpg,image/png" 
                     class="hidden">
            </div>
          </template>

        </div>

        <div class="mt-8 flex justify-between">
          <button @click="step = 1" class="px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
            ← Back
          </button>
          <button @click="nextStep()" 
                  :disabled="!areDocumentsUploaded()"
                  :class="areDocumentsUploaded() ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                  class="px-8 py-3 text-white font-semibold rounded-lg shadow-lg transition">
            Next Step →
          </button>
        </div>
      </div>

      <!-- STEP 3: Personal Information -->
      <div x-show="step === 3" x-transition>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Personal Information</h2>
        <p class="text-gray-600 mb-6">Complete your application details</p>

        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" x-model="form.fullName" placeholder="Enter your full name"
                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Age <span class="text-red-500">*</span>
            </label>
            <input type="number" x-model.number="form.age" min="1" max="120" placeholder="Your age"
                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <p x-show="form.age >= 60" class="text-sm text-green-600 font-semibold mt-2">
              ✓ Senior Citizen - You will receive priority service
            </p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input type="email" x-model="form.email" placeholder="your.email@example.com"
                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Complete Address <span class="text-red-500">*</span>
            </label>
            <textarea x-model="form.address" rows="3" placeholder="Enter your complete address"
                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
          </div>

          <!-- PWD Section -->
          <div class="bg-blue-50 p-4 rounded-lg border-2 border-blue-200">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">PWD INFORMATION</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Are you a PWD (Person with Disability) beneficiary? <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                  <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="radio" name="isPwd" value="no" x-model="form.isPwdString" @change="form.pwdId = ''"
                           class="w-4 h-4 text-blue-600">
                    <span class="font-medium text-gray-800">No</span>
                  </label>
                  <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="radio" name="isPwd" value="yes" x-model="form.isPwdString"
                           class="w-4 h-4 text-blue-600">
                    <span class="font-medium text-gray-800">Yes, I am a PWD beneficiary</span>
                  </label>
                </div>
              </div>
              <div x-show="form.isPwdString === 'yes'" x-transition class="pl-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  PWD ID Number <span class="text-red-500">*</span>
                </label>
                <input type="text" x-model="form.pwdId" placeholder="Enter your PWD ID number"
                       class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-sm text-green-600 font-semibold mt-2">
                  ✓ PWD - You will receive priority service
                </p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Number of Copies <span class="text-red-500">*</span>
            </label>
            <input type="number" x-model.number="form.numberOfCopies" min="1" placeholder="How many copies?"
                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Purpose of Request <span class="text-red-500">*</span>
            </label>
            <textarea x-model="form.purpose" rows="3" placeholder="State the purpose of this request"
                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
          </div>
        </div>

        <div class="mt-8 flex justify-between">
          <button @click="step = 2" class="px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
            ← Back
          </button>
          <button @click="submitApplication()" 
                  :disabled="!isPersonalInfoComplete() || isSubmitting"
                  :class="isPersonalInfoComplete() && !isSubmitting ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
                  class="px-8 py-3 text-white font-semibold rounded-lg shadow-lg transition flex items-center gap-2">
            <template x-if="!isSubmitting">
              <span>Submit Application ✓</span>
            </template>
            <template x-if="isSubmitting">
              <span class="flex items-center gap-2">
                <svg class="spinner w-5 h-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submitting...
              </span>
            </template>
          </button>
        </div>
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
      <p class="text-gray-600 mb-6">Your request has been received successfully. Check your email for the QR code and confirmation details.</p>
      <button @click="window.location.reload()" 
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
        Submit Another Application
      </button>
    </div>
  </div>

  <!-- Alpine.js Script -->
  <script>
    function registrationApp() {
      return {
        step: 1,
        form: {
          requestType: '',
          applicantType: '',
          fullName: '',
          age: null,
          email: '',
          address: '',
          isPwdString: 'no',
          pwdId: '',
          numberOfCopies: '',
          purpose: ''
        },
        uploads: {
          ownerIdImage: null,
          spaImage: null,
          repIdImage: null,
          taxDeclForm: null,
          title: null,
          taxPayment: null,
          latestTaxDecl: null,
          deedOfSale: null,
          transferTaxReceipt: null,
          carFromBir: null
        },
        previews: {
          ownerIdImage: '',
          spaImage: '',
          repIdImage: '',
          taxDeclForm: '',
          title: '',
          taxPayment: '',
          latestTaxDecl: '',
          deedOfSale: '',
          transferTaxReceipt: '',
          carFromBir: ''
        },
        isSubmitting: false,
        successModal: false,

        // List of common documents
        commonDocs: [
          { field: 'taxDeclForm', label: '1. Request for Issuance of Updated Tax Declaration Form', desc: 'Official request form for tax declaration update' },
          { field: 'title', label: '2. Title (Certified True Xerox Copy)', desc: 'Certified copy of the property title' },
          { field: 'taxPayment', label: '3. Updated Real Property Tax Payment (Amilyar)', desc: 'Latest property tax payment receipt' },
          { field: 'latestTaxDecl', label: '4. Latest Tax Declaration (TD/OHA)', desc: 'Most recent tax declaration document' },
          { field: 'deedOfSale', label: '5. Deed of Sale / Extra Judicial Settlement / Partition Agreement', desc: 'Document showing property transfer or ownership' },
          { field: 'transferTaxReceipt', label: '6. Transfer Tax Receipt', desc: 'Receipt of paid transfer tax' },
          { field: 'carFromBir', label: '7. Certificate Authorizing Registration (CAR) from BIR', desc: 'BIR authorization certificate for registration' }
        ],

        nextStep() {
          if (this.step < 3) this.step++;
        },

        // NEW METHOD: Trigger file input by ID instead of using $refs
        triggerFileInput(field) {
          const input = document.getElementById('input-' + field);
          if (input) {
            input.click();
          }
        },

        handleDragOver(event) {
          event.currentTarget.classList.add('drag-over');
        },

        handleDragLeave(event) {
          event.currentTarget.classList.remove('drag-over');
        },

        handleDrop(event, field) {
          event.currentTarget.classList.remove('drag-over');
          const file = event.dataTransfer.files[0];
          this.processFile(file, field);
        },

        handleFileSelect(event, field) {
          const file = event.target.files[0];
          this.processFile(file, field);
        },

        processFile(file, field) {
          if (!file) return;

          if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            alert('Please upload a valid image file (JPG or PNG)');
            return;
          }

          if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
          }

          this.uploads[field] = file;
          const reader = new FileReader();
          reader.onload = e => this.previews[field] = e.target.result;
          reader.readAsDataURL(file);
        },

        removeFile(field) {
          this.uploads[field] = null;
          this.previews[field] = '';
          // Clear the file input
          const input = document.getElementById('input-' + field);
          if (input) {
            input.value = '';
          }
          // Also try clearing with refs for owner/rep/spa
          if (this.$refs[field + 'Input']) {
            this.$refs[field + 'Input'].value = '';
          }
        },

        areDocumentsUploaded() {
          if (this.form.applicantType === 'owner') {
            return !!this.uploads.ownerIdImage;
          }
          if (this.form.applicantType === 'representative') {
            return !!this.uploads.ownerIdImage && !!this.uploads.spaImage && !!this.uploads.repIdImage;
          }
          return false;
        },

        isPersonalInfoComplete() {
          const baseValid = this.form.fullName &&
               this.form.age &&
               this.form.email &&
               this.form.address &&
               this.form.isPwdString &&
               this.form.numberOfCopies &&
               this.form.purpose;

          if (this.form.isPwdString === 'yes') {
            return baseValid && this.form.pwdId;
          }
          return baseValid;
        },

        async submitApplication() {
          if (!this.isPersonalInfoComplete() || this.isSubmitting) return;
          
          this.isSubmitting = true;
          const formData = new FormData();

          // Append form fields with correct backend names
          formData.append('service_type', this.form.requestType);
          formData.append('applicant_type', this.form.applicantType);
          formData.append('full_name', this.form.fullName);
          formData.append('age', this.form.age);
          formData.append('email', this.form.email);
          formData.append('address', this.form.address);
          formData.append('is_pwd', this.form.isPwdString === 'yes' ? 1 : 0);
          formData.append('pwd_id', this.form.pwdId || '');
          formData.append('number_of_copies', this.form.numberOfCopies);
          formData.append('purpose', this.form.purpose);

          // Append files with correct backend names
          const fileMapping = {
              ownerIdImage: 'owner_id_image',
              spaImage: 'spa_image',
              repIdImage: 'rep_id_image',
              taxDeclForm: 'tax_decl_form',
              title: 'title',
              taxPayment: 'tax_payment',
              latestTaxDecl: 'latest_tax_decl',
              deedOfSale: 'deed_of_sale',
              transferTaxReceipt: 'transfer_tax_receipt',
              carFromBir: 'car_from_bir'
          };

          Object.keys(fileMapping).forEach(frontendKey => {
              if (this.uploads[frontendKey]) {
                  formData.append(fileMapping[frontendKey], this.uploads[frontendKey]);
              }
          });

          try {
              const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
              const response = await fetch('/api/pre-registration', {
                  method: 'POST',
                  headers: {
                      'X-CSRF-TOKEN': csrfToken
                  },
                  body: formData
              });

              this.isSubmitting = false;

              if (response.ok) {
                  this.successModal = true;
              } else {
                  const error = await response.json().catch(() => ({}));
                  console.error('Server error:', error);
                  alert('Submission failed: ' + (error.message || 'Unknown error. Please try again.'));
              }
          } catch (err) {
              this.isSubmitting = false;
              console.error('Network error:', err);
              alert('Failed to submit. Please check your connection and try again.');
          }
        }
      };
    }
  </script>
</body>
</html>