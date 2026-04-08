<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ReturnToController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoritController;
use App\Http\Controllers\QueueController;
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

// ——— Return-to redirect ———
Route::post('/return-to/save', [ReturnToController::class, 'save']);
Route::get('/return-to/get', [ReturnToController::class, 'get']);
Route::post('/return-to/clear', [ReturnToController::class, 'clear']);

// ——— Cua Virtual (The Gatekeeper) ———
Route::get('/queue/status/{eventId}', [QueueController::class, 'status']);
Route::get('/queue/threshold/{eventId}', [QueueController::class, 'getThreshold']);

// Rutes protegides per a la cua
Route::middleware('auth:sanctum')->group(function () {
    // ——— Notificacions de cua (des de Laravel) ———
    Route::post('/queue/user-joined', [QueueController::class, 'userJoined']);
    Route::post('/queue/user-left', [QueueController::class, 'userLeft']);
});

// ——— Rutes d'administració de cua (Admin) ———
Route::middleware(['auth:sanctum', 'rol:admin'])->group(function () {
    Route::put('/queue/threshold/{eventId}', [QueueController::class, 'updateThreshold']);
    Route::post('/queue/event/{eventId}/start', [QueueController::class, 'startEvent']);
    Route::post('/queue/event/{eventId}/end', [QueueController::class, 'endEvent']);
    Route::post('/queue/panic', [QueueController::class, 'activatePanic']);
    Route::post('/queue/panic/deactivate', [QueueController::class, 'deactivatePanic']);
});

// ——— Rutes protegides ———
Route::middleware('auth:sanctum')->group(function () {
    // ——— Favorits ———
    Route::get('/favorites', [FavoritController::class, 'index']);
    Route::post('/favorites', [FavoritController::class, 'store']);
    Route::delete('/favorites/{eventId}', [FavoritController::class, 'destroy']);
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
