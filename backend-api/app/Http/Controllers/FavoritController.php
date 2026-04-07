<?php

namespace App\Http\Controllers;

use App\Models\Favorit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritController extends Controller
{
    /**
     * Llistar favorits de l'usuari autenticat.
     */
    public function index(): JsonResponse
    {
        $usuari = Auth::user();
        $favorits = Favorit::where('usuari_id', $usuari->id)
            ->orderBy('creat_el', 'desc')
            ->get();

        return response()->json($favorits);
    }

    /**
     * Afegir un event a favorits.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required|string',
            'event_nom' => 'required|string',
            'event_data' => 'nullable|string',
            'event_imatge' => 'nullable|string',
        ]);

        $usuari = Auth::user();

        $favorit = Favorit::updateOrCreate(
            [
                'usuari_id' => $usuari->id,
                'event_id' => $request->event_id,
            ],
            [
                'event_nom' => $request->event_nom,
                'event_data' => $request->event_data,
                'event_imatge' => $request->event_imatge,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Event afegit a favorits',
            'favorit' => $favorit,
        ], 201);
    }

    /**
     * Eliminar un event de favorits.
     */
    public function destroy(string $eventId): JsonResponse
    {
        $usuari = Auth::user();

        $deleted = Favorit::where('usuari_id', $usuari->id)
            ->where('event_id', $eventId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'status' => 'success',
                'message' => 'Event eliminat de favorits',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No s\'ha trobat el favorit',
        ], 404);
    }
}
