<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #4f46e5; padding: 28px 32px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 22px; margin: 0; }
        .body { padding: 32px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 16px; }
        .otp-box { background: #f0f0ff; border: 2px dashed #4f46e5; border-radius: 8px; text-align: center; padding: 20px; margin: 24px 0; }
        .otp-code { font-size: 40px; font-weight: bold; letter-spacing: 10px; color: #4f46e5; }
        .note { font-size: 14px; color: #666; line-height: 1.6; }
        .note strong { color: #e53e3e; }
        .footer { background: #f9f9f9; text-align: center; padding: 16px 32px; font-size: 12px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="body">
            <p class="greeting">Halo, <strong>{{ $user->name }}</strong>!</p>

            @if ($otp->purpose === 'password_reset')
                <p class="note">Kami menerima permintaan reset password untuk akun Anda. Gunakan kode OTP berikut:</p>
            @else
                <p class="note">Terima kasih telah mendaftar. Gunakan kode OTP berikut untuk memverifikasi akun Anda:</p>
            @endif

            <div class="otp-box">
                <div class="otp-code">{{ $otp->code }}</div>
            </div>

            <p class="note">
                Kode ini berlaku selama <strong>10 menit</strong> sejak email ini dikirim.<br>
                <strong>Jangan bagikan kode ini kepada siapapun.</strong>
            </p>
            <p class="note">
                @if ($otp->purpose === 'password_reset')
                    Jika Anda tidak merasa meminta reset password, abaikan email ini. Akun Anda tetap aman.
                @else
                    Jika Anda tidak merasa mendaftar di {{ config('app.name') }}, abaikan email ini.
                @endif
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
