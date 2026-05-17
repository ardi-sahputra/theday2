<h1>Gift kamu sudah diklaim!</h1>
<p>Halo {{ $gift->sender->name }},</p>
<p>{{ $recipient->name }} ({{ $recipient->email }}) baru saja mengklaim gift premium kamu pada {{ $gift->claimed_at->format('d M Y, H:i') }}.</p>
<p>Terima kasih sudah menyebarkan kebahagiaan!</p>
