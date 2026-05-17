<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Gift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Gift $gift) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('gift.mail.received_subject'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gift-received',
            with: [
                'claimUrl'   => route('gift.claim.show', $this->gift->code),
                'senderName' => $this->gift->source === 'user' && $this->gift->sender
                    ? $this->gift->sender->name
                    : __('gift.common.tim_theday'),
            ]
        );
    }
}
