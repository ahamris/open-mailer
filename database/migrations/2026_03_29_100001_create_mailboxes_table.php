<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('smtp_host')->default('mail.worxone.nl');
            $table->integer('smtp_port')->default(587);
            $table->string('smtp_username');
            $table->text('smtp_password');
            $table->string('smtp_encryption')->nullable();
            $table->string('imap_host')->default('mail.worxone.nl');
            $table->integer('imap_port')->default(993);
            $table->string('signature_html')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Voeg mailbox_id toe aan emails
        Schema::table('emails', function (Blueprint $table) {
            $table->uuid('mailbox_id')->nullable()->after('api_key_id');
            $table->uuid('thread_id')->nullable()->after('mailbox_id');
            $table->uuid('parent_id')->nullable()->after('thread_id');
            $table->boolean('is_read')->default(false)->after('status');
            $table->boolean('is_starred')->default(false)->after('is_read');
            $table->string('folder')->default('inbox')->after('is_starred');
            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign(['mailbox_id']);
            $table->dropColumn(['mailbox_id', 'thread_id', 'parent_id', 'is_read', 'is_starred', 'folder']);
        });
        Schema::dropIfExists('mailboxes');
    }
};
