<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    // GET /api/reportes/solicitudes-por-usuario?anio=2026&activo=1
    public function solicitudesPorUsuario(Request $request)
    {
        $anio = $request->filled('anio') ? (int) $request->query('anio') : null;
        $activo = $request->filled('activo') ? (int) $request->query('activo') : null;

        $q = Solicitud::query();

        if ($anio !== null) {
            $q->whereYear('fecha_Solicitud', $anio);
        }

        if ($activo !== null) {
            $q->where('activo', $activo);
        }

        // Conteo por usuario asignado
        $rows = $q->select('id_Usuario_Asignado', DB::raw('COUNT(*) as total'))
            ->groupBy('id_Usuario_Asignado')
            ->orderByDesc('total')
            ->get();

        // Traer nombres de usuarios para mostrar bonito
        $usuarios = Usuario::whereIn('id_usuario', $rows->pluck('id_Usuario_Asignado'))
            ->get(['id_usuario', 'nombre', 'paterno', 'materno', 'login'])
            ->keyBy('id_usuario');

        $reporte = $rows->map(function ($r) use ($usuarios) {
            $u = $usuarios[$r->id_Usuario_Asignado] ?? null;

            return [
                'id_usuario' => (int) $r->id_Usuario_Asignado,
                'login' => $u?->login,
                'nombre_completo' => $u ? trim($u->nombre . ' ' . $u->paterno . ' ' . $u->materno) : null,
                'total_solicitudes' => (int) $r->total,
            ];
        });

        return response()->json([
            'filtros' => [
                'anio' => $anio,
                'activo' => $activo,
            ],
            'data' => $reporte,
        ]);
    }

    // GET /api/reportes/solicitudes-por-usuario/{idUsuario}?per_page=20&anio=2026&activo=1
    public function detalleSolicitudesPorUsuario(Request $request, int $idUsuario)
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $anio = $request->filled('anio') ? (int) $request->query('anio') : null;
        $activo = $request->filled('activo') ? (int) $request->query('activo') : null;

        // valida que exista el usuario
        $usuario = Usuario::findOrFail($idUsuario);

        $q = Solicitud::where('id_Usuario_Asignado', $idUsuario);

        if ($anio !== null) {
            $q->whereYear('fecha_Solicitud', $anio);
        }

        if ($activo !== null) {
            $q->where('activo', $activo);
        }

        $q->orderByDesc('fecha_Solicitud');

        return response()->json([
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'login' => $usuario->login,
                'nombre_completo' => trim($usuario->nombre . ' ' . $usuario->paterno . ' ' . $usuario->materno),
            ],
            'filtros' => [
                'anio' => $anio,
                'activo' => $activo,
            ],
            'solicitudes' => $q->paginate($perPage),
        ]);
    }

    public function exportSolicitudesPorUsuarioHtml(Request $request)
    {
        $anio = $request->filled('anio') ? (int) $request->query('anio') : null;
        $activo = $request->filled('activo') ? (int) $request->query('activo') : null;

        $q = \App\Models\Solicitud::query();

        if ($anio !== null) {
            $q->whereYear('fecha_Solicitud', $anio);
        }

        if ($activo !== null) {
            $q->where('activo', $activo);
        }

        $rows = $q->select('id_Usuario_Asignado', DB::raw('COUNT(*) as total'))
            ->groupBy('id_Usuario_Asignado')
            ->orderByDesc('total')
            ->get();

        $usuarios = \App\Models\Usuario::whereIn('id_usuario', $rows->pluck('id_Usuario_Asignado'))
            ->get(['id_usuario', 'nombre', 'paterno', 'materno', 'login'])
            ->keyBy('id_usuario');

        $data = $rows->map(function ($r) use ($usuarios) {
            $u = $usuarios[$r->id_Usuario_Asignado] ?? null;

            return [
                'id_usuario' => (int) $r->id_Usuario_Asignado,
                'login' => $u?->login ?? '',
                'nombre_completo' => $u ? trim($u->nombre . ' ' . $u->paterno . ' ' . $u->materno) : '',
                'total_solicitudes' => (int) $r->total,
            ];
        });

        $titulo = 'Reporte: Solicitudes por usuario';
        $subtitulo = 'Filtros: '
            . 'anio=' . ($anio ?? 'todos') . ' | '
            . 'activo=' . ($activo === null ? 'todos' : $activo);

        // Construcción HTML simple (sin Blade para ir rápido)
        $html = '<!doctype html><html lang="es"><head><meta charset="utf-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
        $html .= '<title>' . e($titulo) . '</title>';
        $html .= '<style>
        body{font-family:Arial, sans-serif; margin:24px;}
        h1{margin:0 0 6px 0;}
        .sub{color:#555; margin:0 0 16px 0;}
        table{border-collapse:collapse; width:100%;}
        th,td{border:1px solid #ddd; padding:8px; text-align:left;}
        th{background:#f5f5f5;}
        tr:nth-child(even){background:#fafafa;}
        .right{text-align:right;}
    </style></head><body>';

        $html .= '<h1>' . e($titulo) . '</h1>';
        $html .= '<p class="sub">' . e($subtitulo) . '</p>';

        $html .= '<table><thead><tr>';
        $html .= '<th>ID Usuario</th><th>Login</th><th>Nombre</th><th class="right">Total solicitudes</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            $html .= '<td>' . e((string) $row['id_usuario']) . '</td>';
            $html .= '<td>' . e($row['login']) . '</td>';
            $html .= '<td>' . e($row['nombre_completo']) . '</td>';
            $html .= '<td class="right">' . e((string) $row['total_solicitudes']) . '</td>';
            $html .= '</tr>';
        }

        if ($data->isEmpty()) {
            $html .= '<tr><td colspan="4">Sin resultados para los filtros seleccionados.</td></tr>';
        }

        $html .= '</tbody></table></body></html>';

        // Para que el navegador lo trate como archivo descargable:
        $filename = 'reporte_solicitudes_por_usuario'
            . ($anio ? "_{$anio}" : '')
            . ($activo !== null ? "_activo{$activo}" : '')
            . '.html';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportSolicitudesPorUsuarioCsv(Request $request)
    {
        $anio = $request->filled('anio') ? (int) $request->query('anio') : null;
        $activo = $request->filled('activo') ? (int) $request->query('activo') : null;

        $q = \App\Models\Solicitud::query();

        if ($anio !== null) {
            $q->whereYear('fecha_Solicitud', $anio);
        }

        if ($activo !== null) {
            $q->where('activo', $activo);
        }

        $rows = $q->select('id_Usuario_Asignado', DB::raw('COUNT(*) as total'))
            ->groupBy('id_Usuario_Asignado')
            ->orderByDesc('total')
            ->get();

        $usuarios = \App\Models\Usuario::whereIn('id_usuario', $rows->pluck('id_Usuario_Asignado'))
            ->get(['id_usuario', 'nombre', 'paterno', 'materno', 'login'])
            ->keyBy('id_usuario');

        $filename = 'reporte_solicitudes_por_usuario'
            . ($anio ? "_{$anio}" : '')
            . ($activo !== null ? "_activo{$activo}" : '')
            . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($rows, $usuarios) {
            $out = fopen('php://output', 'w');

            // (Opcional) BOM para que Excel respete acentos en UTF-8
            fwrite($out, "\xEF\xBB\xBF");

            // Encabezados CSV
            fputcsv($out, ['id_usuario', 'login', 'nombre_completo', 'total_solicitudes']);

            foreach ($rows as $r) {
                $u = $usuarios[$r->id_Usuario_Asignado] ?? null;

                $idUsuario = (int) $r->id_Usuario_Asignado;
                $login = $u?->login ?? '';
                $nombre = $u ? trim($u->nombre . ' ' . $u->paterno . ' ' . $u->materno) : '';
                $total = (int) $r->total;

                fputcsv($out, [$idUsuario, $login, $nombre, $total]);
            }

            fclose($out);
        }, 200, $headers);
    }
}