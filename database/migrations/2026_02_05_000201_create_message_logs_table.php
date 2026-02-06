<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('message_logs')) {
            return;
        }

        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empleado_id')->nullable()->index();
            $table->string('template_name');
            $table->string('template_language')->nullable();
            $table->string('status')->default('sent');
            $table->json('response')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
