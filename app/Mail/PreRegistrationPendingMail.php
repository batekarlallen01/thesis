<?php

namespace App\Mail;

use App\Models\PreRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreRegistrationPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $preReg;

    public function __construct(PreRegistration $preReg)
    {
        $this->preReg = $preReg;
    }

    public function build()
    {
        return $this->view('emails.pre-registration-confirmation-pending')
                    ->subject('Pre-Registration Received - Under Review');
    }
}