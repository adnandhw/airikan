<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #0F4C75; text-align: center;">Reset Password</h2>
        <p>Halo {{ $user->name }},</p>
        <p>Kami menerima permintaan untuk mereset kata sandi akun Anda di Air Ikan Store.</p>
        <p>Silakan klik tombol di bawah ini untuk membuat kata sandi baru:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background-color: #0F4C75; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Reset Password</a>
        </div>

        <p>Jika tombol di atas tidak berfungsi, Anda dapat menyalin dan menempel tautan berikut di browser Anda:</p>
        <p style="word-break: break-all; color: #3282B8;">{{ $url }}</p>

        <p>Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini.</p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #777; text-align: center;">&copy; {{ date('Y') }} Air Ikan Store. All rights reserved.</p>
    </div>
</body>
</html>
