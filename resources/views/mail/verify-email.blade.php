<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light only">
<title>Verifikasi Email — Theday</title>
<style>
  /* Progressive enhancement — clients that support web fonts (Apple Mail, iOS)
     use Cormorant; everyone else falls back to Georgia serif (defined inline). */
  @import url('https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,400;0,500;0,600;1,400;1,500&family=JetBrains+Mono:wght@400;500&display=swap');
  body { margin: 0; padding: 0; }
  a { text-decoration: none; }
</style>
</head>
<body style="margin:0; padding:0; background-color:#E8ECE3; font-family:Arial,Helvetica,sans-serif;">

@php
    $firstName = trim(explode(' ', (string) ($user->name ?? ''))[0]) ?: 'Sahabat';
    $serif = "'Cormorant', Georgia, 'Times New Roman', serif";
    $mono  = "'JetBrains Mono', 'Courier New', monospace";
@endphp

<!-- preheader (hidden) -->
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ __('mail.verify.preheader') }}</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#E8ECE3; padding:40px 16px;">
<tr><td align="center">

  <!-- EMAIL CARD -->
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#FBFCF9; border:1px solid #D8DFD2; border-radius:18px; overflow:hidden;">

    <!-- Top bar: logo + meta -->
    <tr>
      <td style="padding:26px 32px 0;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="vertical-align:middle;">
              <span style="font-family:{{ $serif }}; font-style:italic; font-weight:500; font-size:26px; color:#1F2A2E; letter-spacing:-0.5px;">theday<span style="font-style:normal; color:#6F8270; font-weight:700;">.</span></span>
            </td>
            <td align="right" style="vertical-align:middle;">
              <span style="font-family:{{ $mono }}; font-size:10px; color:#6C7A75; letter-spacing:2px; text-transform:uppercase;">VOL. I &middot; {{ date('Y') }}</span>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- HERO -->
    <tr>
      <td style="padding:24px 32px 0;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4EDDC; background:linear-gradient(135deg,#F4EDDC,#E9DFC4); border:1px solid #E0D2BD; border-radius:22px;">
          <tr>
            <td align="center" style="padding:38px 28px 34px;">

              <!-- envelope -->
              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 18px;">
                <tr>
                  <td align="center" style="width:78px; height:78px; background-color:#FBFCF9; border:1px solid #E0D2BD; border-radius:50%; text-align:center; vertical-align:middle; font-size:34px; line-height:78px;">&#128140;</td>
                </tr>
              </table>

              <p style="margin:0 0 10px; font-family:{{ $mono }}; font-size:11px; letter-spacing:3px; color:#8E7339; text-transform:uppercase; font-weight:600;">{{ __('mail.verify.eyebrow') }}</p>
              <h1 style="margin:0 0 12px; font-family:{{ $serif }}; font-weight:500; font-size:34px; line-height:1.15; letter-spacing:-0.5px; color:#1F2A2E;">
                {{ __('mail.verify.title') }}<br><em style="font-style:italic; color:#8E7339; font-weight:400;">{{ $user->name }}.</em>
              </h1>
              <p style="margin:0 auto; max-width:360px; font-family:{{ $serif }}; font-style:italic; font-size:18px; color:#5A4B1A; line-height:1.45;">
                {{ __('mail.verify.hero_sub') }}
              </p>

            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- BODY -->
    <tr>
      <td style="padding:32px 36px 8px;">

        <p style="margin:0 0 14px; font-family:{{ $serif }}; font-style:italic; font-size:20px; color:#1F2A2E;">{{ __('mail.verify.greeting', ['name' => $firstName]) }}</p>
        <p style="margin:0 0 16px; font-size:15px; color:#3D4A4D; line-height:1.7;">
          {{ __('mail.verify.p1') }}
        </p>
        <p style="margin:0 0 16px; font-size:15px; color:#3D4A4D; line-height:1.7;">
          {{ __('mail.verify.p2') }}
        </p>

        <!-- CTA -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0 10px;">
          <tr>
            <td align="center">
              <a href="{{ $url }}" style="display:inline-block; background-color:#1F2A2E; color:#F4EDDC; padding:16px 38px; border-radius:12px; font-size:14px; font-weight:600; letter-spacing:0.5px; font-family:Arial,Helvetica,sans-serif;">&#10022; {{ __('mail.verify.cta') }}</a>
            </td>
          </tr>
        </table>
        <p style="margin:0 0 4px; text-align:center; font-family:{{ $serif }}; font-style:italic; font-size:14px; color:#6C7A75;">
          {{ __('mail.verify.expiry') }}
        </p>

        <!-- Alt link -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0 0;">
          <tr>
            <td style="background-color:#FBFCF9; border:1px solid #D8DFD2; border-radius:10px; padding:14px 16px;">
              <p style="margin:0 0 6px; font-size:11px; color:#6C7A75; font-weight:600; letter-spacing:0.5px; text-transform:uppercase;">{{ __('mail.verify.alt_label') }}</p>
              <p style="margin:0; font-family:{{ $mono }}; font-size:12px; color:#4A5A4C; word-break:break-all; line-height:1.5;">{{ $url }}</p>
            </td>
          </tr>
        </table>

        <!-- Divider -->
        <p style="text-align:center; margin:30px 0; color:#6F8270; font-family:{{ $serif }}; font-style:italic; font-size:18px;">&#10022;</p>

        <!-- WHAT'S NEXT -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#E8ECE3; border:1px solid #D8DFD2; border-radius:16px;">
          <tr>
            <td style="padding:24px 24px 20px;">
              <h3 style="margin:0 0 4px; font-family:{{ $serif }}; font-size:22px; font-weight:500; color:#1F2A2E; letter-spacing:-0.3px;">{{ __('mail.verify.next_title') }}</h3>
              <p style="margin:0 0 18px; font-family:{{ $serif }}; font-style:italic; font-size:14px; color:#6C7A75;">{{ __('mail.verify.next_sub') }}</p>

              @foreach ([
                  [__('mail.verify.step1_t'), __('mail.verify.step1_d')],
                  [__('mail.verify.step2_t'), __('mail.verify.step2_d')],
                  [__('mail.verify.step3_t'), __('mail.verify.step3_d')],
              ] as $i => $step)
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:{{ $loop->last ? '0' : '14px' }};">
                  <tr>
                    <td style="width:32px; vertical-align:top;">
                      <table role="presentation" cellpadding="0" cellspacing="0">
                        <tr>
                          <td align="center" style="width:32px; height:32px; background-color:#FBFCF9; border:1px solid #9CAB8E; border-radius:50%; font-family:{{ $serif }}; font-size:16px; color:#4A5A4C; line-height:32px;">{{ $i + 1 }}</td>
                        </tr>
                      </table>
                    </td>
                    <td style="padding-left:14px; vertical-align:top;">
                      <div style="font-size:14px; color:#1F2A2E; font-weight:600;">{{ $step[0] }}</div>
                      <div style="font-size:12.5px; color:#3D4A4D; margin-top:2px; line-height:1.5;">{{ $step[1] }}</div>
                    </td>
                  </tr>
                </table>
              @endforeach
            </td>
          </tr>
        </table>

        <!-- Security note -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0 0;">
          <tr>
            <td style="background-color:#F7F1E2; border-left:3px solid #C9A45B; border-radius:0 10px 10px 0; padding:14px 18px;">
              <p style="margin:0; font-size:12.5px; color:#5A4B1A; line-height:1.6;">
                <strong style="color:#1F2A2E;">{{ __('mail.verify.security_q') }}</strong> {{ __('mail.verify.security_r') }}
              </p>
            </td>
          </tr>
        </table>

        <!-- Signature -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0 0;">
          <tr>
            <td style="border-top:1px solid #D8DFD2; padding-top:22px;">
              <p style="margin:0 0 8px; font-family:{{ $serif }}; font-style:italic; font-size:17px; color:#3D4A4D;">{{ __('mail.verify.sign_warm') }}</p>
              <div style="font-family:{{ $serif }}; font-size:24px; font-weight:500; color:#1F2A2E; letter-spacing:-0.3px;">{{ __('mail.verify.sign_team') }} <em style="font-style:italic; color:#6F8270; font-weight:400;">Theday</em></div>
              <div style="font-family:{{ $serif }}; font-style:italic; font-size:14px; color:#6C7A75; margin-top:4px;">{{ __('mail.verify.sign_role') }}</div>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    <!-- FOOTER -->
    <tr>
      <td style="background-color:#1F2A2E; padding:30px 36px 26px; margin-top:24px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="padding-bottom:18px; border-bottom:1px solid rgba(244,237,220,0.12);">
              <div style="font-family:{{ $serif }}; font-style:italic; font-size:24px; color:#F4EDDC; font-weight:500; letter-spacing:-0.5px;">theday<span style="font-style:normal; color:#C7D3BC; font-weight:700;">.</span></div>
              <div style="font-family:{{ $serif }}; font-style:italic; font-size:14px; color:rgba(244,237,220,0.55); margin-top:4px;">{{ __('mail.verify.footer_tag') }}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 0 0;">
              <p style="margin:0 0 6px; font-size:11px; color:rgba(244,237,220,0.5); line-height:1.6;">
                {{ __('mail.verify.footer_auto') }}
              </p>
              <p style="margin:0; font-size:11px; color:rgba(244,237,220,0.4); line-height:1.6;">
                {{ __('mail.verify.footer_copy', ['year' => date('Y')]) }}
                <a href="{{ route('legal.privacy') }}" style="color:rgba(244,237,220,0.6); text-decoration:underline;">{{ __('mail.verify.footer_priv') }}</a>
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

  </table>
  <!-- /CARD -->

</td></tr>
</table>

</body>
</html>
