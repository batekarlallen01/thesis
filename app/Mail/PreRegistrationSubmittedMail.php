<?php

namespace App\Mail;

use App\Models\PreRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreRegistrationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $preReg;

    public function __construct(PreRegistration $preReg)
    {
        $this->preReg = $preReg;
    }

    public function build()
    {
        return $this->subject("✅ Your Queue Pre-Registration Confirmation – #{$this->preReg->id}")
                    ->view('emails.pre-registration-confirmation')
                    ->attach(storage_path('app/public/qrcodes/' . $this->preReg->qr_image_path), [
                        'as' => 'your-queue-qr.png',
                        'mime' => 'image/png'
                    ]);
    }
}