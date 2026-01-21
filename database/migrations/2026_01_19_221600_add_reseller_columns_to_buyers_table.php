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
        Schema::table('buyers', function (Blueprint $table) {
            if (!Schema::hasColumn('buyers', 'is_reseller')) {
                $table->boolean('is_reseller')->default(false)->after('postal_code');
            }
            if (!Schema::hasColumn('buyers', 'reseller_status')) {
                $table->string('reseller_status')->nullable()->after('is_reseller');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn(['is_reseller', 'reseller_status']);
        });
    }
};
