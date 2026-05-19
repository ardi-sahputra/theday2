<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Undangan Kedaluwarsa — TheDay</title>
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
            max-width: 480px;
            margin: 60px auto;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.07);
            text-align: center;
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
            margin: 0 0 16px;
        }
        p {
            color: #6B5B3E;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 12px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">TheDay</div>
        <h1>Undangan Kedaluwarsa</h1>
        <p>Undangan ke <strong>{{ $email }}</strong> sudah lewat masa berlaku (7 hari).</p>
        <p>Minta owner akun untuk kirim ulang undangan dari halaman pengaturan mereka.</p>
    </div>
</body>
</html>
