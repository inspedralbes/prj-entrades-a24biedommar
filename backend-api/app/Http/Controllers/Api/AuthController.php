<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUsuariRequest;
use App\Http\Requests\RegisterUsuariRequest;
use App\Http\Resources\UsuariResource;
use App\Models\Usuari;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// ================================ CLASSE ============

/**
 * Controlador d’autenticació API (Sanctum): registre, login, logout i perfil.
 */
class AuthController extends Controller
{
    // ================================ MÈTODES PÚBLICS ============

    /**
     * Registre d’un nou usuari (rol client per defecte).
     */
    public function register(RegisterUsuariRequest $request): JsonResponse
    {
        // A. Validació aplicada a la FormRequest.
        $dades = $request->validated();

        // B. Persistència a PostgreSQL (contrasenya amb cast hashed al model).
        $usuari = new Usuari;
        $usuari->nom = $dades['nom'];
        $usuari->correu_electronic = $dades['correu_electronic'];
        $usuari->contrasenya = $dades['contrasenya'];
        $usuari->rol = 'client';
        $usuari->save();

        // C. Token Sanctum i resposta JSON.
        $nomToken = 'api-auth';
        $token = $usuari->createToken($nomToken)->plainTextToken;

        return response()->json([
            'missatge' => 'Registre correcte.',
            'token' => $token,
            'usuari' => new UsuariResource($usuari),
        ], 201);
    }

    /**
     * Login amb correu i contrasenya; retorna token Bearer.
     */
    public function login(LoginUsuariRequest $request): JsonResponse
    {
        // A. Validació aplicada a la FormRequest.
        $dades = $request->validated();

        // B. Cerca d’usuari i verificació de contrasenya (sense sessió web; API només Bearer).
        $usuari = Usuari::query()
            ->where('correu_electronic', $dades['correu_electronic'])
            ->first();

        if ($usuari === null) {
            return response()->json([
                'missatge' => 'Credencials incorrectes.',
            ], 401);
        }

        if (! Hash::check($dades['contrasenya'], $usuari->getAuthPassword())) {
            return response()->json([
                'missatge' => 'Credencials incorrectes.',
            ], 401);
        }

        // C. Token Sanctum.
        $nomToken = 'api-auth';
        $token = $usuari->createToken($nomToken)->plainTextToken;

        return response()->json([
            'missatge' => 'Sessió iniciada.',
            'token' => $token,
            'usuari' => new UsuariResource($usuari),
        ], 200);
    }

    /**
     * Tanca la sessió API (revoca el token actual).
     */
    public function logout(Request $request): JsonResponse
    {
        // A. Usuari autenticat via Sanctum (middleware auth:sanctum).
        $usuari = $request->user();
        if ($usuari === null) {
            return response()->json([
                'missatge' => 'No autenticat.',
            ], 401);
        }

        // B. Revocació del token d’accés actual.
        $token = $usuari->currentAccessToken();
        if ($token !== null) {
            $token->delete();
        }

        return response()->json([
            'missatge' => 'Sessió tancada.',
        ], 200);
    }

    /**
     * Retorna el perfil de l’usuari autenticat (Bearer).
     */
    public function usuari(Request $request): UsuariResource
    {
        /** @var Usuari $usuari */
        $usuari = $request->user();

        return new UsuariResource($usuari);
    }
}
