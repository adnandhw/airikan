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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('weight')->nullable()->after('size')->comment('in grams');
        });

        Schema::table('product_resellers', function (Blueprint $table) {
            $table->integer('weight')->nullable()->after('size')->comment('in grams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('product_resellers', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
