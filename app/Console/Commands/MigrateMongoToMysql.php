<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MongoDB\Client as MongoClient;

class MigrateMongoToMysql extends Command
{
    protected $signature = 'migrate:mongo-to-mysql';
    protected $description = 'Migrate data from default MongoDB connection to configured MySQL connection';

    public function handle()
    {
        $this->info("Starting migration from MongoDB to MySQL...");

        // Connect to MongoDB
        $mongoHost = '127.0.0.1';
        $mongoPort = 27017; // Force Mongo port, ignore env DB_PORT (which is MySQL)
        $mongoDb = 'air_ikan';
        
        $this->info("Connecting to MongoDB at $mongoHost:$mongoPort / $mongoDb");
        
        try {
            // Using raw MongoDB client to avoid Eloquent model conflicts (since we switched them to MySQL)
            $client = new MongoClient("mongodb://$mongoHost:$mongoPort");
            $db = $client->selectDatabase($mongoDb);
        } catch (\Exception $e) {
            $this->error("Failed to connect to MongoDB: " . $e->getMessage());
            return;
        }

        // --- 1. Migrate Users ---
        $this->migrateCollection($db, 'users', 'users', function ($doc) {
            return [
                'id' => (string) $doc['_id'],
                'name' => $doc['name'],
                'email' => $doc['email'],
                'password' => (trim($doc['email']) == 'airikan@gmail.com') ? \Illuminate\Support\Facades\Hash::make('airikanstore') : $doc['password'],
                'role' => $doc['role'] ?? 'user',
                'avatar' => $doc['avatar'] ?? null,
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });

        // --- 2. Migrate Categories ---
        $this->migrateCollection($db, 'categories', 'categories', function ($doc) {
            return [
                'id' => (string) $doc['_id'],
                'name' => $doc['name'] ?? '',
                'slug' => $doc['slug'] ?? '',
                'image' => $doc['image'] ?? null,
                'types' => isset($doc['types']) ? json_encode($doc['types']) : null, 
                'is_active' => true,
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });

        // --- 3. Migrate Products ---
        $this->migrateCollection($db, 'products', 'products', function ($doc) {
            return [
                'id' => (string) $doc['_id'],
                'category_id' => (string) ($doc['category_id'] ?? ''),
                'name' => $doc['name'],
                'slug' => $doc['slug'] ?? \Illuminate\Support\Str::slug($doc['name']),
                'price' => $doc['price'] ?? 0,
                'stock' => $doc['stock'] ?? 0,
                'type' => $doc['type'] ?? null,
                'description' => $doc['description'] ?? null,
                'image' => $doc['image'] ?? null,
                'is_active' => true,
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });

        // --- 4. Migrate Buyers ---
        $this->migrateCollection($db, 'buyers', 'buyers', function ($doc) {
            return [
                'id' => (string) $doc['_id'],
                'name' => ($doc['firstName'] ?? '') . ' ' . ($doc['lastName'] ?? ''),
                'email' => $doc['email'] ?? null,
                'phone' => $doc['phone'] ?? null,
                'password' => $doc['password'] ?? null,
                'address' => $doc['address'] ?? null,
                'province_id' => $doc['provinceId'] ?? null,
                'city_id' => $doc['regencyId'] ?? null,
                'district_id' => $doc['districtId'] ?? null,
                'village_id' => $doc['villageId'] ?? null,
                'postal_code' => $doc['postalCode'] ?? null,
                'reseller_status' => $doc['reseller_status'] ?? 'none',
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });

         // --- 5. Migrate Resellers ---
         $this->migrateCollection($db, 'resellers', 'resellers', function ($doc) {
            return [
                'id' => (string) $doc['_id'],
                'name' => $doc['name'] ?? '',
                'phone' => $doc['phone'] ?? '',
                'address' => $doc['address'] ?? null,
                'status' => 'pending', // Default
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });

        // --- 6. Migrate Product Resellers ---
        $this->migrateCollection($db, 'product_resellers', 'product_resellers', function ($doc) {
            $pid = (string) ($doc['product_id'] ?? $doc['productId'] ?? '');
            $cid = (string) ($doc['category_id'] ?? $doc['categoryId'] ?? '');
            
            $parent = null;
            if ($pid) {
                $parent = \Illuminate\Support\Facades\DB::table('products')->where('id', $pid)->first();
                if (!$parent) {
                    echo "  [WARN] Parent Product not found for Reseller Item " . ((string)$doc['_id']) . " (PID: $pid)\n";
                }
            } else {
                 echo "  [WARN] No Product ID for Reseller Item " . ((string)$doc['_id']) . "\n";
            }

            return [
                'id' => (string) $doc['_id'],
                'product_id' => $pid,
                'category_id' => $cid,
                'name' => $doc['name'] ?? $parent->name ?? null,
                'description' => $doc['description'] ?? $parent->description ?? null,
                'type' => $doc['type'] ?? $parent->type ?? null,
                'size' => $doc['size'] ?? $parent->size ?? null,
                'price' => $doc['price'] ?? 0,
                'stock' => $doc['stock'] ?? 0,
                'is_active' => $doc['is_active'] ?? true,
                'image' => (!empty($doc['image']) ? $doc['image'] : ($parent->image ?? null)),
                'tier_pricing' => isset($doc['tier_pricing']) ? json_encode($doc['tier_pricing']) : null,
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });

        // --- 7. Migrate Transactions ---
        $this->migrateCollection($db, 'transactions', 'transactions', function ($doc) {
            return [
                'id' => (string) $doc['_id'],
                'buyer_id' => (string) ($doc['buyer_id'] ?? ''),
                'total_amount' => $doc['total_amount'] ?? 0,
                'status' => $doc['status'] ?? 'pending',
                'payment_proof' => $doc['payment_proof'] ?? null,
                'short_id' => $doc['short_id'] ?? null,
                'buyer_info' => isset($doc['buyer_info']) ? json_encode($doc['buyer_info']) : null,
                'products' => isset($doc['products']) ? json_encode($doc['products']) : null,
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });
        
         // --- 8. Migrate Banners ---
         $this->migrateCollection($db, 'banners', 'banners', function ($doc) {
            return [
                'id' => (string) $doc['_id'],
                'image' => $doc['image'] ?? '',
                'title' => null,
                'description' => null,
                'created_at' => $this->formatDate($doc['created_at'] ?? null),
                'updated_at' => $this->formatDate($doc['updated_at'] ?? null),
            ];
        });


        $this->info("Migration completed successfully!");
    }

    private function migrateCollection($db, $collectionName, $tableName, $callback)
    {
        $this->info("Migrating $collectionName -> $tableName...");
        $collection = $db->selectCollection($collectionName);
        $documents = $collection->find();
        $count = 0;

        foreach ($documents as $doc) {
            try {
                $data = $callback($doc);
                // Use InsertOrIgnore to prevent duplicates if running multiple times
                DB::table($tableName)->insertOrIgnore($data);
                $count++;
            } catch (\Exception $e) {
                $this->warn(" - Failed to insert record " . ((string)$doc['_id']) . ": " . $e->getMessage());
            }
        }
        $this->info(" - Moved $count records.");
    }

    private function formatDate($mongoDate)
    {
        if ($mongoDate instanceof \MongoDB\BSON\UTCDateTime) {
            return $mongoDate->toDateTime()->format('Y-m-d H:i:s');
        }
        if (is_string($mongoDate)) {
             // Try to parse string date
             try {
                 return max(date('Y-m-d H:i:s', strtotime($mongoDate)), '1970-01-01 00:00:00');
             } catch (\Exception $e) {}
        }
        return now();
    }
}
