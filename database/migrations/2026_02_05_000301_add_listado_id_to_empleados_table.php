<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados', 'listado_id')) {
                $table->unsignedBigInteger('listado_id')->nullable()->index()->after('Numero');
            }
        });

        if (!Schema::hasTable('listados')) {
            return;
        }

        $puestos = DB::table('empleados')
            ->select('Puesto')
            ->distinct()
            ->pluck('Puesto');

        foreach ($puestos as $puesto) {
            if ($puesto === null || $puesto === '') {
                continue;
            }

            $listadoId = DB::table('listados')->where('nombre', $puesto)->value('id');

            if (!$listadoId) {
                $listadoId = DB::table('listados')->insertGetId([
                    'nombre' => $puesto,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('empleados')
                ->where('Puesto', $puesto)
                ->update(['listado_id' => $listadoId]);
        }
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'listado_id')) {
                $table->dropIndex(['listado_id']);
                $table->dropColumn('listado_id');
            }
        });
    }
};
