<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drip_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('audience_id');
            $table->string('trigger_type')->default('subscription'); // subscription, tag_added, manual
            $table->string('trigger_value')->nullable(); // tag name, etc.
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->boolean('active')->default(false);
            $table->unsignedInteger('enrolled_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->timestamps();
            $table->foreign('audience_id')->references('id')->on('audiences')->cascadeOnDelete();
        });

        Schema::create('drip_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('drip_campaign_id');
            $table->unsignedInteger('position')->default(0);
            $table->string('type')->default('email'); // email, delay, condition
            // Email step
            $table->string('subject')->nullable();
            $table->longText('html_body')->nullable();
            $table->uuid('template_id')->nullable();
            // Delay step
            $table->unsignedInteger('delay_days')->default(1);
            $table->unsignedInteger('delay_hours')->default(0);
            // Condition step
            $table->string('condition_field')->nullable(); // opened_previous, clicked_previous, has_tag
            $table->string('condition_value')->nullable();
            // Stats
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('opens_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();
            $table->foreign('drip_campaign_id')->references('id')->on('drip_campaigns')->cascadeOnDelete();
            $table->foreign('template_id')->references('id')->on('templates')->nullOnDelete();
        });

        Schema::create('drip_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('drip_campaign_id');
            $table->uuid('contact_id');
            $table->unsignedInteger('current_step')->default(0);
            $table->string('status')->default('active'); // active, completed, paused, cancelled
            $table->timestamp('next_action_at')->nullable();
            $table->timestamps();
            $table->foreign('drip_campaign_id')->references('id')->on('drip_campaigns')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->unique(['drip_campaign_id', 'contact_id']);
        });

        // Add grapes_json to templates for drag-and-drop builder state
        Schema::table('templates', function (Blueprint $table) {
            $table->longText('grapes_json')->nullable()->after('text_body');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $t) { $t->dropColumn('grapes_json'); });
        Schema::dropIfExists('drip_enrollments');
        Schema::dropIfExists('drip_steps');
        Schema::dropIfExists('drip_campaigns');
    }
};
