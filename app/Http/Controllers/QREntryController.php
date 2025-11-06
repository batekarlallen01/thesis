<?php

namespace App\Http\Controllers;

use App\Models\PreRegistration;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

class QREntryController extends Controller
{
    /**
     * Validate QR token and get pre-registration details
     */
    public function validateQR(Request $request)
    {
        try {
            $validated = $request->validate([
                'qr_token' => 'required|string|exists:pre_registrations,qr_token'
            ]);

            $preReg = PreRegistration::where('qr_token', $validated['qr_token'])->first();

            if (!$preReg) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code'
                ], 404);
            }

            // Check if QR has expired
            if ($preReg->qr_expires_at && $preReg->qr_expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code has expired. Please register again.'
                ], 400);
            }

            // Check if already entered queue
            if ($preReg->has_entered_queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already entered the queue.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $preReg->id,
                    'full_name' => $preReg->full_name,
                    'service_type' => $preReg->service_type,
                    'qr_token' => $preReg->qr_token
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('QR validation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error validating QR code',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Process QR entry and create queue entry
     */
    public function processQREntry(Request $request)
    {
        try {
            $validated = $request->validate([
                'qr_token' => 'required|string|exists:pre_registrations,qr_token'
            ]);

            DB::beginTransaction();

            try {
                // Get pre-registration record
                $preReg = PreRegistration::where('qr_token', $validated['qr_token'])->first();

                if (!$preReg) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Pre-registration not found.'
                    ], 404);
                }

                // Double-check: prevent duplicate entries
                if ($preReg->has_entered_queue) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'This QR code has already been used.'
                    ], 400);
                }

                // Check if QR expired
                if ($preReg->qr_expires_at && $preReg->qr_expires_at < now()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'QR code has expired.'
                    ], 400);
                }

                // Generate queue number
                $queueNumber = Queue::generateQueueNumber();
                Log::info('Generated queue number for QR entry: ' . $queueNumber);

                // Calculate priority type using the method
                $priorityType = $preReg->getPriorityType();

                // Extract PIN numbers from array
                $pinNumbers = $preReg->pin_numbers ?? [];
                $pinLand = $pinNumbers['land'] ?? null;
                $pinBuilding = $pinNumbers['building'] ?? null;
                $pinMachinery = $pinNumbers['machinery'] ?? null;

                // Create queue entry with ALL form fields
                $queue = Queue::create([
                    'queue_number' => $queueNumber,
                    'full_name' => $preReg->full_name,
                    'email' => $preReg->email,
                    'contact' => null,
                    'birthdate' => null,
                    'age' => $preReg->age,
                    'service_type' => $preReg->service_type,
                    'is_pwd' => $preReg->is_pwd,
                    'pwd_id' => $preReg->pwd_id,
                    'senior_id' => null,
                    'priority_type' => $priorityType,
                    'entry_type' => 'pre_registration',
                    'status' => 'waiting',
                    'queue_entered_at' => now(),
                    'pre_registration_id' => $preReg->id,
                    'number_of_copies' => $preReg->number_of_copies,
                    'purpose' => $preReg->purpose,
                    'address' => $preReg->address,
                    'applicant_type' => $preReg->applicant_type,
                    'govt_id_type' => $preReg->govt_id_type,
                    'govt_id_number' => $preReg->govt_id_number,
                    'issued_at' => $preReg->issued_at,
                    'issued_on' => $preReg->issued_on,
                    'pin_land' => $pinLand,
                    'pin_building' => $pinBuilding,
                    'pin_machinery' => $pinMachinery,
                    'form_data' => [
                        'applicant_type' => $preReg->applicant_type,
                        'number_of_copies' => $preReg->number_of_copies,
                        'pin_numbers' => $preReg->pin_numbers,
                        'purpose' => $preReg->purpose,
                        'address' => $preReg->address,
                        'govt_id_type' => $preReg->govt_id_type,
                        'govt_id_number' => $preReg->govt_id_number,
                        'issued_at' => $preReg->issued_at,
                        'issued_on' => $preReg->issued_on
                    ]
                ]);

                Log::info('Queue entry created for QR scan', [
                    'queue_id' => $queue->id,
                    'pre_registration_id' => $preReg->id,
                    'queue_number' => $queueNumber,
                    'priority_type' => $priorityType
                ]);

                // Mark pre-registration as entered queue
                $preReg->markAsEntered();

                DB::commit();

                // AUTO-PRINT RECEIPT (After successful database commit)
                try {
                    $this->printReceipt([
                        'queue_number' => $queue->queue_number,
                        'full_name' => $queue->full_name,
                        'service_type' => $queue->service_type,
                        'applicant_type' => $queue->applicant_type,
                        'number_of_copies' => $queue->number_of_copies,
                        'priority_type' => $queue->priority_type,
                    ]);
                    Log::info('Receipt printed successfully for QR queue: ' . $queue->queue_number);
                } catch (\Exception $printError) {
                    // Log print error but don't fail the request
                    Log::error('Print error (non-critical): ' . $printError->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully added to queue',
                    'data' => [
                        'queue_id' => $queue->id,
                        'queue_number' => $queue->queue_number,
                        'full_name' => $queue->full_name,
                        'priority_type' => $queue->priority_type,
                        'service_type' => $preReg->service_type
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('QR entry processing error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process QR entry',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Print receipt using ESC/POS
     */
    private function printReceipt(array $data)
    {
        // 🔴 CHANGE THIS TO YOUR PRINTER NAME
        $printerName = "POS PRINTER";

        $connector = new WindowsPrintConnector($printerName);
        $printer = new Printer($connector);

        // Initialize printer
        $printer->initialize();
        
        // ============================================
        // HEADER
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        
        // Try to print logo
        try {
            $logoPath = public_path('img/mainlogo.png');
            if (file_exists($logoPath)) {
                $logo = EscposImage::load($logoPath, false);
                $printer->bitImage($logo);
                $printer->feed(1);
            }
        } catch (\Exception $e) {
            // Skip logo if error
        }

        $printer->setEmphasis(true);
        $printer->text("NORTH CALOOCAN CITY HALL\n");
        $printer->setEmphasis(false);
        $printer->text("Kiosk Queue System\n");
        $printer->text(date('M d, Y h:i A') . "\n");
        $printer->text(str_repeat("-", 32) . "\n");
        $printer->feed(1);

        // ============================================
        // QUEUE NUMBER (LARGE)
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("YOUR QUEUE NUMBER\n");
        $printer->feed(1);
        
        $printer->setTextSize(4, 4);
        $printer->setEmphasis(true);
        $printer->text($data['queue_number'] . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->feed(1);


// Priority badge
if ($data['priority_type'] !== 'Regular') {
    $printer->setEmphasis(true);
    $printer->text("*** " . strtoupper($data['priority_type']) . " PRIORITY ***\n");
    $printer->setEmphasis(false);
    $printer->feed(1);
}

        $printer->text(str_repeat("-", 32) . "\n");
        $printer->feed(1);

        // ============================================
        // CUSTOMER INFO
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        
        $printer->setEmphasis(true);
        $printer->text("Name:\n");
        $printer->setEmphasis(false);
        $printer->text(wordwrap($data['full_name'], 32, "\n", true) . "\n\n");

        $printer->setEmphasis(true);
        $printer->text("Service:\n");
        $printer->setEmphasis(false);
        $printer->text(wordwrap($this->getServiceTypeName($data['service_type']), 32, "\n", true) . "\n\n");

        $printer->setEmphasis(true);
        $printer->text("Type: ");
        $printer->setEmphasis(false);
        $printer->text($data['applicant_type'] === 'owner' ? 'Owner' : 'Representative');
        $printer->text("\n\n");

        $printer->setEmphasis(true);
        $printer->text("Copies: ");
        $printer->setEmphasis(false);
        $printer->text($data['number_of_copies'] . "\n");

        $printer->feed(1);
        $printer->text(str_repeat("-", 32) . "\n");
        $printer->feed(1);

        // ============================================
        // FOOTER
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Please keep this receipt\n");
        $printer->text("Wait for your number to be called\n");
        $printer->feed(1);
        $printer->setEmphasis(true);
        $printer->text("Thank you!\n");
        $printer->setEmphasis(false);

        $printer->feed(3);
        $printer->cut();
        $printer->close();
    }

    /**
     * Get readable service type name
     */
    private function getServiceTypeName($serviceType)
    {
        $names = [
            'tax_declaration' => 'Tax Declaration (TD)',
            'no_improvement' => 'No Improvement Cert.',
            'property_holdings' => 'Property Holdings Cert.',
            'non_property_holdings' => 'Non-property Holdings Cert.'
        ];
        return $names[$serviceType] ?? $serviceType;
    }

    /**
     * Display QR scanner page
     */
    public function scannerPage()
    {
        return view('queue.scanner');
    }
}