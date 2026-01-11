<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Users
        Schema::create('users', function (Blueprint $table) {
            $table->string('id', 24)->primary(); // Mongo ObjectID length
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 4. Banners
        Schema::create('banners', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('image');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 5. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('types')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Products
        Schema::create('products', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('category_id', 24)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(0);
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('size')->nullable();
            $table->integer('discount_percentage')->nullable();
            $table->integer('discount_duration')->nullable();
            $table->timestamp('discount_start_date')->nullable();
            $table->timestamp('discount_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key usually creates constraint, but with string IDs and potential missing parents from Mongo, we can skip strict foreign key constraints or use them if clean.
            // Let's add them for integrity if possible, but use onDelete cascade.
             $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        // 7. Buyers
        Schema::create('buyers', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->text('address')->nullable();
            $table->string('province_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('district_id')->nullable();
            $table->string('village_id')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('reseller_status')->default('none');
            $table->timestamps();
        });

        // 8. Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('buyer_id', 24)->index();
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('pending'); // pending, paid, shipped, completed, cancelled
            $table->string('payment_proof')->nullable();
            $table->string('short_id')->nullable()->index();
            $table->json('buyer_info')->nullable(); // Storing snapshot as JSON
            $table->json('products')->nullable();   // Storing snapshot as JSON
            $table->timestamps();
            
            $table->foreign('buyer_id')->references('id')->on('buyers')->onDelete('cascade');
        });

        // 9. Resellers
        Schema::create('resellers', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('name');
            $table->string('phone')->unique();
            $table->text('address')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });

        // 10. Product Resellers (Reseller Prices)
        Schema::create('product_resellers', function (Blueprint $table) {
            $table->string('id', 24)->primary();
            $table->string('product_id', 24)->index();
            $table->string('category_id', 24)->index(); 
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->json('tier_pricing')->nullable(); 
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_resellers');
        Schema::dropIfExists('resellers');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('buyers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
