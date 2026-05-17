<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #f9f5ee;
            margin: 0;
            padding: 32px 16px;
            color: #2C2417;
        }

        .card {
            background: #fff;
            max-width: 520px;
            margin: 0 auto;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.07);
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: #92A89C;
            letter-spacing: -0.5px;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #2C2417;
            margin: 0 0 8px;
        }

        p {
            color: #6B5B3E;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 12px;
        }

        .badge {
            display: inline-block;
            background: rgba(146, 168, 156, 0.15);
            color: #92A89C;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
            margin-bottom: 24px;
        }

        .detail-box {
            background: #FAFAF8;
            border: 1px solid #E8DFD0;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }

        .detail-row .label {
            color: #9C8B72;
        }

        .detail-row .value {
            font-weight: 600;
            color: #2C2417;
        }

        .btn {
            display: block;
            text-align: center;
            background: #92A89C;
            color: #fff;
            text-decoration: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            margin: 24px 0 0;
        }

        .footer {
            text-align: center;
            color: #73877C;
            font-size: 12px;
            margin-top: 32px;
        }
    </style>
</head>

<body>
    <div class="card">
        <table cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
            <tr>
                <td style="width:32px; height:32px; background:#73877C; border-radius:8px; text-align:center; vertical-align:middle;">
                    <span style="color:#FFFCF7; font-family:Georgia,serif; font-size:18px; font-weight:600; line-height:32px; letter-spacing:-1px;">d<span style="color:#C8A26B;">.</span></span>
                </td>
                <td style="padding-left:10px; vertical-align:middle;">
                    <span style="font-size:18px; font-weight:700; color:#2C2417; letter-spacing:-0.3px; font-family:Georgia,serif;">TheDay</span>
                </td>
            </tr>
        </table>
        <span class="badge">Pembayaran Berhasil</span>
        <h1>Selamat, {{ $userName }}!</h1>
        <p>Paket <strong>{{ $planName }}</strong>mu sudah aktif. Nikmati semua fitur premium untuk membuat undangan
            pernikahan yang sempurna.</p>

        <div class="detail-box">
            <div class="detail-row">
                <span class="label">No. Invoice</span>
                <span class="value">{{ $invoiceNumber }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Paket</span>
                <span class="value">{{ $planName }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total Bayar</span>
                <span class="value">{{ $amount }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Tanggal Bayar</span>
                <span class="value">{{ $paidAt }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Aktif Hingga</span>
                <span class="value">{{ $expiresAt }}</span>
            </div>
        </div>

        <a href="{{ $invoiceUrl }}" class="btn">Lihat & Unduh Invoice →</a>

        <div class="footer">
            <p style="margin:0">Pertanyaan? Hubungi kami di <strong>hello@theday.id</strong></p>
            <p style="margin:8px 0 0">TheDay — Undangan Pernikahan Digital</p>
        </div>
    </div>
</body>

</html>
