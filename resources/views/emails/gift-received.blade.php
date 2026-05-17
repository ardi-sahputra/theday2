<h1>Selamat! Kamu dapat gift premium 🎁</h1>
<p>Halo,</p>
<p><strong>{{ $senderName }}</strong> mengirimkan kamu akses Premium {{ $gift->plan->name }} selama {{ $gift->duration_days }} hari.</p>

@if ($gift->message)
    <blockquote style="border-left: 4px solid #ccc; padding-left: 16px; margin: 16px 0; color: #444;">
        {{ $gift->message }}
    </blockquote>
@endif

<p>
    <a href="{{ $claimUrl }}" style="display: inline-block; padding: 12px 24px; background: #6366f1; color: #fff; text-decoration: none; border-radius: 6px;">
        Klaim Gift Sekarang
    </a>
</p>

<p>Atau buka link berikut: <br><a href="{{ $claimUrl }}">{{ $claimUrl }}</a></p>

<p style="color: #666; font-size: 14px;">Gift ini berlaku sampai {{ $gift->expires_at->format('d M Y') }}. Setelah itu kode akan kadaluarsa.</p>
