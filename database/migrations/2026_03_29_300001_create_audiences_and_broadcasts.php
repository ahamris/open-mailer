<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('audience_contact', function (Blueprint $table) {
            $table->uuid('audience_id');
            $table->uuid('contact_id');
            $table->timestamps();
            $table->primary(['audience_id', 'contact_id']);
            $table->foreign('audience_id')->references('id')->on('audiences')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
        });

        Schema::create('broadcasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('template_id')->nullable();
            $table->uuid('audience_id')->nullable();
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->string('subject');
            $table->longText('html_body')->nullable();
            $table->string('status')->default('draft'); // draft, sending, sent, failed
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->foreign('template_id')->references('id')->on('templates')->nullOnDelete();
            $table->foreign('audience_id')->references('id')->on('audiences')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
        Schema::dropIfExists('audience_contact');
        Schema::dropIfExists('audiences');
    }
};
