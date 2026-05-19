<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerLinkedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $partnerName)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "{$this->partnerName} menerima undangan kamu");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-linked',
            with: ['partnerName' => $this->partnerName],
        );
    }
}
