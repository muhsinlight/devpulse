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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->timestamp('opened_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('last_error_message', 1000)->nullable();
            $table->unsignedSmallInteger('opened_status_code')->nullable();
            $table->unsignedSmallInteger('resolved_status_code')->nullable();
            $table->timestamps();

            $table->index(['monitor_id', 'resolved_at']);
            $table->index(['status', 'opened_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
