<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Email - Air Ikan Store</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #F8B600;">Verifikasi Email Anda</h2>
        <p>Halo {{ $user->name }},</p>
        <p>Terima kasih telah mendaftar di Air Ikan Store. Silakan klik tombol di bawah ini untuk memverifikasi email Anda dan mengaktifkan akun Anda.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background-color: #F8B600; color: #000; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;">Verifikasi Email</a>
        </div>
        
        <p>Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut di browser Anda:</p>
        <p><a href="{{ $url }}">{{ $url }}</a></p>
        
        <p>Terima kasih,<br>Tim Air Ikan Store</p>
    </div>
</body>
</html>
