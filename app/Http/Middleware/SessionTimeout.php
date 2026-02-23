<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->header('X-User-Id');
        if (!$userId)
            return response()->json(['message' => 'No autenticado'], 401);

        $key = "last_activity_user_{$userId}";
        $last = Cache::get($key); // int timestamp

        $now = time();
        $ttl = 60; // en prod: 300

        // Si no hay registro en caché, es que ya expiró o no ha hecho login
        if (!$last) {
            return response()->json(['message' => 'Sesión expirada o no iniciada'], 401);
        }

        // Comprobación manual de tiempo por si el driver de caché tarda en limpiar
        if (($now - $last) >= $ttl) {
            Cache::forget($key);
            return response()->json(['message' => 'Sesión expirada por inactividad'], 401);
        }

        Cache::put($key, $now, $ttl);
        return $next($request);
    }
}