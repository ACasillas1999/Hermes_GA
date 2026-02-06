<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('empleados')) {
            return;
        }

        Schema::create('empleados', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Puesto');
            $table->string('Nombre');
            $table->string('Numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
