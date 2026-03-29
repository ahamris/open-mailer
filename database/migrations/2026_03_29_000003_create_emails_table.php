<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('api_key_id')->nullable();
            $table->string('direction')->default('outbound'); // outbound, inbound
            $table->string('status')->default('queued'); // queued, sending, sent, delivered, bounced, failed
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->json('to_addresses');
            $table->json('cc_addresses')->nullable();
            $table->json('bcc_addresses')->nullable();
            $table->json('reply_to')->nullable();
            $table->string('subject');
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->json('headers')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->string('message_id')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->text('bounce_reason')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            // Inbound-specifieke velden
            $table->string('spf_result')->nullable();
            $table->string('dkim_result')->nullable();
            $table->string('dmarc_result')->nullable();
            $table->string('sender_ip')->nullable();
            $table->timestamps();

            $table->foreign('api_key_id')->references('id')->on('api_keys')->nullOnDelete();
            $table->index(['status', 'direction']);
            $table->index('from_address');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
