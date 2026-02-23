<?php

use App\Http\Controllers\SolicitudesController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ConfiguracionCargaController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('session.timeout')->group(function () {
    Route::post('/solicitudes', [SolicitudesController::class, 'store']);

    Route::put('/solicitudes/{id}/cancelar', [SolicitudesController::class, 'cancelar']);

    Route::get('/solicitudes', [SolicitudesController::class, 'index']);
    Route::get('/solicitudes/{id}', [SolicitudesController::class, 'show']);

    Route::get('/reportes/solicitudes-por-usuario', [ReportesController::class, 'solicitudesPorUsuario']);
    Route::get('/reportes/solicitudes-por-usuario/{idUsuario}', [ReportesController::class, 'detalleSolicitudesPorUsuario']);

    Route::get('/reportes/solicitudes-por-usuario/export/html', [ReportesController::class, 'exportSolicitudesPorUsuarioHtml']);

    Route::get('/reportes/solicitudes-por-usuario/export/csv', [ReportesController::class, 'exportSolicitudesPorUsuarioCsv']);

    Route::apiResource('configuracion-carga', ConfiguracionCargaController::class);
});

