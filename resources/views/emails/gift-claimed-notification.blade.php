<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('gift.mail.claimed_subject') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f0e8; font-family:Georgia,'Times New Roman',serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f0e8; padding:40px 16px;">
<tr><td align="center">

  <!-- Card -->
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
      <td style="background:linear-gradient(160deg,#1A2720 0%,#243830 60%,#2E4A3C 100%); padding:36px 40px 32px;">

        <!-- Logo row -->
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td style="width:36px; height:36px; background:#73877C; border-radius:10px; text-align:center; vertical-align:middle;">
              <span style="color:#FFFCF7; font-family:Georgia,serif; font-size:20px; font-weight:600; line-height:36px; letter-spacing:-1px;">d<span style="color:#C8A26B;">.</span></span>
            </td>
            <td style="padding-left:10px;">
              <span style="color:#ffffff; font-size:20px; font-weight:600; font-family:Georgia,serif; letter-spacing:-0.3px;">Theday</span>
            </td>
          </tr>
        </table>

        <!-- Heading block -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px;">
          <tr>
            <td>
              <p style="margin:0 0 8px; color:rgba(184,199,191,0.7); font-size:11px; font-weight:600; letter-spacing:0.14em; text-transform:uppercase; font-family:Arial,sans-serif;">Gift Diklaim</p>
              <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:600; line-height:1.35; font-family:Georgia,serif;">
                {{ __('gift.mail.claimed_heading') }}
              </h1>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    <!-- Sage accent strip -->
    <tr>
      <td style="height:4px; background:linear-gradient(90deg,#92A89C 0%,#B8C7BF 50%,#92A89C 100%);"></td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="padding:36px 40px 28px;">

        <p style="margin:0 0 8px; font-size:15px; color:#6B5B3E; font-family:Arial,sans-serif; line-height:1.3;">{{ __('gift.mail.claimed_greeting', ['name' => $gift->sender->name]) }}</p>

        <p style="margin:0 0 24px; font-size:15px; color:#6B5B3E; font-family:Arial,sans-serif; line-height:1.75;">
          {{ __('gift.mail.claimed_body', ['recipient' => $recipient->name, 'email' => $recipient->email, 'date' => $gift->claimed_at->format('d M Y, H:i')]) }}
        </p>

        <!-- Highlight box: plan info -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
          <tr>
            <td style="background:#F7FAF8; border:1px solid #D5E0DB; border-radius:12px; padding:16px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="width:8px; background:#92A89C; border-radius:4px; vertical-align:top;">&nbsp;</td>
                  <td style="padding-left:14px;">
                    <p style="margin:0 0 4px; font-size:11px; font-weight:600; color:#73877C; text-transform:uppercase; letter-spacing:0.1em; font-family:Arial,sans-serif;">Paket Premium</p>
                    <p style="margin:0; font-size:14px; color:#2C2417; font-family:Arial,sans-serif; font-weight:600;">{{ $gift->plan->name }} &middot; {{ $gift->duration_days }} hari</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <p style="margin:0; font-size:14px; color:#6B5B3E; font-family:Arial,sans-serif; line-height:1.7;">
          {{ __('gift.mail.claimed_thanks') }}
        </p>

      </td>
    </tr>

    <!-- Divider -->
    <tr>
      <td style="padding:0 40px;">
        <hr style="border:none; border-top:1px solid #EDE8E0; margin:0;">
      </td>
    </tr>

    <!-- Footer -->
    <tr>
      <td style="padding:24px 40px 32px;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <p style="margin:0 0 4px; font-size:13px; font-weight:600; color:#2C2417; font-family:Georgia,serif;">Theday</p>
              <p style="margin:0; font-size:12px; color:#B0A090; font-family:Arial,sans-serif;">Platform Undangan Pernikahan Digital Indonesia</p>
            </td>
            <td align="right" style="vertical-align:top;">
              <a href="mailto:hello@theday.id" style="font-size:12px; color:#92A89C; text-decoration:none; font-family:Arial,sans-serif;">hello@theday.id</a>
            </td>
          </tr>
        </table>
        <p style="margin:16px 0 0; font-size:11px; color:#C8BEB0; font-family:Arial,sans-serif; line-height:1.6;">
          Email ini dikirim otomatis. Mohon jangan membalas email ini langsung.<br>
          &copy; {{ date('Y') }} Theday. Semua hak dilindungi.
        </p>
      </td>
    </tr>

  </table>
  <!-- /Card -->

</td></tr>
</table>

</body>
</html>
