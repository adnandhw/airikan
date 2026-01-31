<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Transaksi #{{ $transaction->short_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            max-width: 80mm; /* Struk style width */
            margin: 0 auto;
            padding: 10px;
        }
        h3 {
            margin: 5px 0;
            text-align: center;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
        }
        .header p {
            margin: 2px 0;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 12px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 4px 0;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px dashed #333;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        @media print {
            body {
                width: 100%;
                max-width: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h3>Air Ikan Store</h3>
        <p>ID Transaksi: #{{ $transaction->short_id }}</p>
        <p>{{ $transaction->created_at->translatedFormat('d F Y H.i') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Info Pengiriman</div>
        <p style="margin: 0;">
            <strong>{{ $transaction->buyer_info['name'] ?? '-' }}</strong><br>
            {{ $transaction->buyer_info['phone'] ?? '-' }}<br>
            {{ $transaction->buyer_info['address'] ?? '-' }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Produk</div>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-right">Jml</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $totalWeight = 0;
                @endphp
                @foreach($transaction->products as $item)
                @php
                    $lineTotal = $item['price'] * $item['quantity'];
                    $subtotal += $lineTotal;
                    $itemWeight = $item['weight'] ?? 0;
                    $lineWeight = $itemWeight * $item['quantity'];
                    $totalWeight += $lineWeight;
                @endphp
                <tr>
                    <td>
                        {{ $item['name'] }}
                        @if(isset($item['variant']))
                            <br><small class="text-gray-500">({{ $item['variant'] }})</small>
                        @endif
                        @if($itemWeight > 0)
                            <br><small style="color: #666;">Berat: {{ $itemWeight }} g @if(isset($item['is_reseller']) && $item['is_reseller']) / {{ $item['quantity'] * 10 }} pcs @endif</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $item['quantity'] }}</td>
                    <td class="text-right">{{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($lineTotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                
                <tr style="border-top: 1px solid #ddd;">
                    <td colspan="3" class="text-right" style="padding-top: 10px;">Subtotal Produk</td>
                    <td class="text-right" style="padding-top: 10px;">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Biaya Pengiriman ({{ $transaction->courier_name ?? '-' }})</td>
                    <td class="text-right">{{ number_format($transaction->shipping_cost ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total Pembayaran</td>
                    <td class="text-right">Rp{{ number_format($subtotal + ($transaction->shipping_cost ?? 0), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: center; font-size: 11px; padding-top: 10px; color: #666;">
                        Berat Total: {{ $totalWeight < 1000 ? $totalWeight . ' g' : ($totalWeight / 1000) . ' kg' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja!</p>
    </div>

</body>
</html>
