<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesan Kontak Baru — TheDay</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f0e8; font-family:Georgia,'Times New Roman',serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f0e8; padding:40px 16px;">
<tr><td align="center">

  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
      <td style="background:linear-gradient(160deg,#1A2720 0%,#243830 60%,#2E4A3C 100%); padding:36px 40px 32px;">

        <!-- Logo -->
        <table cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr>
            <td style="width:36px; height:36px; background:#73877C; border-radius:10px; text-align:center; vertical-align:middle;">
              <span style="color:#FFFCF7; font-family:Georgia,serif; font-size:20px; font-weight:600; line-height:36px; letter-spacing:-1px;">d<span style="color:#C8A26B;">.</span></span>
            </td>
            <td style="padding-left:10px;">
              <span style="color:#ffffff; font-size:20px; font-weight:600; font-family:Georgia,serif; letter-spacing:-0.3px;">TheDay</span>
            </td>
          </tr>
        </table>

        <!-- Icon -->
        <table cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
          <tr>
            <td style="width:64px; height:64px; background:rgba(146,168,156,0.18); border:1px solid rgba(146,168,156,0.25); border-radius:16px; text-align:center; vertical-align:middle;">
              <span style="font-size:28px; line-height:64px;">💬</span>
            </td>
          </tr>
        </table>

        <p style="margin:0 0 6px; color:rgba(184,199,191,0.6); font-size:11px; font-weight:600; letter-spacing:0.12em; text-transform:uppercase; font-family:Arial,sans-serif;">Pesan Masuk</p>
        <h1 style="margin:0; color:#ffffff; font-size:26px; font-weight:600; line-height:1.3; font-family:Georgia,serif;">
          Pesan kontak baru<br><em style="font-style:italic; color:#B8C7BF;">diterima.</em>
        </h1>
        <p style="margin:10px 0 0; color:rgba(184,199,191,0.5); font-size:13px; font-family:Arial,sans-serif;">{{ $submittedAt }}</p>

      </td>
    </tr>

    <!-- Accent bar -->
    <tr>
      <td style="height:4px; background:linear-gradient(90deg,#92A89C 0%,#B8C7BF 50%,#92A89C 100%);"></td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="padding:36px 40px 28px;">

        <!-- Sender info card -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#F7FAF8; border:1px solid #D5E0DB; border-radius:14px; padding:20px 24px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding-bottom:12px; border-bottom:1px solid #E8F0EC;">
                    <p style="margin:0 0 2px; font-size:11px; font-weight:600; color:#73877C; text-transform:uppercase; letter-spacing:0.1em; font-family:Arial,sans-serif;">Nama</p>
                    <p style="margin:0; font-size:15px; color:#2C2417; font-family:Georgia,serif; font-weight:600;">{{ $senderName }}</p>
                  </td>
                </tr>
                <tr>
                  <td style="padding-top:12px; padding-bottom:12px; border-bottom:1px solid #E8F0EC;">
                    <p style="margin:0 0 2px; font-size:11px; font-weight:600; color:#73877C; text-transform:uppercase; letter-spacing:0.1em; font-family:Arial,sans-serif;">Email</p>
                    <a href="mailto:{{ $senderEmail }}" style="font-size:15px; color:#92A89C; text-decoration:none; font-family:Arial,sans-serif;">{{ $senderEmail }}</a>
                  </td>
                </tr>
                <tr>
                  <td style="padding-top:12px;">
                    <p style="margin:0 0 2px; font-size:11px; font-weight:600; color:#73877C; text-transform:uppercase; letter-spacing:0.1em; font-family:Arial,sans-serif;">Topik</p>
                    <p style="margin:0; font-size:15px; color:#2C2417; font-family:Arial,sans-serif;">{{ $topic }}</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <!-- Message -->
        <p style="margin:0 0 8px; font-size:11px; font-weight:600; color:#73877C; text-transform:uppercase; letter-spacing:0.1em; font-family:Arial,sans-serif;">Isi Pesan</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr>
            <td style="background:#FFFCF7; border:1px solid #EDE8E0; border-left:3px solid #92A89C; border-radius:0 10px 10px 0; padding:18px 20px;">
              <p style="margin:0; font-size:15px; color:#4A3728; line-height:1.8; font-family:Arial,sans-serif; white-space:pre-wrap;">{{ $messageBody }}</p>
            </td>
          </tr>
        </table>

        <!-- CTA -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td align="center">
              <a href="mailto:{{ $senderEmail }}?subject=Re:%20{{ rawurlencode($topic) }}"
                 style="display:inline-block; background:#92A89C; color:#ffffff; text-decoration:none; font-family:Arial,sans-serif; font-size:14px; font-weight:600; padding:13px 36px; border-radius:12px; letter-spacing:0.01em;">
                Balas Pesan →
              </a>
            </td>
          </tr>
        </table>

        <p style="margin:0; font-size:12px; color:#B0A090; font-family:Arial,sans-serif; line-height:1.6; text-align:center;">
          Dikirim dari formulir kontak TheDay pada {{ $submittedAt }}
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
              <p style="margin:0 0 4px; font-size:13px; font-weight:600; color:#2C2417; font-family:Georgia,serif;">TheDay</p>
              <p style="margin:0; font-size:12px; color:#B0A090; font-family:Arial,sans-serif;">Platform Undangan Pernikahan Digital Indonesia</p>
            </td>
            <td align="right" style="vertical-align:top;">
              <a href="mailto:hello@theday.id" style="font-size:12px; color:#92A89C; text-decoration:none; font-family:Arial,sans-serif;">hello@theday.id</a>
            </td>
          </tr>
        </table>
        <p style="margin:16px 0 0; font-size:11px; color:#C8BEB0; font-family:Arial,sans-serif; line-height:1.6;">
          © {{ date('Y') }} TheDay. Semua hak dilindungi.
        </p>
      </td>
    </tr>

  </table>

</td></tr>
</table>

</body>
</html>
