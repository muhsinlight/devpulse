<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monitors', function (Blueprint $table) {
            $table->timestamp('next_check_at')->nullable()->after('is_active');
            $table->index(['is_active', 'next_check_at']);
        });

        DB::table('monitors')->update(['next_check_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitors', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'next_check_at']);
            $table->dropColumn('next_check_at');
        });
    }
};
