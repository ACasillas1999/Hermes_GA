<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waba_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('waba_templates', 'header_type')) {
                $table->string('header_type')->nullable()->after('category');
            }
            if (!Schema::hasColumn('waba_templates', 'header_text')) {
                $table->text('header_text')->nullable()->after('header_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('waba_templates', function (Blueprint $table) {
            if (Schema::hasColumn('waba_templates', 'header_text')) {
                $table->dropColumn('header_text');
            }
            if (Schema::hasColumn('waba_templates', 'header_type')) {
                $table->dropColumn('header_type');
            }
        });
    }
};
