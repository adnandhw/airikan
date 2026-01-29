<div class="rounded-xl border border-gray-200 shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
    @php
        $products = $getState() ?? [];
        $total = 0;
    @endphp

    {{-- 1. Desktop Layout (Table) --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm text-left" style="table-layout: fixed; width: 100%; min-width: 600px;">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-400 font-medium border-b dark:border-gray-700">
                <tr>
                    <th scope="col" style="width: 35%; padding: 16px 32px; text-align: left;">Produk</th>
                    <th scope="col" style="width: 15%; padding: 16px 8px; text-align: center;">Jumlah</th>
                    <th scope="col" style="width: 25%; padding: 16px 8px; text-align: center;">Harga</th>
                    <th scope="col" style="width: 25%; padding: 16px 32px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($products as $product)
                    @php
                        $price = $product['price'] ?? 0;
                        $quantity = $product['quantity'] ?? 0;
                        $subtotal = $price * $quantity;
                        $total += $subtotal;
                        $image = $product['image_url'] ?? null;
                        $type = $product['type'] ?? 'Item';
                    @endphp
                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td style="padding: 16px 32px; vertical-align: middle;">
                            <div class="flex items-center gap-4">
                                @if($image)
                                    <img src="{{ $image }}" alt="{{ $product['name'] }}" class="rounded-lg bg-gray-50 dark:bg-gray-800 border dark:border-gray-700" style="width: 80px; height: 80px; object-fit: contain;">
                                @else
                                    <div class="rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400" style="width: 80px; height: 80px;">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2">{{ $product['name'] ?? '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $type }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 8px; text-align: center; vertical-align: middle;">
                            <span class="text-gray-900 dark:text-white font-bold text-lg">{{ $quantity }}</span>
                        </td>
                        <td style="padding: 16px 8px; text-align: center; vertical-align: middle;">
                            @if(isset($product['discount_percentage']) && $product['discount_percentage'] > 0)
                                <div class="flex flex-col items-center">
                                    <span class="text-xs text-gray-400 line-through">Rp{{ number_format($price / (1 - ($product['discount_percentage'] / 100)), 0, ',', '.') }}</span>
                                    <span class="font-bold text-lg text-red-600 dark:text-red-500">Rp{{ number_format($price, 0, ',', '.') }}</span>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800">{{ $product['discount_percentage'] }}% OFF</span>
                                </div>
                            @else
                                <span class="font-medium text-lg text-gray-600 dark:text-gray-400">Rp{{ number_format($price, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td style="padding: 16px 32px; text-align: right; vertical-align: middle;">
                            <span class="font-bold text-lg text-gray-900 dark:text-white">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white dark:bg-gray-900"><td colspan="4" class="px-8 py-8 text-center text-gray-500 italic">Tidak ada produk</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700">
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2" style="padding: 24px 32px; text-align: right;">
                        <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Total Pembayaran</div>
                        <div class="text-3xl font-extrabold text-blue-600 dark:text-blue-500">Rp{{ number_format($total, 0, ',', '.') }}</div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- 2. Mobile Layout (List) --}}
    <div class="block sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
        @php $total = 0; @endphp
        @forelse($products as $product)
            @php
                $price = $product['price'] ?? 0;
                $quantity = $product['quantity'] ?? 0;
                $subtotal = $price * $quantity;
                $total += $subtotal;
                $type = $product['type'] ?? 'Item';
            @endphp
            <div class="p-4">
                {{-- Product Info (Compact) --}}
                <div class="font-bold text-gray-900 dark:text-white text-base leading-tight">
                    {{ $product['name'] ?? '-' }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $type }}
                </div>
                
                <div class="mt-2 flex justify-between items-center text-sm">
                    <div class="text-gray-600 dark:text-gray-400">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $quantity }}</span> x Rp{{ number_format($price, 0, ',', '.') }}
                    </div>
                    <div class="font-extrabold text-gray-900 dark:text-white">
                        Rp{{ number_format($subtotal, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500 italic">Tidak ada produk</div>
        @endforelse

        {{-- Mobile Footer --}}
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 flex justify-between items-center">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Pembayaran</div>
            <div class="text-xl font-extrabold text-blue-600 dark:text-blue-500">
                Rp{{ number_format($total, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>




