<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ================================ RUTES API REST (prefix /api) ============

Route::post('/register', [AuthController::class , 'register']);
Route::post('/login', [AuthController::class , 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class , 'logout']);
    Route::get('/usuari', [AuthController::class , 'usuari']);
});

// ================================ RUTES PROTEGIDES PER ROL ============

// ——— Rutes exclusives per a administradors ———
Route::middleware(['auth:sanctum', 'rol:admin'])->group(function () {
    Route::get('/admin/estat', function () {
            return response()->json(['missatge' => "Benvingut al panell d'administrador."]);
        }
        );
    });

// ——— Rutes exclusives per a clients ———
Route::middleware(['auth:sanctum', 'rol:client'])->group(function () {
    Route::get('/client/perfil-extens', function () {
            return response()->json(['missatge' => "Àrea exclusiva de client."]);
        }
        );
    });