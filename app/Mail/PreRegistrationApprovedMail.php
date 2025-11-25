<?php

namespace App\Mail;

use App\Models\PreRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PreRegistrationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $preReg;

    public function __construct(PreRegistration $preReg)
    {
        $this->preReg = $preReg;
        
        Log::info('PreRegistrationApprovedMail created', [
            'id' => $preReg->id,
            'qr_image_path' => $preReg->qr_image_path
        ]);
    }

    public function build()
    {
        $qrPath = storage_path("app/public/qrcodes/{$this->preReg->qr_image_path}");
        
        Log::info('Building email with QR attachment', [
            'qr_path' => $qrPath,
            'exists' => file_exists($qrPath),
            'file_size' => file_exists($qrPath) ? filesize($qrPath) : 0
        ]);
        
        return $this->view('emails.pre-registration-approved')
                    ->subject('Pre-Registration Approved - QR Code for Queueing')
                    ->attach($qrPath, [
                        'as' => 'QR-Code.png',
                        'mime' => 'image/png',
                    ]);
    }
}