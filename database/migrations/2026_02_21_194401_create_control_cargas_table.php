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
        Schema::create('tblcontrolcarga', function (Blueprint $table) {
            $table->increments('id_Control_Carga');
            $table->unsignedInteger('id_Usuario');
            $table->integer('anio');
            $table->integer('total')->default(0);

            $table->foreign('id_Usuario')
                ->references('id_usuario')
                ->on('tblusuarios')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_cargas');
    }
};
