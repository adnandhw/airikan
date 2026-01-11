<!DOCTYPE html>
<html>
<head>
    <title>Bukti Pembayaran Baru</title>
</head>
<body>
    <h2>Halo Admin,</h2>
    <p>Ada bukti pembayaran baru yang diupload oleh user.</p>
    
    <p><strong>ID Transaksi:</strong> #{{ strtoupper(substr($transaction->id, 0, 8)) }}</p>
    <p><strong>Nama Pembeli:</strong> {{ $transaction->buyer_info['name'] ?? 'N/A' }}</p>
    <p><strong>Total:</strong> Rp{{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
    
    <p>Silakan cek di Admin Panel untuk verifikasi:</p>
    <p><a href="{{ url('/admin/transactions/' . $transaction->id . '/edit') }}">Lihat Transaksi</a></p>

    <p>Terima kasih.</p>
</body>
</html>
