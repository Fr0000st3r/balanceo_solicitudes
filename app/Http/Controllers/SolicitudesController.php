<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\BitacoraService;

class SolicitudesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre_Solicitante' => ['required', 'string', 'max:100'],
            'paterno_Solicitante' => ['required', 'string', 'max:100'],
            'materno_Solicitante' => ['required', 'string', 'max:100'],
        ]);

        $idUsuario = $this->asignarUsuario();

        $solicitud = Solicitud::create([
            'id_Usuario_Asignado' => $idUsuario,
            'nombre_Solicitante' => $request->nombre_Solicitante,
            'paterno_Solicitante' => $request->paterno_Solicitante,
            'materno_Solicitante' => $request->materno_Solicitante,
            'activo' => 1,
            'fecha_Solicitud' => Carbon::now(),
        ]);

        $this->actualizarCarga($idUsuario);

        BitacoraService::log(
            idUsuario: $idUsuario,
            accion: 'CREAR_SOLICITUD',
            movimiento: "Creó solicitud {$solicitud->id_solicitud} asignada a usuario {$idUsuario}"
        );

        return response()->json([
            'message' => 'Solicitud creada',
            'solicitud' => $solicitud
        ], 201);
    }

    private function asignarUsuario()
    {
        $anio = now()->year;

        $usuarios = Usuario::where('activo', 1)->get();

        if ($usuarios->isEmpty()) {
            abort(422, 'No hay usuarios activos para asignar');
        }

        $cargas = DB::table('tblcontrolcarga')
            ->where('anio', $anio)
            ->get()
            ->keyBy('id_Usuario');

        $usuarioConMenorCarga = null;
        $menorCarga = PHP_INT_MAX;

        foreach ($usuarios as $usuario) {
            if (!isset($cargas[$usuario->id_usuario])) {
                return $usuario->id_usuario;
            }

            $cargaActual = $cargas[$usuario->id_usuario]->total;

            if ($cargaActual < $menorCarga) {
                $menorCarga = $cargaActual;
                $usuarioConMenorCarga = $usuario;
            }
        }

        return $usuarioConMenorCarga->id_usuario;
    }

    private function actualizarCarga($idUsuario)
    {
        $anio = now()->year;

        $carga = DB::table('tblcontrolcarga')
            ->where('anio', $anio)
            ->where('id_Usuario', $idUsuario)
            ->first();

        if ($carga) {
            DB::table('tblcontrolcarga')
                ->where('anio', $anio)
                ->where('id_Usuario', $idUsuario)
                ->update([
                    'total' => $carga->total + 1
                ]);
        } else {
            DB::table('tblcontrolcarga')->insert([
                'anio' => $anio,
                'id_Usuario' => $idUsuario,
                'total' => 1
            ]);
        }
    }

    public function cancelar(int $id)
    {
        return DB::transaction(function () use ($id) {

            $solicitud = Solicitud::lockForUpdate()->findOrFail($id);

            // Si ya estaba cancelada, no restes doble
            if ((int) $solicitud->activo === 0) {
                return response()->json([
                    'message' => 'La solicitud ya estaba cancelada',
                    'solicitud' => $solicitud
                ], 409);
            }

            // Cancelar solicitud
            $solicitud->activo = 0;
            $solicitud->save();

            $anio = \Carbon\Carbon::parse($solicitud->fecha_Solicitud)->year;
            $idUsuario = (int) $solicitud->id_Usuario_Asignado;

            $carga = DB::table('tblcontrolcarga')
                ->where('anio', $anio)
                ->where('id_Usuario', $idUsuario)
                ->lockForUpdate()
                ->first();

            if ($carga && (int) $carga->total > 0) {
                DB::table('tblcontrolcarga')
                    ->where('id_Control_Carga', $carga->id_Control_Carga)
                    ->update([
                        'total' => (int) $carga->total - 1
                    ]);
            }

            BitacoraService::log(
                idUsuario: $idUsuario,
                accion: 'CANCELAR_SOLICITUD',
                movimiento: "Canceló solicitud {$solicitud->id_solicitud} asignada a usuario {$idUsuario}"
            );

            return response()->json([
                'message' => 'Solicitud cancelada y carga actualizada',
                'solicitud' => $solicitud
            ], 200);
        });
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100)); // límite sano

        $q = Solicitud::query();

        // Filtros opcionales
        if ($request->filled('activo')) {
            $q->where('activo', (int) $request->query('activo'));
        }

        if ($request->filled('anio')) {
            $anio = (int) $request->query('anio');
            $q->whereYear('fecha_Solicitud', $anio);
        }

        if ($request->filled('id_usuario_asignado')) {
            $q->where('id_Usuario_Asignado', (int) $request->query('id_usuario_asignado'));
        }

        if ($request->filled('q')) {
            $term = trim($request->query('q'));
            $q->where(function ($sub) use ($term) {
                $sub->where('nombre_Solicitante', 'like', "%{$term}%")
                    ->orWhere('paterno_Solicitante', 'like', "%{$term}%")
                    ->orWhere('materno_Solicitante', 'like', "%{$term}%");
            });
        }

        $q->orderByDesc('fecha_Solicitud');

        // (Opcional) Bitácora de consulta — usa operador si ya lo tienes
        // $operadorId = (int) $request->header('X-User-Id', 1);
        // BitacoraService::log($operadorId, 'CONSULTAR_SOLICITUDES', 'Consultó listado de solicitudes');

        return $q->paginate($perPage);
    }

    public function show(Request $request, int $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        // (Opcional) Bitácora
        // $operadorId = (int) $request->header('X-User-Id', 1);
        // BitacoraService::log($operadorId, 'CONSULTAR_SOLICITUD', "Consultó solicitud {$id}");

        return response()->json($solicitud);
    }
}
