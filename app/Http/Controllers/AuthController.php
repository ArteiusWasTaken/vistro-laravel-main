<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function auth_register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function auth_login(Request $request)
    {
        try {
            $jsonData = json_decode($request->input('data'), true);
            $credentials = [
                'email' => $jsonData['email'] ?? '',
                'password' => $jsonData['password'] ?? '',
            ];
            if (!$token = Auth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }

            $cookie = cookie(
                'token',
                $token,
                60 * 24,
                null,
                null,
                true,
                true,
                false,
                'Strict'
            );

            return response()->json(['message' => 'Login exitoso'])->withCookie($cookie);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function auth_logout()
    {
        $cookie = Cookie::forget('token');

        return response()->json(['message' => 'Logout exitoso'])->withCookie($cookie);
    }

    public function auth_me()
    {
        return response()->json(Auth::user());
    }

    public function auth_refresh()
    {
        try {
            $newToken = Auth::refresh();

            $cookie = cookie('token', $newToken, 60 * 24, null, null, true, true, false, 'Strict');

            return response()->json(['message' => 'Token renovado'])->withCookie($cookie);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo renovar el token'], 401);
        }
    }

    public function auth_check(Request $request): JsonResponse
    {
        $user = $request->auth;

        return response()->json([
            'message' => 'Token válido'
        ]);
    }
}
