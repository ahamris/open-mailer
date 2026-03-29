<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('email_id');
            $table->string('filename');
            $table->string('content_type');
            $table->unsignedBigInteger('size');
            $table->string('storage_path');
            $table->string('content_id')->nullable(); // voor inline images
            $table->timestamps();

            $table->foreign('email_id')->references('id')->on('emails')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
