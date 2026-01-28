<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Design Decision (from interview):
     * - Both: In-app banner + optional email notification
     * - Target: all tenants, specific tenants, or specific plans
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('content');
            $table->enum('type', ['info', 'warning', 'maintenance', 'feature', 'promotion'])->default('info');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_dismissible')->default(true);
            $table->boolean('send_email')->default(false);
            $table->enum('email_target', ['admin_only', 'all_users'])->default('admin_only');
            $table->enum('target', ['all', 'specific_tenants', 'specific_plans'])->default('all');
            $table->json('target_ids')->nullable()->comment('Array of tenant IDs or plan codes');
            $table->string('action_url')->nullable()->comment('Optional CTA button URL');
            $table->string('action_text', 50)->nullable()->comment('Optional CTA button text');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('type');
        });

        // Track which users have dismissed which announcements
        Schema::create('announcement_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('dismissed_at');

            $table->unique(['announcement_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_dismissals');
        Schema::dropIfExists('announcements');
    }
};
