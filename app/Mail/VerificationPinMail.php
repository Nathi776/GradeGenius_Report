<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent straight away (not queued) so the PIN arrives while the person is still on the page.
 */
class VerificationPinMail extends Mailable
{
    public function __construct(
        public User $user,
        public string $pin,
        public int $validMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "{$this->pin} is your GradeGenius verification code");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verification-pin');
    }
}
