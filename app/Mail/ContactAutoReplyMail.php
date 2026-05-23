<?php

// app/Mail/ContactAutoReplyMail.php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactAutoReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $senderName,
        public readonly string $topic,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesan kamu sudah kami terima — Theday',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-autoreply',
        );
    }
}
