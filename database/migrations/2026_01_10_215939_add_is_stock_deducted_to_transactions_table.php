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
            $table->boolean('is_stock_deducted')->default(false)->after('status');
        });

        // Backfill for existing 'approve' transactions
        \Illuminate\Support\Facades\DB::table('transactions')
            ->where('status', 'approve')
            ->update(['is_stock_deducted' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('is_stock_deducted');
        });
    }
};
