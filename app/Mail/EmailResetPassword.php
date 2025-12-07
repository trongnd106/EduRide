<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subjectDefault = 'Email reset password';
        $subject = (isset($this->data['subject']) && $this->data['subject'] != "")?$this->data['subject']:$subjectDefault;
        return $this
        ->subject($subject)
        ->view('emails.emailResetPassword')
        ->with('data', $this->data);
    }
}
