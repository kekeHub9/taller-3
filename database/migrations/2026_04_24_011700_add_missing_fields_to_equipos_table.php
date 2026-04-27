<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            
            if (!Schema::hasColumn('equipos', 'proveedor')) {
                $table->string('proveedor')->nullable()->after('fecha_adquisicion');
            }
            if (!Schema::hasColumn('equipos', 'vida_util')) {
                $table->integer('vida_util')->nullable()->after('proxima_calibracion');
            }
            if (!Schema::hasColumn('equipos', 'depreciacion')) {
                $table->decimal('depreciacion', 10, 2)->nullable()->after('vida_util');
            }
            // Verificar que timestamps exista
            if (!Schema::hasColumn('equipos', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['proveedor', 'vida_util', 'depreciacion']);
        });
    }
};