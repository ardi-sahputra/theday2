<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerRevokedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $ownerName)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Akses ke akun TheDay dicabut');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.partner-revoked', with: ['ownerName' => $this->ownerName]);
    }
}
