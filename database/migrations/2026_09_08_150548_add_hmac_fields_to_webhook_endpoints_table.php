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
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->boolean('hmac_required')->default(false)->after('is_active');
            $table->text('hmac_secret')->nullable()->after('hmac_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            $table->dropColumn(['hmac_required', 'hmac_secret']);
        });
    }
};
