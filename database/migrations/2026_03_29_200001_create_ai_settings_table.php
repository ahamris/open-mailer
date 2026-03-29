<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('anthropic'); // anthropic, openai, gemini, ollama, etc
            $table->string('model')->nullable();
            $table->text('api_key')->nullable(); // encrypted
            $table->string('base_url')->nullable(); // for ollama/custom
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
