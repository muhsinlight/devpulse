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
        Schema::create('webhook_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('method', 16);
            $table->json('headers');
            $table->json('query_params')->nullable();
            $table->json('payload')->nullable();
            $table->text('raw_body')->nullable();
            $table->string('content_type')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['webhook_endpoint_id', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_requests');
    }
};
