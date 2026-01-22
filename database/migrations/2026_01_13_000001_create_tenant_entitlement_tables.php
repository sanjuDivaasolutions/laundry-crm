<?php

declare(strict_types=1);

use App\Enums\QuotaPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tenant Features (Boolean Flags)
        Schema::create('tenant_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('feature_code'); // e.g., 'sso', 'api_access'
            $table->boolean('enabled')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_code']);
        });

        // Tenant Quotas (Defined Limits)
        Schema::create('tenant_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('quota_code'); // e.g., 'max_users', 'storage_gb'
            $table->integer('limit')->default(0); 
            $table->string('period')->default(QuotaPeriod::LIFETIME->value);
            $table->timestamps();

            $table->unique(['tenant_id', 'quota_code']);
        });

        // Tenant Usage (Tracking Consumption)
        Schema::create('tenant_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('quota_code');
            $table->integer('current_usage')->default(0);
            $table->timestamp('reset_at')->nullable(); // Next reset date for periodic quotas
            $table->timestamps();

            $table->unique(['tenant_id', 'quota_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_usage');
        Schema::dropIfExists('tenant_quotas');
        Schema::dropIfExists('tenant_features');
    }
};
