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
        .btn {
            display: inline-block;
            background: #92A89C;
            color: #fff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            margin: 24px 0;
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
        <div class="logo">Theday</div>
        <h1>Undangan Partner Akun</h1>
        <p>
            <strong>{{ $ownerName }}</strong> mengundang kamu untuk mengelola undangan pernikahan bersama di Theday.
        </p>
        <p>
            Setelah menerima undangan ini, kamu bisa mengakses semua fitur (undangan, checklist, budget, dll) di akun mereka dengan login kamu sendiri.
        </p>
        <a href="{{ $acceptUrl }}" class="btn">Terima Undangan</a>
        <p class="note">
            Undangan ini berlaku selama {{ $expiresIn }}. Jika kamu tidak mengenal pengirim ini, abaikan email ini.
        </p>
        <p class="note">Salam,<br>Theday</p>
    </div>
</body>
</html>
