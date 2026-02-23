<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'tblbitacoras';
    protected $primaryKey = 'id_bitacora';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'cve_accion',
        'fecha',
        'movimiento'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function accion()
    {
        return $this->belongsTo(Accion::class, 'cve_accion', 'cve_accion');
    }
}
