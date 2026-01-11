<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductReseller;

class SyncProductsToResellers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-resellers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing Products to ProductReseller collection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync...');

        $products = Product::all();
        $count = 0;

        foreach ($products as $product) {
            // Check if exists based on product_id
            $exists = ProductReseller::where('product_id', $product->id)->exists();

            if (!$exists) {
                ProductReseller::create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'type' => $product->type,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image,
                    'size' => $product->size,
                    'category_id' => $product->category_id,
                    'is_active' => true,
                    'tier_pricing' => []
                ]);
                $count++;
                $this->info("Synced: {$product->name}");
            }
        }

        $this->info("Sync complete. {$count} products created.");
    }
}
