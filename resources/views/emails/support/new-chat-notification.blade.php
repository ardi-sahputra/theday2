@component('mail::message')
# Chat baru dari {{ $userName }}

**{{ $userEmail }}** baru kirim chat di TheDay support.

**Pesan:**
> {{ $messageBody ?? '[Gambar dilampirkan]' }}

@if($hasImage)
**Gambar terlampir:**

<img src="{{ $imageUrl }}" alt="Attachment" style="max-width:400px;border-radius:8px;margin-top:8px"/>
@endif

@component('mail::button', ['url' => $adminChatUrl, 'color' => 'primary'])
Buka Chat di Dashboard Admin
@endcomponent

Atau reply langsung email ini — balasan akan masuk ke inbox user (`{{ $userEmail }}`).

Salam,<br>
{{ config('app.name') }}
@endcomponent
