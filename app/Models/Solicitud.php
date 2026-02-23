<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'tblsolicitudes';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = false;

    protected $fillable = [
        'id_Usuario_Asignado',
        'nombre_Solicitante',
        'paterno_Solicitante',
        'materno_Solicitante',
        'activo',
        'fecha_Solicitud'
    ];

    public function usuarioAsignado()
    {
        return $this->belongsTo(Usuario::class, 'id_Usuario_Asignado', 'id_usuario');
    }
}
