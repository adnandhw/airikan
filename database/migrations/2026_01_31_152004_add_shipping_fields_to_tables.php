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
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('total_amount');
            $table->string('courier_name')->nullable()->after('shipping_cost');
            $table->integer('total_weight')->default(0)->after('courier_name'); // in grams
            $table->decimal('distance', 8, 2)->default(0)->after('total_weight'); // in km
            $table->decimal('total_payment', 15, 2)->default(0)->after('distance'); // total_amount + shipping_cost
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'courier_name', 'total_weight', 'distance', 'total_payment']);
        });
    }
};
