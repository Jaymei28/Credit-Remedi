<?php

namespace App\Mail;

use App\Models\WaitlistUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WaitlistJoinedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(WaitlistUser $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('🎉 You’re on the Credit Remedi Waitlist!')
                    ->view('emails.waitlist_joined');
    }
}
