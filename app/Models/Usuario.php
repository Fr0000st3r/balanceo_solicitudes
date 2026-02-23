<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'tblusuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'paterno',
        'materno',
        'login',
        'password',
        'activo',
        'cve_grupo'
    ];

    public function grupo()
    {
        return $this->belongsTo(GrupoSistema::class, 'cve_grupo', 'cve_grupo_sistema');
    }
}
