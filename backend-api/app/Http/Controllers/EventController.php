<?php

//================================ NAMESPACES / IMPORTS ============

namespace App\Http\Controllers;

use App\Services\TicketmasterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

/**
 * Controlador per gestionar esdeveniments des de Ticketmaster API.
 */
class EventController extends Controller
{
    // A. Propietats del controlador
    private TicketmasterService $ticketmasterService;

    // B. Constructor amb injecció de dependències
    public function __construct(TicketmasterService $ticketmasterService)
    {
        $this->ticketmasterService = $ticketmasterService;
    }

    /**
     * Obté el llistat d'esdeveniments.
     * A. Valida els paràmetres de cerca (lat, lng, radi).
     * B. Crida el servei de Ticketmaster.
     * C. Retorna la llista d'esdeveniments.
     */
    public function index(Request $request): JsonResponse
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $radius = $request->input('radius', 50);

        $events = $this->ticketmasterService->getEvents($lat, $lng, $radius);

        return response()->json([
            'esdeveniments' => $events,
        ], 200);
    }

    /**
     * Obté el detall d'un esdeveniment específic.
     * A. Valida l'ID de l'esdeveniment.
     * B. Crida el servei de Ticketmaster per obtenir el detall.
     * C. Retorna les dades de l'esdeveniment.
     */
    public function show(string $id): JsonResponse
    {
        $event = $this->ticketmasterService->getEventById($id);

        if ($event === null) {
            return response()->json([
                'missatge' => 'Esdeveniment no trobat.',
            ], 404);
        }

        return response()->json([
            'esdeveniment' => $event,
        ], 200);
    }
}