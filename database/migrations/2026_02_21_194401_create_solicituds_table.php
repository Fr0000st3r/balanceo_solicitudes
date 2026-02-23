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
        Schema::create('tblsolicitudes', function (Blueprint $table) {
            $table->increments('id_solicitud');

            $table->unsignedInteger('id_Usuario_Asignado');

            $table->string('nombre_Solicitante', 100);
            $table->string('paterno_Solicitante', 100);
            $table->string('materno_Solicitante', 100);

            $table->integer('activo')->default(1);
            $table->dateTime('fecha_Solicitud')->useCurrent();

            $table->foreign('id_Usuario_Asignado')
                ->references('id_usuario')
                ->on('tblusuarios')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};
