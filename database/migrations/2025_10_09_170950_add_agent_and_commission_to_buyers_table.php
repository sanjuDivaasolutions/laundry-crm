<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            if (!Schema::hasColumn('buyers', 'agent_name')) {
                $table->string('agent_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('buyers', 'commission_rate')) {
                $table->decimal('commission_rate', 8, 2)->nullable()->after('agent_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            if (Schema::hasColumn('buyers', 'agent_name')) {
                $table->dropColumn('agent_name');
            }

            if (Schema::hasColumn('buyers', 'commission_rate')) {
                $table->dropColumn('commission_rate');
            }
        });
    }
};
