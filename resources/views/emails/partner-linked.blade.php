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
        .note {
            font-size: 13px;
            color: #A0917D;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #E8DFD0;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">TheDay</div>
        <h1>Partner Terhubung</h1>
        <p>
            <strong>{{ $partnerName }}</strong> sudah menerima undangan dan sekarang punya akses penuh ke akun TheDay kamu.
        </p>
        <p>
            Mereka bisa mengakses undangan, checklist, budget, dan billing bersama kamu.
        </p>
        <p class="note">Salam,<br>TheDay</p>
    </div>
</body>
</html>
