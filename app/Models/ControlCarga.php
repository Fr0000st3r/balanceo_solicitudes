<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlCarga extends Model
{
    protected $table = 'tblcontrolcarga';
    protected $primaryKey = 'id_Control_Carga';
    public $timestamps = false;

    protected $fillable = [
        'id_Usuario',
        'anio',
        'total'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_Usuario', 'id_usuario');
    }
}
