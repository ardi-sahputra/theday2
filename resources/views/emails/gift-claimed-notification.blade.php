<h1>{{ __('gift.mail.claimed_heading') }}</h1>
<p>{{ __('gift.mail.claimed_greeting', ['name' => $gift->sender->name]) }}</p>
<p>{{ __('gift.mail.claimed_body', ['recipient' => $recipient->name, 'email' => $recipient->email, 'date' => $gift->claimed_at->format('d M Y, H:i')]) }}</p>
<p>{{ __('gift.mail.claimed_thanks') }}</p>
