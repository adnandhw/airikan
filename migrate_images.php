<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

$publicUploadsDir = public_path('uploads/payment_proofs');
$storageDir = storage_path('app/public/payment_proofs');

if (!File::exists($storageDir)) {
    File::makeDirectory($storageDir, 0755, true);
    echo "Created storage directory: $storageDir\n";
}

$transactions = DB::table('transactions')->whereNotNull('payment_proof')->get();

foreach ($transactions as $t) {
    $currentPath = $t->payment_proof; // e.g., /uploads/payment_proofs/123.jpg
    
    // Check if it's already in new format (doesn't start with /uploads)
    if (!str_starts_with($currentPath, '/uploads/')) {
        echo "Skipping already migrated or new format: $currentPath\n";
        continue;
    }

    $filename = basename($currentPath);
    $sourceFile = $publicUploadsDir . '/' . $filename;
    $targetFile = $storageDir . '/' . $filename;
    $newDbPath = 'payment_proofs/' . $filename;

    if (File::exists($sourceFile)) {
        File::copy($sourceFile, $targetFile); // Copy instead of move to be safe
        echo "Moved: $filename\n";
        
        DB::table('transactions')
            ->where('id', $t->id)
            ->update(['payment_proof' => $newDbPath]);
            
        echo "Updated DB for ID {$t->id} to $newDbPath\n";
    } else {
        echo "Source file not found for ID {$t->id}: $sourceFile\n";
        // Attempt to see if it's already in storage?
        if (File::exists($targetFile)) {
             echo "File exists in target, updating DB only.\n";
             DB::table('transactions')
                ->where('id', $t->id)
                ->update(['payment_proof' => $newDbPath]);
        }
    }
}

echo "Migration complete.\n";
