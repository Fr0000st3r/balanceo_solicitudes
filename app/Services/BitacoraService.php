<?php

namespace App\Services;

use App\Models\Accion;
use App\Models\Bitacora;

class BitacoraService
{
    public static function log(int $idUsuario, string $accion, string $movimiento = ''): void
    {
        $accionRow = Accion::where('descripcion', $accion)->where('activo', 1)->first();

        // si no existe la acción, no rompemos el flujo
        $cveAccion = $accionRow?->cve_accion ?? 0;

        Bitacora::create([
            'id_usuario' => $idUsuario,
            'cve_accion' => $cveAccion,
            'fecha' => now(),
            'movimiento' => $movimiento
        ]);
    }
}