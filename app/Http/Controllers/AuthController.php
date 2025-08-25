<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Données invalides',
    //             'errors' => $validator->errors()
    //         ], 400);
    //     }

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'balance' => 10000.00,
    //     ]);

    //     try {
    //         $token = JWTAuth::fromUser($user);
    //     } catch (JWTException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur lors de la création du token'
    //         ], 500);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Inscription réussie',
    //         'user' => $user,
    //         'token' => $token,
    //     ], 201);
    // }
public function register(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'balance' => 10000.00,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie',
            'user' => $user,
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du token'
            ], 500);
        }

        $user = auth()->user();

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh();
            return response()->json([
                'success' => true,
                'token' => $token
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de rafraîchir le token'
            ], 401);
        }
    }
}