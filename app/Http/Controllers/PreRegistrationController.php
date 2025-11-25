<?php

namespace App\Http\Controllers;

use App\Models\PreRegistration;
use App\Mail\PreRegistrationApprovedMail;
use App\Mail\PreRegistrationPendingMail;
use App\Mail\PreRegistrationDisapprovedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

class PreRegistrationController extends Controller
{
    /**
     * Handle pre-registration submission (NO QR YET - just save to pending)
     */
    public function store(Request $request)
    {
        try {
            Log::info('Starting pre-registration submission', [
                'full_name' => $request->input('full_name'),
                'email' => $request->input('email'),
                'applicant_type' => $request->input('applicant_type')
            ]);

            // Clean up empty strings to null
            if ($request->pwd_id === '') {
                $request->merge(['pwd_id' => null]);
            }

            // Validate incoming request
            $validated = $request->validate([
                'service_type' => 'required|string|in:tax_declaration,no_improvement,property_holdings,non_property_holdings',
                'applicant_type' => 'required|string|in:owner,representative',
                'full_name' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:120',
                'email' => 'required|email|max:255',
                'address' => 'required|string',
                'is_pwd' => 'required|boolean',
                'pwd_id' => 'nullable|string|max:50',
                'number_of_copies' => 'required|integer|min:1',
                'purpose' => 'required|string',
                
                // File uploads - required based on applicant type
                'owner_id_image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
                'spa_image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'rep_id_image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                
                // Common documents (all optional)
                'tax_decl_form' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'title' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'tax_payment' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'latest_tax_decl' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'deed_of_sale' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'transfer_tax_receipt' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
                'car_from_bir' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            ]);

            // Prepare data for database (NO QR generation yet)
            $data = [
                'service_type' => $validated['service_type'],
                'applicant_type' => $validated['applicant_type'],
                'full_name' => $validated['full_name'],
                'age' => $validated['age'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'is_pwd' => $validated['is_pwd'],
                'pwd_id' => $validated['pwd_id'] ?? null,
                'number_of_copies' => $validated['number_of_copies'],
                'purpose' => $validated['purpose'],
                'status' => 'pending', // START AS PENDING
                'qr_token' => null, // Will be generated on approval
                'qr_expires_at' => null,
                'pin_code' => null,
                'has_entered_queue' => false,
                'qr_image_path' => null,
                'govt_id_type' => null,
                'govt_id_number' => null,
                'issued_at' => null,
                'issued_on' => null,
                'pin_numbers' => null,
            ];

            // Handle file uploads
            $documentFields = [
                'owner_id_image',
                'spa_image',
                'rep_id_image',
                'tax_decl_form',
                'title',
                'tax_payment',
                'latest_tax_decl',
                'deed_of_sale',
                'transfer_tax_receipt',
                'car_from_bir'
            ];

            foreach ($documentFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = time() . '_' . $field . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    // Store in storage/app/public/documents
                    $path = $file->storeAs('documents', $filename, 'public');
                    
                    // Save only the filename to database
                    $data[$field] = $filename;
                    
                    Log::info("File uploaded: {$field}", ['filename' => $filename]);
                } else {
                    $data[$field] = null;
                }
            }

            // Save to database
            $preReg = PreRegistration::create($data);

            Log::info('Pre-registration saved to database with PENDING status', ['id' => $preReg->id]);

            // Send simple confirmation email (NO QR CODE) using Mailable
            try {
                Mail::to($preReg->email)->send(new PreRegistrationPendingMail($preReg));
                
                Log::info('Confirmation email sent', ['to' => $preReg->email, 'id' => $preReg->id]);
            } catch (\Exception $mailException) {
                Log::error('Email sending failed', [
                    'error' => $mailException->getMessage(),
                    'email' => $preReg->email,
                    'id' => $preReg->id
                ]);
            }

            // Success response
            return response()->json([
                'success' => true,
                'message' => 'Your pre-registration has been submitted successfully! You will receive an email once it has been reviewed.',
                'id' => $preReg->id
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Please fix the errors above.'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during pre-registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pre-registrations for admin review
     * 🔴 Now only returns 'pending' by default
     */
    public function index(Request $request)
    {
        try {
            $query = PreRegistration::query();

            // Filter by status if provided; otherwise show only 'pending'
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                $query->where('status', 'pending'); // 👈 Only pending items shown
            }

            // Optional search by name or email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $registrations = $query->orderBy('created_at', 'desc')->get();

            return response()->json($registrations);
        } catch (\Exception $e) {
            Log::error('Error fetching pre-registrations', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registrations'
            ], 500);
        }
    }

    /**
     * Admin approves pre-registration - GENERATES QR CODE and sends email
     */
    public function approve($id)
    {
        try {
            $preReg = PreRegistration::findOrFail($id);
            
            if ($preReg->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only approve pending registrations'
                ], 400);
            }

            // Generate QR token
            $qrToken = Str::random(32);
            $qrExpiresAt = now()->addHours(24);

            $preReg->update([
                'status' => 'approved',
                'qr_token' => $qrToken,
                'qr_expires_at' => $qrExpiresAt,
                'approved_at' => now(),
                'reviewed_at' => now(),
                'reviewed_by' => session('user_id') ?? null,
            ]);

            // Generate QR Code
            $qrUrl = route('queue.scan', ['token' => $qrToken]);
            $qrDirectory = storage_path('app/public/qrcodes');

            if (!file_exists($qrDirectory)) {
                mkdir($qrDirectory, 0777, true);
            }

            $filename = "prereg_{$preReg->id}_{$qrToken}.png";
            $fullPath = "{$qrDirectory}/{$filename}";

            try {
                $builder = new Builder(
                    writer: new PngWriter(),
                    writerOptions: [],
                    validateResult: false,
                    data: $qrUrl,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 300,
                    margin: 10,
                    roundBlockSizeMode: RoundBlockSizeMode::Margin
                );

                $result = $builder->build();
                $result->saveToFile($fullPath);
                
                // Update database with QR filename
                $preReg->update(['qr_image_path' => $filename]);
                
                // CRITICAL: Refresh the model to get the updated qr_image_path
                $preReg->refresh();

                Log::info('QR code generated for approved pre-registration', [
                    'path' => $fullPath,
                    'filename' => $filename,
                    'file_exists' => file_exists($fullPath),
                    'model_qr_path' => $preReg->qr_image_path
                ]);

            } catch (\Exception $e) {
                Log::error('QR Code generation failed', ['error' => $e->getMessage()]);
            }

            // Send approval email WITH QR CODE using Mailable
            // The Mailable class handles everything (base64 encoding, subject, view)
            try {
                // Double-check QR file exists before sending email
                $qrFilePath = storage_path("app/public/qrcodes/{$preReg->qr_image_path}");
                
                if (!file_exists($qrFilePath)) {
                    Log::error('QR file missing before sending email', [
                        'expected_path' => $qrFilePath,
                        'qr_image_path' => $preReg->qr_image_path
                    ]);
                }
                
                Mail::to($preReg->email)->send(new PreRegistrationApprovedMail($preReg));
                
                Log::info('Approval email with QR sent', ['to' => $preReg->email, 'id' => $preReg->id]);
            } catch (\Exception $e) {
                Log::error('Approval email failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pre-registration approved successfully. QR code sent to applicant.',
                'data' => [
                    'id' => $preReg->id,
                    'email_sent_to' => $preReg->email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving pre-registration', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin disapproves pre-registration with reasons
     */
    public function disapprove($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'incorrect_documents' => 'required|array',
                'incorrect_documents.*' => 'string',
                'other_reason' => 'nullable|string|max:1000'
            ]);

            $preReg = PreRegistration::findOrFail($id);
            
            if ($preReg->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only disapprove pending registrations'
                ], 400);
            }

            $preReg->update([
                'status' => 'disapproved',
                'disapproved_at' => now(),
                'reviewed_at' => now(),
                'reviewed_by' => session('user_id') ?? null,
                'disapproval_reasons' => $validated['incorrect_documents'],
                'disapproval_other_reason' => $validated['other_reason'] ?? null
            ]);

            // Send disapproval email with reasons using Mailable
            try {
                Mail::to($preReg->email)->send(
                    new PreRegistrationDisapprovedMail(
                        $preReg, 
                        $validated['incorrect_documents'],
                        $validated['other_reason'] ?? null
                    )
                );
                
                Log::info('Disapproval email sent', [
                    'to' => $preReg->email, 
                    'id' => $preReg->id
                ]);
            } catch (\Exception $e) {
                Log::error('Disapproval email failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pre-registration disapproved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error disapproving pre-registration', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disapprove: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a single pre-registration with document URLs for admin review
     */
    public function show($id)
    {
        try {
            $preReg = PreRegistration::findOrFail($id);

            // Document fields that store image uploads
            $documentFields = [
                'owner_id_image', 'spa_image', 'rep_id_image',
                'tax_decl_form', 'title', 'tax_payment',
                'latest_tax_decl', 'deed_of_sale',
                'transfer_tax_receipt', 'car_from_bir'
            ];

            // Generate full public URLs for frontend display
            $documents = [];
            foreach ($documentFields as $field) {
                if ($preReg->$field) {
                    $documents[$field] = asset("storage/documents/{$preReg->$field}");
                } else {
                    $documents[$field] = null;
                }
            }

            return response()->json([
                ...$preReg->toArray(),
                'documents' => $documents,
                'priority_status' => $preReg->priority_status,
                'formatted_created_at' => $preReg->created_at?->format('M d, Y \a\t h:i A'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load submission'
            ], 500);
        }
    }
}