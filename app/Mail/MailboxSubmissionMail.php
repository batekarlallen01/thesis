<?php

namespace App\Mail;

use App\Models\MailboxSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailboxSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $submission;

    public function __construct(MailboxSubmission $submission)
    {
        $this->submission = $submission;
    }

    public function build()
    {
        return $this->subject('Document Drop-off Application Confirmation - PIN: ' . $this->submission->pin_code)
                    ->view('emails.mailbox-submission-confirmation');
    }
}