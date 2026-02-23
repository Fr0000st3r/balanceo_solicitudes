<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        $usuario = Usuario::where('login', $request->login)
            ->where('activo', 1)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        Cache::put("last_activity_user_{$usuario->id_usuario}", time(), 60);

        return response()->json([
            'message' => 'Login correcto',
            'id_usuario' => $usuario->id_usuario
        ]);
    }
}