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
            'total_amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $transaction = Transaction::create([
            'buyer_id' => $request->buyer_id,
            'buyer_info' => $request->buyer_info,
            'products' => $request->products,
            'total_amount' => $request->total_amount,
            'status' => 'pending',
            'payment_proof' => null
        ]);

        // Generate and save short_id for searching
        $transaction->update([
            'short_id' => strtoupper(substr($transaction->id, 0, 8))
        ]);

        // --- STOCK SYNCHRONIZATION LOGIC (MOVED TO ADMIN APPROVAL) ---
        // logic moved to Filament/Resources/Transactions/Pages/EditTransaction.php
        /*
        foreach ($request->products as $item) {
            $qty = (int) $item['quantity'];
            $productId = $item['product_id'];

            // 1. Try to find in Regular Products
            $mainProduct = Product::find($productId);
            
            if ($mainProduct) {
                // It is a Main Product
                // Decrement its own stock
                if ($mainProduct->stock >= $qty) {
                    $mainProduct->decrement('stock', $qty);
                }

                // Decrement all linked Reseller Products
                ProductReseller::where('product_id', $mainProduct->id)->decrement('stock', $qty);

            } else {
                // 2. Try to find in Reseller Products
                $resellerProduct = ProductReseller::find($productId);

                if ($resellerProduct) {
                    // It is a Reseller Product
                    // Decrement its own stock
                    if ($resellerProduct->stock >= $qty) {
                        $resellerProduct->decrement('stock', $qty);
                    }

                    // Check if it has a parent Product
                    if (!empty($resellerProduct->product_id)) {
                        $parentProduct = Product::find($resellerProduct->product_id);
                        if ($parentProduct) {
                            // Decrement Parent Product
                            if ($parentProduct->stock >= $qty) {
                                $parentProduct->decrement('stock', $qty);
                            }

                            // Decrement OTHER linked Reseller Products (siblings)
                            ProductReseller::where('product_id', $parentProduct->id)
                                ->where('id', '!=', $resellerProduct->id)
                                ->decrement('stock', $qty);
                        }
                    }
                }
            }
        }
        */
        // -----------------------------------

        // Format Product List for Email
        $productList = "";
        foreach ($request->products as $product) {
            $price = number_format($product['price'], 0, ',', '.');
            $subtotal = number_format($product['price'] * $product['quantity'], 0, ',', '.');
            $productList .= "- {$product['name']} (x{$product['quantity']}) - Rp{$price}\n";
        }

        // Send Email Notification to Admin
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Pesanan Baru Masuk!\n\n" .
                "ID Transaksi: " . strtoupper(substr($transaction->id, 0, 8)) . "\n" .
                "Pembeli: " . ($request->buyer_info['name'] ?? '-') . "\n" .
                "No. HP: " . ($request->buyer_info['phone'] ?? '-') . "\n" .
                "Alamat: " . ($request->buyer_info['address'] ?? '-') . "\n\n" .
                "Detail Pesanan:\n" .
                $productList . "\n" .
                "Total Pembayaran: Rp" . number_format($request->total_amount, 0, ',', '.') . " (Belum termasuk Ongkir)\n\n" .
                "Silakan cek Admin Panel untuk detailnya.",
                function ($message) {
                    $message->to('adnandhw@gmail.com')
                            ->subject('Notifikasi Pesanan Baru - Air Ikan Store');
                }
            );
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
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
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/payment_proofs'), $imageName);

            \Illuminate\Support\Facades\Log::info("Uploading proof for ID: " . $id);
            $transaction = Transaction::find($id);
            
            if (!$transaction) {
                \Illuminate\Support\Facades\Log::error("Transaction not found for ID: " . $id);
                return response()->json(['success' => false, 'message' => 'Transaction not found for ID: ' . $id], 404);
            }

            $transaction->payment_proof = '/uploads/payment_proofs/' . $imageName;
            $transaction->status = 'paid'; // Mark as 'paid' (Waiting Confirmation)
            $transaction->save();

            // Send Email to Admin
            try {
                \Illuminate\Support\Facades\Mail::to('adnandhw@gmail.com')->send(new \App\Mail\PaymentProofUploaded($transaction));
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
