<?php

namespace App\Http\Controllers;

use App\Models\KioskEntry;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

class KioskEntryController extends Controller
{
    /**
     * Store kiosk entry and add to queue
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:120',
                'is_pwd' => 'required|boolean',
                'pwd_id' => 'required_if:is_pwd,true|nullable|string|max:50',
                'applicant_type' => 'required|in:owner,representative',
                'service_type' => 'required|in:tax_declaration,no_improvement,property_holdings,non_property_holdings',
                'number_of_copies' => 'required|integer|min:1',
                'pin_land' => 'nullable|string|max:100',
                'pin_building' => 'nullable|string|max:100',
                'pin_machinery' => 'nullable|string|max:100',
                'purpose' => 'required|string',
                'address' => 'required|string',
                'govt_id_type' => 'required|string|max:100',
                'govt_id_number' => 'required|string|max:100',
                'issued_at' => 'nullable|string|max:100',
                'issued_on' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Use database transaction to ensure both records are created
            DB::beginTransaction();

            try {
                // 1. Generate queue number first
                $queueNumber = Queue::generateQueueNumber();
                Log::info('Generated queue number: ' . $queueNumber);

                // 2. Determine priority type
                $priorityType = $this->determinePriorityType($data['age'], $data['is_pwd']);
                Log::info('Priority type: ' . $priorityType);

                // 3. Create Queue Entry
                $queue = Queue::create([
                    'queue_number' => $queueNumber,
                    'full_name' => $data['full_name'],
                    'email' => null,
                    'birthdate' => null,
                    'age' => $data['age'],
                    'service_type' => $data['service_type'],
                    'is_pwd' => $data['is_pwd'],
                    'pwd_id' => $data['pwd_id'] ?? null,
                    'senior_id' => null,
                    'priority_type' => $priorityType,
                    'entry_type' => 'direct',
                    'status' => 'waiting',
                    'queue_entered_at' => now(),
                    'number_of_copies' => $data['number_of_copies'],
                    'purpose' => $data['purpose'],
                    'address' => $data['address'],
                    'applicant_type' => $data['applicant_type'],
                    'govt_id_type' => $data['govt_id_type'],
                    'govt_id_number' => $data['govt_id_number'],
                    'issued_at' => $data['issued_at'] ?? null,
                    'issued_on' => $data['issued_on'] ?? null,
                    'pin_land' => $data['pin_land'] ?? null,
                    'pin_building' => $data['pin_building'] ?? null,
                    'pin_machinery' => $data['pin_machinery'] ?? null,
                    'form_data' => [
                        'applicant_type' => $data['applicant_type'],
                        'number_of_copies' => $data['number_of_copies'],
                        'pin_land' => $data['pin_land'] ?? null,
                        'pin_building' => $data['pin_building'] ?? null,
                        'pin_machinery' => $data['pin_machinery'] ?? null,
                        'purpose' => $data['purpose'],
                        'address' => $data['address'],
                        'govt_id_type' => $data['govt_id_type'],
                        'govt_id_number' => $data['govt_id_number'],
                        'issued_at' => $data['issued_at'] ?? null,
                        'issued_on' => $data['issued_on'] ?? null
                    ]
                ]);

                Log::info('Queue created with ID: ' . $queue->id);

                // 4. Create Kiosk Entry
                $kioskEntry = KioskEntry::create([
                    'full_name' => $data['full_name'],
                    'age' => $data['age'],
                    'is_pwd' => $data['is_pwd'],
                    'pwd_id' => $data['pwd_id'] ?? null,
                    'applicant_type' => $data['applicant_type'],
                    'service_type' => $data['service_type'],
                    'number_of_copies' => $data['number_of_copies'],
                    'pin_land' => $data['pin_land'] ?? null,
                    'pin_building' => $data['pin_building'] ?? null,
                    'pin_machinery' => $data['pin_machinery'] ?? null,
                    'purpose' => $data['purpose'],
                    'address' => $data['address'],
                    'govt_id_type' => $data['govt_id_type'],
                    'govt_id_number' => $data['govt_id_number'],
                    'issued_at' => $data['issued_at'] ?? null,
                    'issued_on' => $data['issued_on'] ?? null,
                    'status' => 'in_queue',
                    'queue_id' => $queue->id,
                    'priority_type' => $priorityType
                ]);

                Log::info('Kiosk entry created with ID: ' . $kioskEntry->id);

                DB::commit();

                // 5. AUTO-PRINT RECEIPT (After successful database commit)
                try {
                    $this->printReceipt([
                        'queue_number' => $queue->queue_number,
                        'full_name' => $queue->full_name,
                        'service_type' => $queue->service_type,
                        'applicant_type' => $queue->applicant_type,
                        'number_of_copies' => $queue->number_of_copies,
                        'priority_type' => $queue->priority_type,
                    ]);
                    Log::info('Receipt printed successfully for queue: ' . $queue->queue_number);
                } catch (\Exception $printError) {
                    // Log print error but don't fail the request
                    Log::error('Print error (non-critical): ' . $printError->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully added to queue',
                    'data' => [
                        'kiosk_entry_id' => $kioskEntry->id,
                        'queue_id' => $queue->id,
                        'queue_number' => $queue->queue_number,
                        'full_name' => $queue->full_name,
                        'priority_type' => $queue->priority_type,
                        'service_type' => $kioskEntry->service_type,
                        'receipt_printed' => true
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction error: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Kiosk entry error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process kiosk entry',
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
            $printer->setInvert(true);
            $printer->text(" *** " . $data['priority_type'] . " PRIORITY *** \n");
            $printer->setInvert(false);
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
     * Determine priority type based on age and PWD status
     */
    private function determinePriorityType($age, $isPwd): string
    {
        if ($isPwd) {
            return 'PWD';
        }
        
        if ($age >= 60) {
            return 'Senior';
        }
        
        return 'Regular';
    }

    /**
     * Get all kiosk entries (for admin)
     */
    public function index(Request $request)
    {
        try {
            $status = $request->query('status', 'all');
            
            $query = KioskEntry::with('queue')->orderBy('created_at', 'desc');
            
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            
            $entries = $query->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $entries
            ]);

        } catch (\Exception $e) {
            Log::error('Fetch kiosk entries error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch entries'], 500);
        }
    }

    /**
     * Get single kiosk entry
     */
    public function show($id)
    {
        try {
            $entry = KioskEntry::with('queue')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $entry
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Entry not found'], 404);
        }
    }
}