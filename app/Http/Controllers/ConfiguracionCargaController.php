<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BitacoraService;
use App\Models\ConfiguracionCarga;

class ConfiguracionCargaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $q = ConfiguracionCarga::query();

        if ($request->filled('anio')) {
            $q->where('anio', (int) $request->query('anio'));
        }

        if ($request->filled('activo')) {
            $q->where('activo', (int) $request->query('activo'));
        }

        $q->orderByDesc('anio')->orderByDesc('id_Configuracion_Carga');

        // Bitácora (operador)
        $operadorId = (int) $request->header('X-User-Id', 0);
        if ($operadorId) {
            BitacoraService::log($operadorId, 'CONFIGURACION_CARGA', 'Consultó configuración de carga');
        }

        return $q->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proporcion' => ['required', 'integer', 'min:1'],
            'diferencia' => ['required', 'integer', 'min:0'],
            'anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'activo' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $data['activo'] = $data['activo'] ?? 1;

        // (Opcional) evitar duplicados por año
        // si quieres 1 config activa por año, desactiva las anteriores del mismo año:
        // ConfiguracionCarga::where('anio', $data['anio'])->update(['activo' => 0]);

        $config = ConfiguracionCarga::create($data);

        $operadorId = (int) $request->header('X-User-Id', 0);
        if ($operadorId) {
            BitacoraService::log(
                $operadorId,
                'CONFIGURACION_CARGA',
                "Creó configuración {$config->id_Configuracion_Carga} para año {$config->anio}"
            );
        }

        return response()->json([
            'message' => 'Configuración creada',
            'configuracion' => $config
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $config = ConfiguracionCarga::findOrFail($id);
        return response()->json($config);
    }

    public function update(Request $request, int $id)
    {
        $config = ConfiguracionCarga::findOrFail($id);

        $data = $request->validate([
            'proporcion' => ['sometimes', 'integer', 'min:1'],
            'diferencia' => ['sometimes', 'integer', 'min:0'],
            'anio' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'activo' => ['sometimes', 'integer', 'in:0,1'],
        ]);

        $config->update($data);

        $operadorId = (int) $request->header('X-User-Id', 0);
        if ($operadorId) {
            BitacoraService::log(
                $operadorId,
                'CONFIGURACION_CARGA',
                "Actualizó configuración {$config->id_Configuracion_Carga}"
            );
        }

        return response()->json([
            'message' => 'Configuración actualizada',
            'configuracion' => $config
        ]);
    }

    // Baja lógica: activo = 0 (mejor que delete real)
    public function destroy(Request $request, int $id)
    {
        $config = ConfiguracionCarga::findOrFail($id);
        $config->activo = 0;
        $config->save();

        $operadorId = (int) $request->header('X-User-Id', 0);
        if ($operadorId) {
            BitacoraService::log(
                $operadorId,
                'CONFIGURACION_CARGA',
                "Desactivó configuración {$config->id_Configuracion_Carga}"
            );
        }

        return response()->json([
            'message' => 'Configuración desactivada',
            'configuracion' => $config
        ]);
    }
}
