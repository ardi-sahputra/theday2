<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verifikasi Email — TheDay</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f0e8; font-family:Georgia,'Times New Roman',serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f0e8; padding:40px 16px;">
<tr><td align="center">

  <!-- Card -->
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">

    <!-- Header -->
    <tr>
      <td style="background:linear-gradient(160deg,#1A2720 0%,#243830 60%,#2E4A3C 100%); padding:0;">

        <!-- Dot pattern via repeating gradient -->
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="padding:36px 40px 32px;">

              <!-- Logo row -->
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="vertical-align:middle;">
                    <table cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="width:36px; height:36px; background:#73877C; border-radius:10px; text-align:center; vertical-align:middle;">
                          <span style="color:#FFFCF7; font-family:Georgia,serif; font-size:20px; font-weight:600; line-height:36px; letter-spacing:-1px;">d<span style="color:#C8A26B;">.</span></span>
                        </td>
                        <td style="padding-left:10px;">
                          <span style="color:#ffffff; font-size:20px; font-weight:600; font-family:Georgia,serif; letter-spacing:-0.3px;">TheDay</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Envelope icon area -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px;">
                <tr>
                  <td>
                    <!-- Icon box -->
                    <table cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="width:64px; height:64px; background:rgba(146,168,156,0.18); border:1px solid rgba(146,168,156,0.25); border-radius:16px; text-align:center; vertical-align:middle;">
                          <span style="font-size:28px; line-height:64px;">✉</span>
                        </td>
                      </tr>
                    </table>

                    <p style="margin:16px 0 6px; color:rgba(184,199,191,0.6); font-size:11px; font-weight:600; letter-spacing:0.12em; text-transform:uppercase; font-family:Arial,sans-serif;">Verifikasi Akun</p>
                    <h1 style="margin:0; color:#ffffff; font-size:26px; font-weight:600; line-height:1.3; font-family:Georgia,serif;">
                      Konfirmasi alamat<br><em style="font-style:italic; color:#B8C7BF;">email Anda</em>
                    </h1>
                  </td>
                </tr>
              </table>

            </td>
          </tr>
        </table>

      </td>
    </tr>

    <!-- Divider accent -->
    <tr>
      <td style="height:4px; background:linear-gradient(90deg, #92A89C 0%, #B8C7BF 50%, #92A89C 100%);"></td>
    </tr>

    <!-- Body -->
    <tr>
      <td style="padding:36px 40px 28px;">

        <p style="margin:0 0 8px; font-size:15px; color:#6B5B3E; font-family:Arial,sans-serif; line-height:1.3;">Hei, <strong style="color:#2C2417;">{{ $user->name }}</strong> 👋</p>

        <p style="margin:0 0 20px; font-size:15px; color:#6B5B3E; font-family:Arial,sans-serif; line-height:1.75;">
          Terima kasih sudah mendaftar di <strong style="color:#2C2417;">TheDay</strong> — platform undangan pernikahan digital. Klik tombol di bawah untuk memverifikasi email dan mengaktifkan akun Anda.
        </p>

        <!-- Info box -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
          <tr>
            <td style="background:#F7FAF8; border:1px solid #D5E0DB; border-radius:12px; padding:16px 20px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="width:8px; background:#92A89C; border-radius:4px; vertical-align:top;">&nbsp;</td>
                  <td style="padding-left:14px;">
                    <p style="margin:0 0 4px; font-size:12px; font-weight:600; color:#73877C; text-transform:uppercase; letter-spacing:0.08em; font-family:Arial,sans-serif;">Penting</p>
                    <p style="margin:0; font-size:13px; color:#4A7060; line-height:1.6; font-family:Arial,sans-serif;">
                      Tautan ini hanya berlaku selama <strong>60 menit</strong>. Jika sudah kedaluwarsa, Anda dapat meminta tautan baru dari halaman verifikasi.
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <!-- CTA Button -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0;">
          <tr>
            <td align="center">
              <a href="{{ $url }}"
                 style="display:inline-block; background:#92A89C; color:#ffffff; text-decoration:none; font-family:Arial,sans-serif; font-size:15px; font-weight:600; padding:14px 40px; border-radius:12px; letter-spacing:0.01em;">
                Verifikasi Email Saya →
              </a>
            </td>
          </tr>
        </table>

        <!-- Fallback URL -->
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="background:#FAFAF8; border:1px solid #EDE8E0; border-radius:10px; padding:16px 20px;">
              <p style="margin:0 0 8px; font-size:12px; color:#9C8B72; font-family:Arial,sans-serif; font-weight:600; text-transform:uppercase; letter-spacing:0.08em;">Tombol tidak berfungsi?</p>
              <p style="margin:0 0 8px; font-size:12px; color:#9C8B72; font-family:Arial,sans-serif; line-height:1.6;">Salin dan tempel URL berikut di browser:</p>
              <p style="margin:0; font-size:11px; color:#92A89C; font-family:'Courier New',monospace; word-break:break-all; line-height:1.5;">{{ $url }}</p>
            </td>
          </tr>
        </table>

        <p style="margin:24px 0 0; font-size:13px; color:#B0A090; font-family:Arial,sans-serif; line-height:1.6;">
          Jika Anda tidak mendaftar di TheDay, abaikan email ini — tidak ada tindakan yang diperlukan.
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
          Email ini dikirim otomatis. Mohon jangan membalas email ini langsung.<br>
          © {{ date('Y') }} TheDay. Semua hak dilindungi.
        </p>
      </td>
    </tr>

  </table>
  <!-- /Card -->

</td></tr>
</table>

</body>
</html>
