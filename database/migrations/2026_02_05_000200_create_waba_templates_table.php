<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('waba_templates')) {
            return;
        }

        Schema::create('waba_templates', function (Blueprint $table) {
            $table->id();
            $table->string('meta_template_id')->nullable();
            $table->string('waba_id')->nullable();
            $table->string('name');
            $table->string('language')->nullable();
            $table->string('status')->nullable();
            $table->string('category')->nullable();
            $table->json('components')->nullable();
            $table->json('quality_score')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['name', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waba_templates');
    }
};
