<?php

namespace App\Http\Controllers;

use App\Models\MailboxSubmission;
use App\Models\PreRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistrationSubmittedMail;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Support\Facades\Log;

class MailboxSubmissionController extends Controller
{
    /**
     * Store a new mailbox submission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|in:tax_declaration,no_improvement,property_holdings,non_property_holdings',
            'applicant_type' => 'required|in:owner,representative',
            'number_of_copies' => 'required|integer|min:1',
            'full_name' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'is_pwd' => 'required|boolean',
            'pwd_id' => 'required_if:is_pwd,true|nullable|string|max:50',
            'pin_land' => 'nullable|string|max:100',
            'pin_building' => 'nullable|string|max:100',
            'pin_machinery' => 'nullable|string|max:100',
            'purpose' => 'required|string',
            'govt_id_type' => 'required|string',
            'govt_id_number' => 'required|string|max:100',
            'issued_at' => 'nullable|string|max:255',
            'issued_on' => 'nullable|date',
            'address' => 'required|string',
            'email' => 'required|email|max:255',
        ]);

        // Generate unique 6-digit PIN code
        $pinCode = MailboxSubmission::generateUniquePinCode();

        // Create submission with status 'pending'
        $submission = MailboxSubmission::create([
            ...$validated,
            'pin_code' => $pinCode,
            'status' => 'pending'
        ]);

        // Send email with PIN
        $this->sendConfirmationEmail($submission);

        return response()->json([
            'success' => true,
            'message' => 'Submission successful',
            'data' => [
                'id' => $submission->id,
                'pin_code' => $pinCode
            ]
        ], 201);
    }

    /**
     * Verify PIN code (for IoT mailbox - user enters PIN onsite)
     * Changes status from 'pending' to 'submitted'
     */
    public function verifyPin(Request $request)
    {
        $validated = $request->validate([
            'pin_code' => 'required|string|size:6'
        ]);

        // Look for submissions with status 'pending' (not yet submitted to mailbox)
        $submission = MailboxSubmission::where('pin_code', $validated['pin_code'])
            ->where('status', 'pending')
            ->first();

        if (!$submission) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN or document not ready'
            ], 404);
        }

        // Mark as submitted (user just deposited docs in mailbox)
        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified. Mailbox unlocked.',
            'data' => [
                'name' => $submission->full_name,
                'service' => $submission->service_type
            ]
        ]);
    }

    /**
     * Get all submissions for admin panel
     * Only returns pending and submitted (active) submissions
     */
    public function index(Request $request)
    {
        try {
            $query = MailboxSubmission::query();

            // FIXED: Only show pending and submitted submissions (filter out completed/disapproved)
            $query->whereIn('status', ['pending', 'submitted']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('pin_code', 'like', "%{$search}%");
                });
            }

            $submissions = $query->orderBy('created_at', 'desc')->get();

            return response()->json($submissions);
        } catch (\Exception $e) {
            Log::error('Error fetching mailbox submissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch submissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single submission details (for admin)
     */
    public function show($id)
    {
        try {
            $submission = MailboxSubmission::findOrFail($id);
            
            return response()->json([
                'submission' => $submission
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching mailbox submission', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Submission not found'
            ], 404);
        }
    }

    /**
     * Admin approves submission - creates PreRegistration and sends QR code
     */
    public function approveMail($id)
    {
        try {
            $submission = MailboxSubmission::findOrFail($id);
            
            if ($submission->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only approve submitted documents'
                ], 400);
            }

            // Generate unique tokens for PreRegistration
            $qrToken = Str::random(32);
            $qrExpiresAt = now()->addHours(24);

            // Create PreRegistration from mailbox submission data
            $preReg = PreRegistration::create([
                'service_type' => $submission->service_type,
                'applicant_type' => $submission->applicant_type,
                'number_of_copies' => $submission->number_of_copies,
                'full_name' => $submission->full_name,
                'age' => $submission->age ?? 0,
                'is_pwd' => $submission->is_pwd ?? false,
                'pwd_id' => $submission->pwd_id,
                'purpose' => $submission->purpose,
                'govt_id_type' => $submission->govt_id_type,
                'govt_id_number' => $submission->govt_id_number,
                'issued_at' => $submission->issued_at,
                'issued_on' => $submission->issued_on,
                'address' => $submission->address,
                'email' => $submission->email,
                'qr_token' => $qrToken,
                'qr_expires_at' => $qrExpiresAt,
                'has_entered_queue' => false,
                'pin_numbers' => [
                    'land' => $submission->pin_land,
                    'building' => $submission->pin_building,
                    'machinery' => $submission->pin_machinery
                ]
            ]);

            Log::info('PreRegistration created from approved mailbox submission', [
                'pre_reg_id' => $preReg->id, 
                'mailbox_id' => $submission->id
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
                $preReg->update(['qr_image_path' => $filename]);

                Log::info('QR code generated for pre-registration', ['path' => $fullPath]);
            } catch (\Exception $e) {
                Log::error('QR Code generation failed', ['error' => $e->getMessage()]);
            }

            // Send QR code email to applicant using PreRegistration model
            try {
                Mail::send('emails.mailbox-approved', [
                    'preReg' => $preReg
                ], function ($message) use ($submission) {
                    $message->to($submission->email)
                        ->subject('Request Approved - QR Code for Queueing');
                });
                Log::info('QR code email sent', ['to' => $submission->email, 'pre_reg_id' => $preReg->id]);
            } catch (\Exception $e) {
                Log::error('Email sending failed', ['error' => $e->getMessage()]);
            }

            // Update mailbox submission status to completed
            $submission->update([
                'status' => 'completed',
                'approved_at' => now(),
                'pre_registration_id' => $preReg->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Submission approved. Pre-registration created and QR code sent to applicant.',
                'data' => [
                    'mailbox_id' => $submission->id,
                    'pre_registration_id' => $preReg->id,
                    'email_sent_to' => $submission->email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving mailbox submission', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve submission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin disapproves submission with specific reasons
     */
    public function disapproveMail($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'incorrect_documents' => 'required|array',
                'incorrect_documents.*' => 'string',
                'other_reason' => 'nullable|string|max:1000'
            ]);

            $submission = MailboxSubmission::findOrFail($id);
            
            if ($submission->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only disapprove submitted documents'
                ], 400);
            }

            // Ensure we have at least one reason
            if (empty($validated['incorrect_documents']) && empty($validated['other_reason'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide at least one reason for disapproval'
                ], 422);
            }

            $submission->update([
                'status' => 'disapproved',
                'disapproved_at' => now(),
                'disapproval_reasons' => $validated['incorrect_documents'],
                'disapproval_other_reason' => $validated['other_reason'] ?? null
            ]);

            // Send disapproval email with reasons
            try {
                Mail::send('emails.mailbox-disapproved', [
                    'submission' => $submission,
                    'incorrectDocuments' => $validated['incorrect_documents'],
                    'otherReason' => $validated['other_reason'] ?? null
                ], function ($message) use ($submission) {
                    $message->to($submission->email)
                        ->subject('Document Submission Disapproved - Action Required');
                });
                
                Log::info('Disapproval email sent', [
                    'to' => $submission->email, 
                    'id' => $submission->id,
                    'reasons' => $validated['incorrect_documents']
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send disapproval email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Submission disapproved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error disapproving mailbox submission', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disapprove submission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send confirmation email with PIN
     */
    private function sendConfirmationEmail(MailboxSubmission $submission)
    {
        try {
            Mail::send('emails.mailbox-submission-confirmation', [
                'submission' => $submission
            ], function ($message) use ($submission) {
                $message->to($submission->email)
                    ->subject('Document Drop-off Application Confirmation - PIN: ' . $submission->pin_code);
            });
            
            Log::info('Confirmation email sent', ['to' => $submission->email, 'pin' => $submission->pin_code]);
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email: ' . $e->getMessage());
        }
    }
}