<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0d6efd; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background-color: #f8f9fa; padding: 30px; border: 1px solid #dee2e6; border-radius: 0 0 10px 10px; }
        .order-info { margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: white; border-radius: 8px; overflow: hidden; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .table th { background-color: #f1f1f1; font-weight: bold; }
        .total-section { text-align: right; }
        .footer { text-align: center; margin-top: 30px; font-size: 0.8em; color: #6c757d; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .bg-primary { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($isAdmin)
                <h1>Pesanan Baru Masuk!</h1>
            @else
                <h1>Terima Kasih Atas Pesanan Anda!</h1>
            @endif
        </div>
        
        <div class="content">
            <div class="order-info">
                <h3>Informasi Pesanan</h3>
                <p><strong>Order ID:</strong> #{{ strtoupper(substr($transaction->id, 0, 8)) }}</p>
                <p><strong>Tanggal:</strong> {{ $transaction->created_at->format('d M Y, H:i') }}</p>
                <p><strong>Status:</strong> <span class="badge bg-primary">Menunggu Pembayaran</span></p>
                
                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                
                <h3>Informasi Pengiriman</h3>
                <p><strong>Nama:</strong> {{ $transaction->buyer_info['name'] ?? '-' }}</p>
                <p><strong>Telepon:</strong> {{ $transaction->buyer_info['phone'] ?? '-' }}</p>
                <p><strong>Alamat:</strong> {{ $transaction->buyer_info['address'] ?? '-' }}</p>
                @if(isset($transaction->courier_name))
                    <p><strong>Kurir:</strong> {{ $transaction->courier_name }}</p>
                @endif
            </div>

            <h3>Rincian Produk</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th style="text-align: center;">Jumlah</th>
                        <th style="text-align: right;">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->products as $product)
                        <tr>
                            <td>
                                {{ str_replace('[RESELLER]', '', $product['name']) }}
                                <br>
                                <small style="color: #6c757d;">{{ $product['type'] ?? '' }}</small>
                            </td>
                            <td style="text-align: center;">{{ $product['quantity'] }}</td>
                            <td style="text-align: right;">Rp{{ number_format($product['price'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <p><strong>Subtotal:</strong> Rp{{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                <p><strong>Biaya Pengiriman:</strong> Rp{{ number_format($transaction->shipping_cost, 0, ',', '.') }}</p>
                <h2 style="color: #0d6efd;">Total: Rp{{ number_format($transaction->total_payment, 0, ',', '.') }}</h2>
            </div>
            
            @if(!$isAdmin)
                <div style="background: #e7f1ff; padding: 20px; border-radius: 8px; margin-top: 30px; border: 1px solid #b6d4fe;">
                    <p style="margin-top: 0;"><strong>Langkah Selanjutnya:</strong></p>
                    <ol style="margin-bottom: 0;">
                        <li>Lakukan pembayaran ke nomor rekening Admin.</li>
                        <li>Klik <strong>"Hubungi Admin"</strong> di halaman profil atau balas pesan WhatsApp otomatis untuk konfirmasi.</li>
                        <li>Pesanan akan diproses segera setelah pembayaran diverifikasi.</li>
                    </ol>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Air Ikan Store. All rights reserved.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
