<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('priority')->default(0);
            $table->json('triggers');   // conditions: from, to, subject, has_attachment, etc.
            $table->json('actions');    // forward, reply, label, webhook, ai_respond, etc.
            $table->unsignedInteger('times_triggered')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('workflow_id');
            $table->uuid('email_id');
            $table->string('action');
            $table->string('status'); // success, failed
            $table->text('result')->nullable();
            $table->timestamps();
            $table->foreign('workflow_id')->references('id')->on('workflows')->cascadeOnDelete();
            $table->foreign('email_id')->references('id')->on('emails')->cascadeOnDelete();
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('email_id');
            $table->string('action'); // compose, reply, summarize
            $table->text('prompt');
            $table->longText('response');
            $table->string('model')->default('claude-sonnet-4-20250514');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->timestamps();
            $table->foreign('email_id')->references('id')->on('emails')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('workflow_logs');
        Schema::dropIfExists('workflows');
    }
};
