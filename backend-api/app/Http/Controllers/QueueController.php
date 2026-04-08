<?php

//================================ NAMESPACES / IMPORTS ============

namespace App\Http\Controllers;

use App\Services\QueueEventService;
use App\Models\Esdeveniment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

/**
 * Controlador per gestionar la cua virtual (The Gatekeeper).
 * A. Endpoints per unir-se/sortir de la cua (des de Laravel o delegant a Node.js).
 * B. Consulta de l'estat de la cua d'un event.
 * C. Gestió del threshold N.
 */
class QueueController extends Controller
{
    private QueueEventService $queueEventService;

    public function __construct(QueueEventService $queueEventService)
    {
        $this->queueEventService = $queueEventService;
    }

    /**
     * Obté l'estat de la cua per a un event.
     * A. Rep l'ID de l'event.
     * B. Consulta el threshold N des de la taula events (o valor per defecte).
     * C. Retorna l'estat actual.
     * 
     * Nota: El estat en temps real (nombre d'usuaris, posicions) es gestiona
     * directament a Node.js via WebSocket. Aquest endpoint retorna
     * informació estàtica/configurable.
     */
    public function status(string $eventId): JsonResponse
    {
        $esdeveniment = Esdeveniment::find($eventId);

        if ($esdeveniment === null) {
            return response()->json([
                'missatge' => 'Esdeveniment no trobat.',
            ], 404);
        }

        $thresholdN = $esdeveniment->llindar_n ?? 100;

        return response()->json([
            'event_id' => $eventId,
            'threshold_n' => $thresholdN,
            'info' => 'Per a informació en temps real, utilitza la connexió WebSocket.',
        ], 200);
    }

    /**
     * Actualitza el threshold N per a un event.
     * A. Rep l'ID de l'event i el nou valor del threshold.
     * B. Actualitza la base de dades.
     * C. Notifica a Node.js via Redis.
     * 
     * Requisit: Admin només (s'hauria de validar amb middleware).
     */
    public function updateThreshold(Request $request, string $eventId): JsonResponse
    {
        $thresholdN = $request->input('threshold_n');

        if ($thresholdN === null || !is_numeric($thresholdN) || $thresholdN < 1) {
            return response()->json([
                'missatge' => 'El threshold N ha de ser un número positiu.',
            ], 400);
        }

        $esdeveniment = Esdeveniment::find($eventId);

        if ($esdeveniment === null) {
            return response()->json([
                'missatge' => 'Esdeveniment no trobat.',
            ], 404);
        }

        $esdeveniment->llindar_n = (int) $thresholdN;
        $esdeveniment->save();

        $this->queueEventService->thresholdActualitzat($eventId, (int) $thresholdN);

        return response()->json([
            'missatge' => 'Threshold actualitzat correctament.',
            'event_id' => $eventId,
            'threshold_n' => $thresholdN,
        ], 200);
    }

    /**
     * Notifica que un usuari s'ha unit a la cua.
     * A. Rep ID de l'usuari i ID de l'event.
     * B. Publica l'esdeveniment a Redis.
     */
    public function userJoined(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $eventId = $request->input('event_id');

        if (!$userId || !$eventId) {
            return response()->json([
                'missatge' => 'user_id i event_id són obligatoris.',
            ], 400);
        }

        $this->queueEventService->usuariUnit($userId, $eventId);

        return response()->json([
            'missatge' => 'Usuari unit a la cua notificat.',
        ], 200);
    }

    /**
     * Notifica que un usuari ha sortit de la cua.
     * A. Rep ID de l'usuari i ID de l'event.
     * B. Publica l'esdeveniment a Redis.
     */
    public function userLeft(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $eventId = $request->input('event_id');

        if (!$userId || !$eventId) {
            return response()->json([
                'missatge' => 'user_id i event_id són obligatoris.',
            ], 400);
        }

        $this->queueEventService->usuariSortit($userId, $eventId);

        return response()->json([
            'missatge' => 'Usuari sortit de la cua notificat.',
        ], 200);
    }

    /**
     * Obté el threshold N per a un event.
     * A. Rep l'ID de l'event.
     * B. Retorna el valor del threshold.
     * 
     * Utilitzat per Node.js per saber el límit de la cua.
     */
    public function getThreshold(string $eventId): JsonResponse
    {
        $esdeveniment = Esdeveniment::find($eventId);

        if ($esdeveniment === null) {
            return response()->json([
                'threshold_n' => 100,
            ], 200);
        }

        return response()->json([
            'event_id' => $eventId,
            'threshold_n' => $esdeveniment->llindar_n ?? 100,
        ], 200);
    }

    /**
     * Inicia un event (prepara la cua).
     * A. Rep l'ID de l'event.
     * B. Notifica a Node.js per inicialitzar la cua.
     */
    public function startEvent(string $eventId): JsonResponse
    {
        $this->queueEventService->eventIniciat($eventId);

        return response()->json([
            'missatge' => 'Event iniciat, cua preparada.',
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * Acaba un event (tanca la cua).
     * A. Rep l'ID de l'event.
     * B. Notifica a Node.js per netejar la cua.
     */
    public function endEvent(string $eventId): JsonResponse
    {
        $this->queueEventService->eventAcabat($eventId);

        return response()->json([
            'missatge' => 'Event acabat, cua tancada.',
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * Activa el mode pànic (atura totes les interaccions).
     * Requisit: Admin només.
     */
    public function activatePanic(Request $request): JsonResponse
    {
        $missatge = $request->input('missatge', 'Sistema aturat per administrador');
        $this->queueEventService->activarPanic($missatge);

        return response()->json([
            'missatge' => 'Mode pànic activat.',
        ], 200);
    }

    /**
     * Desactiva el mode pànic.
     * Requisit: Admin només.
     */
    public function deactivatePanic(): JsonResponse
    {
        $this->queueEventService->desactivarPanic();

        return response()->json([
            'missatge' => 'Mode pànic desactivat.',
        ], 200);
    }
}