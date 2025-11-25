<?php

namespace App\Mail;

use App\Models\PreRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreRegistrationDisapprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $preReg;
    public $incorrectDocuments;
    public $otherReason;

    public function __construct(PreRegistration $preReg, array $incorrectDocuments, ?string $otherReason = null)
    {
        $this->preReg = $preReg;
        $this->incorrectDocuments = $incorrectDocuments;
        $this->otherReason = $otherReason;
    }

    public function build()
    {
        return $this->view('emails.pre-registration-disapproved')
                    ->subject('Pre-Registration Disapproved - Action Required');
    }
}