<h1>{{ __('gift.mail.received_heading') }}</h1>
<p>{{ __('gift.mail.received_greeting') }}</p>
<p><strong>{{ $senderName }}</strong> {{ __('gift.mail.received_intro', ['plan' => $gift->plan->name, 'days' => $gift->duration_days]) }}</p>

@if ($gift->message)
    <blockquote style="border-left: 4px solid #ccc; padding-left: 16px; margin: 16px 0; color: #444;">
        {{ $gift->message }}
    </blockquote>
@endif

<p>
    <a href="{{ $claimUrl }}" style="display: inline-block; padding: 12px 24px; background: #6366f1; color: #fff; text-decoration: none; border-radius: 6px;">
        {{ __('gift.mail.received_cta') }}
    </a>
</p>

<p>{{ __('gift.mail.received_link_prefix') }} <br><a href="{{ $claimUrl }}">{{ $claimUrl }}</a></p>

<p style="color: #666; font-size: 14px;">{{ __('gift.mail.received_expires', ['date' => $gift->expires_at->format('d M Y')]) }}</p>
