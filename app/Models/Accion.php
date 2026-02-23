<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accion extends Model
{
    protected $table = 'tblacciones';
    protected $primaryKey = 'cve_accion';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'activo'
    ];
}
