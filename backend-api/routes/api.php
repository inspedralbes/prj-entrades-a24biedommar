<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\ReturnToController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoritController;
use Illuminate\Support\Facades\Route;

// ================================ RUTES API REST (prefix /api) ============

// ——— Rutes públiques ———
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ——— Sessió API (Sanctum Bearer) ———
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/usuari', [AuthController::class, 'usuari']);
});

// ——— Events (Ticketmaster) ———
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::post('/sync/events', [EventController::class, 'syncEvents']);

// ——— Return-to redirect ———
Route::post('/return-to/save', [ReturnToController::class, 'save']);
Route::get('/return-to/get', [ReturnToController::class, 'get']);
Route::post('/return-to/clear', [ReturnToController::class, 'clear']);

// ——— Rutes protegides ———
Route::middleware('auth:sanctum')->group(function () {
    // ——— Favorits ———
    Route::get('/favorites', [FavoritController::class, 'index']);
    Route::post('/favorites', [FavoritController::class, 'store']);
    Route::delete('/favorites/{eventId}', [FavoritController::class, 'destroy']);

    // ——— Comandes / entrades Ticketmaster (demo) ———
    Route::post('/comandes', [ComandaController::class, 'store']);
    Route::get('/meves-entrades', [ComandaController::class, 'mevesEntrades']);
});

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