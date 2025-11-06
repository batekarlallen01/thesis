<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
use Illuminate\Support\Facades\Log;

class ReceiptPrintController extends Controller
{
    /**
     * Print queue receipt
     */
    public function printQueueReceipt(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'queue_number' => 'required|string',
                'full_name' => 'required|string',
                'service_type' => 'required|string',
                'applicant_type' => 'required|string',
                'number_of_copies' => 'required|integer',
                'priority_type' => 'required|string|in:Regular,Senior,PWD',
            ]);

        
            $printerName = "POS PRINTER"; 

            // Create printer connection
            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);

            // Start printing
            $this->printReceipt($printer, $validated);

            // Close printer connection
            $printer->close();

            return response()->json([
                'success' => true,
                'message' => 'Receipt printed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Receipt print error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to print receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format and print the receipt
     */
    private function printReceipt(Printer $printer, array $data)
    {
        // Initialize printer
        $printer->initialize();
        
        // ============================================
        // HEADER SECTION
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        
        // Print logo (optional - if you have logo image)
        try {
            $logoPath = public_path('img/mainlogo.png');
            if (file_exists($logoPath)) {
                $logo = EscposImage::load($logoPath, false);
                $printer->bitImage($logo);
                $printer->feed(1);
            }
        } catch (\Exception $e) {
            // Skip logo if error
            Log::warning('Logo print skipped: ' . $e->getMessage());
        }

        // City Hall Name
        $printer->setEmphasis(true);
        $printer->text("NORTH CALOOCAN CITY HALL\n");
        $printer->setEmphasis(false);
        $printer->text("Kiosk Queue System\n");
        $printer->text(date('M d, Y h:i A') . "\n");
        
        // Separator line
        $printer->text(str_repeat("-", 32) . "\n");
        $printer->feed(1);

        // ============================================
        // QUEUE NUMBER SECTION (Large and Bold)
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("YOUR QUEUE NUMBER\n");
        $printer->feed(1);
        
        // Large queue number
        $printer->setTextSize(4, 4);
        $printer->setEmphasis(true);
        $printer->text($data['queue_number'] . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->feed(1);

        // Priority badge if applicable
        if ($data['priority_type'] !== 'Regular') {
            $printer->setInvert(true);
            $printer->text(" *** " . $data['priority_type'] . " PRIORITY *** \n");
            $printer->setInvert(false);
            $printer->feed(1);
        }

        $printer->text(str_repeat("-", 32) . "\n");
        $printer->feed(1);

        // ============================================
        // CUSTOMER INFORMATION
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        
        // Name
        $printer->setEmphasis(true);
        $printer->text("Name:\n");
        $printer->setEmphasis(false);
        $printer->text($this->wordWrap($data['full_name'], 32) . "\n");
        $printer->feed(1);

        // Service Type
        $printer->setEmphasis(true);
        $printer->text("Service:\n");
        $printer->setEmphasis(false);
        $printer->text($this->wordWrap($this->getServiceTypeName($data['service_type']), 32) . "\n");
        $printer->feed(1);

        // Applicant Type
        $printer->setEmphasis(true);
        $printer->text("Type: ");
        $printer->setEmphasis(false);
        $printer->text($data['applicant_type'] === 'owner' ? 'Owner' : 'Representative');
        $printer->text("\n\n");

        // Number of Copies
        $printer->setEmphasis(true);
        $printer->text("Copies: ");
        $printer->setEmphasis(false);
        $printer->text($data['number_of_copies'] . "\n");

        $printer->feed(1);
        $printer->text(str_repeat("-", 32) . "\n");
        $printer->feed(1);

        // ============================================
        // FOOTER SECTION
        // ============================================
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Please keep this receipt\n");
        $printer->text("Wait for your number to be called\n");
        $printer->feed(1);
        $printer->setEmphasis(true);
        $printer->text("Thank you for using our service!\n");
        $printer->setEmphasis(false);

        // Feed and cut paper
        $printer->feed(3);
        $printer->cut();
    }

    /**
     * Get readable service type name
     */
    private function getServiceTypeName($serviceType)
    {
        $serviceNames = [
            'tax_declaration' => 'Tax Declaration (TD)',
            'no_improvement' => 'Certification of No Improvement',
            'property_holdings' => 'Certification of Property Holdings',
            'non_property_holdings' => 'Certification of Non-property Holdings'
        ];

        return $serviceNames[$serviceType] ?? $serviceType;
    }

    /**
     * Word wrap text to fit receipt width
     */
    private function wordWrap($text, $width = 32)
    {
        return wordwrap($text, $width, "\n", true);
    }

    /**
     * Test print function
     */
    public function testPrint()
    {
        try {
            $printerName = "POS PRINTER"; // Change this to your printer name
            
            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);

            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("TEST PRINT\n");
            $printer->setTextSize(1, 1);
            $printer->text("Printer is working!\n");
            $printer->feed(2);
            $printer->cut();
            $printer->close();

            return response()->json([
                'success' => true,
                'message' => 'Test print successful!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test print failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}