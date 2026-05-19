<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $ownerName,
        public string $token,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "{$this->ownerName} mengundang kamu di TheDay");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-invite',
            with: [
                'ownerName'  => $this->ownerName,
                'acceptUrl'  => url('/couple/accept/' . $this->token),
                'expiresIn'  => '7 hari',
            ],
        );
    }
}
