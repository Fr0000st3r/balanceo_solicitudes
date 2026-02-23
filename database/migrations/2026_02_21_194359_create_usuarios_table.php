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
        Schema::create('tblusuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('nombre', 100);
            $table->string('paterno', 100);
            $table->string('materno', 100);
            $table->string('login', 250)->unique();
            $table->string('password', 250);
            $table->integer('activo')->default(1);

            $table->unsignedInteger('cve_grupo'); // FK

            $table->foreign('cve_grupo')
                ->references('cve_grupo_sistema')
                ->on('tblgrupos_sistema')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
