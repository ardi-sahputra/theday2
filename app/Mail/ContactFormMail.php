<?php

// app/Mail/ContactFormMail.php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $topic,
        public readonly string $messageBody,
        public readonly string $submittedAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Kontak Theday] {$this->topic} — dari {$this->senderName}",
            replyTo: [new \Illuminate\Mail\Mailables\Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-form',
        );
    }
}
