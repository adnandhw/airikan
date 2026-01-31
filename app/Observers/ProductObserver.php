<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Carbon;

class ProductObserver
{
    /**
     * Handle the Product "saving" event.
     */
    public function saving(Product $product): void
    {
        // If discount_duration is set/changed, recalculate dates
        // If discount removed (set to 0), clear dates
        if ($product->discount_percentage == 0) {
             $product->discount_duration = null;
             $product->discount_start_date = null;
             $product->discount_end_date = null;
        } 
        // If discount is active and parameters changed, recalculate dates
        elseif ($product->discount_percentage > 0 && 
               ($product->isDirty('discount_duration') || $product->isDirty('discount_percentage') || !$product->discount_end_date)) {
            
            // Ensure duration is valid
            $duration = (int) ($product->discount_duration ?? 1); // Default to 1 day if missing
            $product->discount_duration = $duration;
            
            $product->discount_start_date = Carbon::now();
            $product->discount_end_date = Carbon::now()->addDays($duration + 1)->startOfDay();
        }
    }


    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        \App\Models\ProductReseller::create([
            'product_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'type' => $product->type,
            'price' => $product->price,
            'stock' => $product->stock,
            'image' => $product->image,
            'size' => $product->size,
            'weight' => $product->weight,
            'category_id' => $product->category_id,
            'is_active' => false, // Default to hidden so admin must approve/enable it first
            'tier_pricing' => [] // Initialize empty, manageable by Reseller Admin
        ]);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Don't sync if only stock changed because TransactionController handles stock separately?
        // Actually, if Admin updates Product details, we WANT to sync.
        // But if Transaction updates Stock, it triggers 'updated'.
        // Syncing blindly is fine because if Stock matches, no harm done.

        $resellerProduct = \App\Models\ProductReseller::where('product_id', $product->id)->first();

        if ($resellerProduct) {
            $resellerProduct->update([
                'name' => $product->name,
                'description' => $product->description,
                'type' => $product->type,
                'price' => $product->price,
                'stock' => $product->stock,
                'image' => $product->image,
                'size' => $product->size,
                'weight' => $product->weight,
                'category_id' => $product->category_id,
                // Do NOT update tier_pricing, as that is Reseller specific
            ]);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        \App\Models\ProductReseller::where('product_id', $product->id)->delete();
    }
}
