<?php

namespace App\Mail;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewChatNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportConversation $conversation,
        public SupportMessage $message,
    ) {}

    public function envelope(): Envelope
    {
        $userName = $this->conversation->user->name;
        $preview  = Str::limit($this->message->body ?? '[gambar]', 60);

        return new Envelope(
            subject: "💬 Chat baru dari {$userName} — {$preview}",
            replyTo: [
                new Address($this->conversation->user->email, $userName),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support.new-chat-notification',
            with: [
                'userName'     => $this->conversation->user->name,
                'userEmail'    => $this->conversation->user->email,
                'messageBody'  => $this->message->body,
                'hasImage'     => (bool) $this->message->attachment_path,
                'imageUrl'     => $this->message->attachment_path
                    ? Storage::disk('public')->url($this->message->attachment_path)
                    : null,
                'adminChatUrl' => route('admin.support.show', $this->conversation),
            ],
        );
    }
}
