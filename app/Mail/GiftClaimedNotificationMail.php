<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Gift;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftClaimedNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Gift $gift, public User $recipient) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('gift.mail.claimed_subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.gift-claimed-notification');
    }
}
