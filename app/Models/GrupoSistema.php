<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoSistema extends Model
{
    protected $table = 'tblgrupos_sistema';
    protected $primaryKey = 'cve_grupo_sistema';
    public $timestamps = false;

    protected $fillable = [
        'descripcion_grupo',
        'activo'
    ];
}
