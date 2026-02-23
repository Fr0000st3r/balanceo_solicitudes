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
        Schema::create('tblgrupos_sistema', function (Blueprint $table) {
            $table->increments('cve_grupo_sistema'); // PK
            $table->string('descripcion_grupo', 250);
            $table->integer('activo')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_sistemas');
    }
};
