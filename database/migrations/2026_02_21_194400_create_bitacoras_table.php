<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tblbitacoras', function (Blueprint $table) {
            $table->increments('id_bitacora');

            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('cve_accion');

            $table->dateTime('fecha')->useCurrent();
            $table->mediumText('movimiento')->nullable();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('tblusuarios')
                ->onDelete('cascade');

            $table->foreign('cve_accion')
                ->references('cve_accion')
                ->on('tblacciones')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
