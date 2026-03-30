<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tags
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('color')->default('#0073e6');
            $table->timestamps();
        });

        Schema::create('contact_tag', function (Blueprint $table) {
            $table->uuid('contact_id');
            $table->uuid('tag_id');
            $table->primary(['contact_id', 'tag_id']);
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });

        // Suppressions
        Schema::create('suppressions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('reason')->default('manual'); // manual, bounce, complaint, unsubscribe
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Subscription forms
        Schema::create('subscription_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('audience_id');
            $table->boolean('double_opt_in')->default(false);
            $table->string('redirect_url')->nullable();
            $table->string('confirmation_subject')->default('Please confirm your subscription');
            $table->text('confirmation_html')->nullable();
            $table->text('welcome_html')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('submissions_count')->default(0);
            $table->timestamps();
            $table->foreign('audience_id')->references('id')->on('audiences')->cascadeOnDelete();
        });

        // Tracking
        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('email_id');
            $table->string('type'); // sent, delivered, opened, clicked, bounced, unsubscribed, complained
            $table->string('url')->nullable(); // for click events
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->foreign('email_id')->references('id')->on('emails')->cascadeOnDelete();
            $table->index(['email_id', 'type']);
        });

        // Add tracking fields to emails
        Schema::table('emails', function (Blueprint $table) {
            $table->unsignedInteger('opens_count')->default(0)->after('sender_ip');
            $table->unsignedInteger('clicks_count')->default(0)->after('opens_count');
            $table->timestamp('opened_at')->nullable()->after('clicks_count');
            $table->timestamp('clicked_at')->nullable()->after('opened_at');
            $table->boolean('track_opens')->default(true)->after('clicked_at');
            $table->boolean('track_clicks')->default(true)->after('track_opens');
        });

        // Add confirmation fields to contacts
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('confirmed')->default(true)->after('unsubscribed');
            $table->string('confirmation_token')->nullable()->after('confirmed');
            $table->timestamp('confirmed_at')->nullable()->after('confirmation_token');
        });

        // Add UTM + A/B fields to broadcasts
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->boolean('utm_tags')->default(false)->after('failed_count');
            $table->string('utm_source')->nullable()->after('utm_tags');
            $table->string('utm_medium')->default('email')->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            // A/B testing
            $table->string('variant_b_subject')->nullable()->after('utm_campaign');
            $table->longText('variant_b_html')->nullable()->after('variant_b_subject');
            $table->unsignedInteger('test_percentage')->default(20)->after('variant_b_html');
            $table->string('winning_variant')->nullable()->after('test_percentage');
        });

        // Webhook endpoints
        Schema::table('webhooks', function (Blueprint $table) {
            $table->unsignedInteger('success_count')->default(0)->after('active');
            $table->unsignedInteger('failure_count')->default(0)->after('success_count');
            $table->timestamp('last_triggered_at')->nullable()->after('failure_count');
        });
    }

    public function down(): void
    {
        Schema::table('webhooks', function (Blueprint $t) { $t->dropColumn(['success_count','failure_count','last_triggered_at']); });
        Schema::table('broadcasts', function (Blueprint $t) { $t->dropColumn(['utm_tags','utm_source','utm_medium','utm_campaign','variant_b_subject','variant_b_html','test_percentage','winning_variant']); });
        Schema::table('contacts', function (Blueprint $t) { $t->dropColumn(['confirmed','confirmation_token','confirmed_at']); });
        Schema::table('emails', function (Blueprint $t) { $t->dropColumn(['opens_count','clicks_count','opened_at','clicked_at','track_opens','track_clicks']); });
        Schema::dropIfExists('email_events');
        Schema::dropIfExists('subscription_forms');
        Schema::dropIfExists('suppressions');
        Schema::dropIfExists('contact_tag');
        Schema::dropIfExists('tags');
    }
};
