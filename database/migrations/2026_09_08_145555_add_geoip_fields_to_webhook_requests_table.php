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
        Schema::table('webhook_requests', function (Blueprint $table) {
            $table->string('ip_iso_code', 8)->nullable()->after('ip_address');
            $table->string('ip_country')->nullable()->after('ip_iso_code');
            $table->string('ip_city')->nullable()->after('ip_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_requests', function (Blueprint $table) {
            $table->dropColumn(['ip_iso_code', 'ip_country', 'ip_city']);
        });
    }
};
