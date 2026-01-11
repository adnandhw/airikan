<?php
use App\Models\Category;
use App\Models\Product;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Backfilling Category Types from Products...\n";

$categories = Category::all();
foreach ($categories as $category) {
    // Find unique types from products in this category
    $types = Product::where('category_id', $category->id)
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->distinct()
                ->pluck('type')
                ->toArray();
    
    // Filter and sanitize
    $types = array_values(array_filter($types));
    
    if (!empty($types)) {
        // Assigning array to 'types'. 
        // The Category model's 'setTypesAttribute' mutator will convert this to a comma-separated string (e.g., "Arwana,Betta").
        $category->types = $types; 
        $category->save();
        echo " - Updated {$category->name}: " . implode(', ', $types) . "\n";
    } else {
        echo " - No types for {$category->name}\n";
    }
}
echo "Done.\n";
