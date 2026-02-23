<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionCarga extends Model
{
    protected $table = 'tblconfiguracioncarga';
    protected $primaryKey = 'id_Configuracion_Carga';
    public $timestamps = false;

    protected $fillable = [
        'proporcion',
        'diferencia',
        'anio',
        'activo'
    ];
}
