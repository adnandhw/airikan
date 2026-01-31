<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\ProductReseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TransactionController extends Controller
{
    // Create new transaction
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'buyer_id' => 'required',
            'buyer_info' => 'required',
            'products' => 'required|array',
            'shipping_cost' => 'nullable|numeric',
            'courier_name' => 'nullable|string',
            'total_weight' => 'nullable|numeric',
            'distance' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // --- SECURITY: RECALCULATE TOTAL & SANITIZE PRODUCTS ---
        $calculatedTotal = 0;
        $sanitizedProducts = [];
        $productListText = "";

        foreach ($request->products as $item) {
            $qty = (int) $item['quantity'];
            if ($qty <= 0) continue;

            $productId = $item['product_id'];
            $effectivePrice = 0;
            $productName = "Unknown Product";
            
            // Explicitly reset variables for each iteration
            $mainProduct = null;
            $resellerProduct = null;

            // 1. Determine type based on request flag
            if (isset($item['is_reseller']) && $item['is_reseller']) {
                // Search ONLY in Reseller Products
                $resellerProduct = ProductReseller::find($productId);

                if ($resellerProduct) {
                    $productName = $resellerProduct->name;
                    // Check Tier Pricing
                    $price = $resellerProduct->price;
                    if (!empty($resellerProduct->tier_pricing) && is_array($resellerProduct->tier_pricing)) {
                        // Sort tiers by quantity descending
                        $tiers = collect($resellerProduct->tier_pricing)->sortByDesc('quantity');
                        $matchedTier = $tiers->first(function ($tier) use ($qty) {
                            return $qty >= $tier['quantity'];
                        });

                        if ($matchedTier) {
                            if (isset($matchedTier['unit_price'])) {
                                $price = $matchedTier['unit_price'];
                            } elseif (isset($matchedTier['discount_percentage'])) {
                                $price = $resellerProduct->price * (1 - ($matchedTier['discount_percentage'] / 100));
                            }
                        }
                    }
                    $effectivePrice = $price;
                } else {
                     return response()->json([
                        'success' => false,
                        'message' => 'Produk Reseller tidak ditemukan ID: ' . $productId
                    ], 400);
                }
            } else {
                // Search ONLY in Regular Products
                $mainProduct = Product::find($productId);
                
                if ($mainProduct) {
                    $effectivePrice = $mainProduct->price;
                    $productName = $mainProduct->name;
                } else {
                     return response()->json([
                        'success' => false,
                        'message' => 'Produk tidak ditemukan ID: ' . $productId
                    ], 400);
                }
            }

            $currentSubtotal = $effectivePrice * $qty;
            $calculatedTotal += $currentSubtotal;

            // Reconstruct item with server-side price
            $sanitizedItem = [
                'product_id' => $productId,
                'name' => $productName,
                'image_url' => $item['image_url'] ?? null,
                'type' => $item['type'] ?? 'product',
                'price' => $effectivePrice,
                'quantity' => $qty,
                'is_reseller' => $resellerProduct ? true : false
            ];
            $sanitizedProducts[] = $sanitizedItem;

            $formattedPrice = number_format($effectivePrice, 0, ',', '.');
            $productListText .= "- {$productName} (x{$qty}) - Rp{$formattedPrice}\n";
        }

        $shippingCost = (float) ($request->shipping_cost ?? 0);
        $totalPayment = $calculatedTotal + $shippingCost;

        $transaction = Transaction::create([
            'buyer_id' => $request->buyer_id,
            'buyer_info' => $request->buyer_info,
            'products' => $sanitizedProducts,
            'total_amount' => $calculatedTotal, // Trust Server Calculation
            'shipping_cost' => $shippingCost,
            'courier_name' => $request->courier_name,
            'total_weight' => $request->total_weight ?? 0,
            'distance' => $request->distance ?? 0,
            'total_payment' => $totalPayment,
            'status' => 'pending',
            'payment_proof' => null
        ]);

        // Generate and save short_id for searching
        $transaction->update([
            'short_id' => strtoupper(substr($transaction->id, 0, 8))
        ]);

        // Send Email Notification to Admin
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Pesanan Baru Masuk!\n\n" .
                "ID Transaksi: " . strtoupper(substr($transaction->id, 0, 8)) . "\n" .
                "Pembeli: " . ($request->buyer_info['name'] ?? '-') . "\n" .
                "No. HP: " . ($request->buyer_info['phone'] ?? '-') . "\n" .
                "Alamat: " . ($request->buyer_info['address'] ?? '-') . "\n\n" .
                "Detail Pesanan:\n" .
                $productListText . "\n" .
                "Subtotal: Rp" . number_format($calculatedTotal, 0, ',', '.') . "\n" .
                "Ongkos Kirim (" . ($request->courier_name ?? 'Kurir') . "): Rp" . number_format($shippingCost, 0, ',', '.') . "\n" .
                "Total Pembayaran (Server Validated): Rp" . number_format($totalPayment, 0, ',', '.') . "\n\n" .
                "Silakan cek Admin Panel untuk detailnya.",
                function ($message) {
                    $message->to(['adnandhw@gmail.com', 'black.busted99@gmail.com'])
                            ->subject('Notifikasi Pesanan Baru - Air Ikan Store');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Email notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction
        ], 201);
    }

    // Get transactions by buyer_id
    public function index($userId)
    {
        $transactions = Transaction::where('buyer_id', $userId)
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    // Upload payment proof
    public function uploadProof(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Use Laravel Storage (public disk)
            // This stores in storage/app/public/payment_proofs
            $path = $image->store('payment_proofs', 'public');

            \Illuminate\Support\Facades\Log::info("Uploading proof for ID: " . $id);
            $transaction = Transaction::find($id);
            
            if (!$transaction) {
                \Illuminate\Support\Facades\Log::error("Transaction not found for ID: " . $id);
                return response()->json(['success' => false, 'message' => 'Transaction not found for ID: ' . $id], 404);
            }

            // Save the path compatible with Filament ImageColumn (relative path)
            $transaction->payment_proof = $path;
            $transaction->status = 'paid'; // Mark as 'paid' (Waiting Confirmation)
            $transaction->save();

            // Send Email to Admin
            try {
                \Illuminate\Support\Facades\Mail::to(['adnandhw@gmail.com', 'black.busted99@gmail.com'])->send(new \App\Mail\PaymentProofUploaded($transaction));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send payment proof email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully',
                'data' => $transaction
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No image uploaded'], 400);
    }
}
