<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Émettre un vrai JWT
        $token = auth('api')->login($user);

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ], 201);
    }

    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        if (! $token = auth('api')->attempt($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Identifiants invalides'], 401);
        }

        $u = auth('api')->user();

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email],
        ]);
    }

    public function me()
    {
        return response()->json(['success' => true, 'user' => auth('api')->user()]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['success' => true, 'message' => 'Déconnecté']);
    }

    public function refresh()
    {
        return response()->json(['success' => true, 'token' => auth('api')->refresh()]);
    }
}
