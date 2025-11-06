<?php

namespace App\Http\Controllers;

use App\Models\PreRegistration;
use App\Mail\PreRegistrationSubmittedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

class PreRegistrationController extends Controller
{
    /**
     * Handle pre-registration submission.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Starting pre-registration submission', [
                'full_name' => $request->input('full_name'),
                'email' => $request->input('email')
            ]);

            // Validate incoming request
            $validated = $request->validate([
                'service_type' => 'required|string|in:tax_declaration,no_improvement,property_holdings,non_property_holdings',
                'applicant_type' => 'required|string|in:owner,representative',
                'number_of_copies' => 'required|integer|min:1',
                'full_name' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:120',
                'is_pwd' => 'required|boolean',
                'pwd_id' => 'required_if:is_pwd,true|nullable|string|max:50',
                'purpose' => 'required|string',
                'govt_id_type' => 'required|string',
                'govt_id_number' => 'required|string',
                'address' => 'required|string',
                'issued_at' => 'nullable|string',
                'issued_on' => 'nullable|date',
                'email' => 'required|email|max:255',
                'pin_numbers' => 'nullable|array',
                'pin_numbers.land' => 'nullable|string|max:100',
                'pin_numbers.building' => 'nullable|string|max:100',
                'pin_numbers.machinery' => 'nullable|string|max:100',
            ]);

            // Generate unique tokens
            $qrToken = Str::random(32);
            $pinCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $qrExpiresAt = now()->addHours(24);

            // Save to database
            $preReg = PreRegistration::create(array_merge($validated, [
                'qr_token' => $qrToken,
                'qr_expires_at' => $qrExpiresAt,
                'pin_code' => $pinCode,
                'has_entered_queue' => false,
                'qr_image_path' => null,
            ]));

            Log::info('Pre-registration saved to database', ['id' => $preReg->id]);

            // --- Generate QR Code ---
            $qrUrl = route('queue.scan', ['token' => $qrToken]);
            $qrDirectory = storage_path('app/public/qrcodes');

            // Ensure directory exists
            if (!file_exists($qrDirectory)) {
                mkdir($qrDirectory, 0777, true);
            }

            $filename = "prereg_{$preReg->id}_{$qrToken}.png";
            $fullPath = "{$qrDirectory}/{$filename}";

            try {
                // Create QR code using Builder
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

                // Save file
                $result->saveToFile($fullPath);

                // Update model with QR path
                $preReg->update(['qr_image_path' => $filename]);

                Log::info('QR code successfully generated', ['path' => $fullPath]);
            } catch (\Exception $e) {
                Log::error('QR Code generation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $qrUrl,
                    'pre_reg_id' => $preReg->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate QR code. Please try again.'
                ], 500);
            }

            // --- Send Confirmation Email ---
            try {
                Mail::to($request->email)->send(new PreRegistrationSubmittedMail($preReg));
                Log::info('Email sent successfully', ['to' => $request->email, 'pre_reg_id' => $preReg->id]);
            } catch (\Exception $mailException) {
                Log::error('Email sending failed', [
                    'error' => $mailException->getMessage(),
                    'trace' => $mailException->getTraceAsString(),
                    'email' => $request->email,
                    'pre_reg_id' => $preReg->id
                ]);

                // Proceed but warn user
                return response()->json([
                    'success' => true,
                    'warning' => 'Application submitted, but we could not send the confirmation email. Please contact support.',
                    'id' => $preReg->id
                ]);
            }

            // Success response
            return response()->json([
                'success' => true,
                'message' => 'Your pre-registration has been confirmed!',
                'id' => $preReg->id,
                'validity_hours' => 24
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
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get all pre-registrations (for admin).
     */
    public function index()
    {
        $labelMap = [
            'tax_declaration' => 'Certified True Copy of Tax Declaration',
            'no_improvement' => 'Certification of No Improvement',
            'property_holdings' => 'Certification of Property Holdings',
            'non_property_holdings' => 'Certification of Non-property Holdings'
        ];

        $regs = PreRegistration::orderBy('created_at', 'desc')->get();

        return response()->json($regs->map(function ($reg) use ($labelMap) {
            $reg->service_type_label = $labelMap[$reg->service_type] ?? $reg->service_type;
            return $reg;
        }));
    }
}