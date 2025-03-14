<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailVerifyOtp extends Mailable
{
    use Queueable, SerializesModels;

    private  $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from("karanofficial9@gmail.com")->subject('Otp Verify Mail')->view('emails.verify_mail')->with([
                'token' => $this->token,
            ]);
    }
}