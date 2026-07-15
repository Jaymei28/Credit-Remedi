<?php

namespace App\Mail;

use App\Models\WaitlistUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReferralMilestoneMail extends Mailable
{
    use Queueable, SerializesModels;

    public $referrer;

    public function __construct(WaitlistUser $referrer)
    {
        $this->referrer = $referrer;
    }

    public function build()
    {
        return $this->subject('🎉 You’ve unlocked Founding Member access!')
                    ->view('emails.referral_milestone');
    }
}

