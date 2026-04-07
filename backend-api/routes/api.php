<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ReturnToController;
use Illuminate\Support\Facades\Route;

// ================================ RUTES API REST (prefix /api) ============

// ——— Rutes públiques ———
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ——— Return-to redirect ———
Route::post('/return-to/save', [ReturnToController::class, 'save']);
Route::get('/return-to/get', [ReturnToController::class, 'get']);
Route::post('/return-to/clear', [ReturnToController::class, 'clear']);

// ——— Rutes protegides ———

// ——— Rutes exclusives per a administradors ———
Route::middleware(['auth:sanctum', 'rol:admin'])->group(function () {
    Route::get('/admin/estat', function () {
        return response()->json(['missatge' => "Benvingut al panell d'administrador."]);
    });
});

// ——— Rutes exclusives per a clients ———
Route::middleware(['auth:sanctum', 'rol:client'])->group(function () {
    Route::get('/client/perfil-extens', function () {
        return response()->json(['missatge' => 'Àrea exclusiva de client.']);
    });
});
