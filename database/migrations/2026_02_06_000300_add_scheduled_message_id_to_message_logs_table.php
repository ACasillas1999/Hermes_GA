<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('message_logs') || Schema::hasColumn('message_logs', 'scheduled_message_id')) {
            return;
        }

        Schema::table('message_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('scheduled_message_id')->nullable()->after('empleado_id');
            $table->index('scheduled_message_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('message_logs') || !Schema::hasColumn('message_logs', 'scheduled_message_id')) {
            return;
        }

        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropIndex(['scheduled_message_id']);
            $table->dropColumn('scheduled_message_id');
        });
    }
};
