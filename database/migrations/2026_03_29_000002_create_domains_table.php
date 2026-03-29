<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('status')->default('pending'); // pending, verified, failed
            $table->boolean('spf_valid')->default(false);
            $table->boolean('dkim_valid')->default(false);
            $table->boolean('dmarc_valid')->default(false);
            $table->boolean('mx_valid')->default(false);
            $table->string('dkim_selector')->default('clom');
            $table->text('dkim_public_key')->nullable();
            $table->text('dkim_private_key')->nullable();
            $table->json('dns_records')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
