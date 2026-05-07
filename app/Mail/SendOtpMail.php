<?php

namespace App\Mail;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly User    $user,
        public readonly OtpCode $otp,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->otp->purpose) {
            'password_reset'       => 'Reset Password - ' . config('app.name'),
            'email_verification'   => 'Kode Verifikasi Email - ' . config('app.name'),
            default                => 'Kode OTP - ' . config('app.name'),
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.otp',
        );
    }
}
