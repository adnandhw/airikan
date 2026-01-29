<div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700 dark:bg-gray-900 bg-white">
    {{-- Header (Desktop Only) --}}
    <div class="hidden md:grid grid-cols-12 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-400 font-medium border-b dark:border-gray-700">
        <div class="col-span-5 px-8 py-4">Produk</div>
        <div class="col-span-2 px-2 py-4 text-center">Jumlah</div>
        <div class="col-span-2 px-2 py-4 text-center">Harga</div>
        <div class="col-span-3 px-8 py-4 text-right">Total</div>
    </div>

    {{-- Body --}}
    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @php
            $products = $getState() ?? [];
            $total = 0;
        @endphp
        @forelse($products as $product)
            @php
                $price = $product['price'] ?? 0;
                $quantity = $product['quantity'] ?? 0;
                $subtotal = $price * $quantity;
                $total += $subtotal;
                $image = $product['image_url'] ?? null;
                $type = $product['type'] ?? 'Item';
            @endphp
            <div class="md:grid md:grid-cols-12 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                {{-- Product Info --}}
                <div class="col-span-12 md:col-span-5 px-4 py-4 sm:px-8 flex items-center gap-4">
                    @if($image)
                        <div class="flex-shrink-0" style="width: 80px; height: 80px;">
                            <img src="{{ $image }}" alt="{{ $product['name'] }}" class="rounded-lg bg-gray-50 dark:bg-gray-800 border dark:border-gray-700 object-contain" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px;">
                        </div>
                    @else
                        <div class="rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 flex-shrink-0" style="width: 80px; height: 80px;">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="text-base sm:text-lg font-bold text-gray-900 dark:text-white line-clamp-2">
                            {{ $product['name'] ?? '-' }}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $type }}
                        </div>
                        
                        {{-- Mobile labels (Hidden on tablet/desktop) --}}
                        <div class="md:hidden mt-3 flex flex-wrap items-center gap-3">
                            <div class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300">
                                Qty: {{ $quantity }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Rp{{ number_format($price, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile Subtotal Divider (Hidden on tablet/desktop) --}}
                <div class="md:hidden px-4 pb-4 flex justify-between items-center border-t border-gray-50 dark:border-gray-800 pt-3">
                    <span class="text-xs uppercase tracking-wider text-gray-500 font-medium">Subtotal</span>
                    <span class="text-lg font-extrabold text-gray-900 dark:text-white">
                        Rp{{ number_format($subtotal, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Desktop Quantity --}}
                <div class="hidden md:flex col-span-2 px-2 py-4 items-center justify-center">
                    <span class="text-gray-900 dark:text-white font-bold text-lg">
                        {{ $quantity }}
                    </span>
                </div>

                {{-- Desktop Price --}}
                <div class="hidden md:flex col-span-2 px-2 py-4 items-center justify-center">
                    @if(isset($product['discount_percentage']) && $product['discount_percentage'] > 0)
                        <div class="flex flex-col items-center">
                            <span class="text-xs text-gray-400 line-through">
                                Rp{{ number_format($price / (1 - ($product['discount_percentage'] / 100)), 0, ',', '.') }}
                            </span>
                            <span class="font-bold text-lg text-red-600 dark:text-red-500 whitespace-nowrap">
                                Rp{{ number_format($price, 0, ',', '.') }}
                            </span>
                            <div class="mt-1 flex flex-col items-center gap-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ $product['discount_percentage'] }}% OFF
                                </span>
                            </div>
                        </div>
                    @else
                        <span class="font-medium text-lg text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            Rp{{ number_format($price, 0, ',', '.') }}
                        </span>
                    @endif
                </div>

                {{-- Desktop Total --}}
                <div class="hidden md:flex col-span-3 px-8 py-4 items-center justify-end">
                    <span class="font-bold text-lg text-gray-900 dark:text-white whitespace-nowrap">
                        Rp{{ number_format($subtotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-900 px-8 py-12 text-center text-gray-500 dark:text-gray-400 italic font-medium">
                Tidak ada produk
            </div>
        @endforelse
    </div>

    {{-- Footer/Total --}}
    <div class="bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 px-6 py-8 sm:px-8 flex justify-end">
        <div class="flex flex-col items-end gap-1 sm:gap-2">
            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Pembayaran</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-blue-600 dark:text-blue-500">
                Rp{{ number_format($total, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>



