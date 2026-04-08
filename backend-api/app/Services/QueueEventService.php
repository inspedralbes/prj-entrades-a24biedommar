<?php

//================================ NAMESPACES / IMPORTS ============

namespace App\Services;

use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

/**
 * Servei per publicar esdeveniments de cua a Redis.
 * A. Publica canvis d'estat de la cua (user-joined, user-left, threshold-updated).
 * B. Comunica l'estat de la cua al servidor Node.js (The Gatekeeper).
 * C. Format: canal "queue:{eventId}" per a events específics.
 */
class QueueEventService
{
    /**
     * Publica un missatge al canal de cua d'un event.
     * A. Rep el tipus d'event, ID de l'event i dades addicionals.
     * B. Construeix el missatge en format JSON.
     * C. Publica al canal Redis corresponent.
     */
    private function publicar(string $eventType, string $eventId, array $data = []): void
    {
        $missatge = [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        $canal = 'queue:' . $eventId;
        Redis::publish($canal, json_encode($missatge));
    }

    /**
     * Publica missatge al canal global d'updates de cua.
     * A. Utilitzat per a actualitzacions que afecten múltiples events.
     * B. Publica al canal "queue:updates".
     */
    private function publicarGlobal(string $eventType, string $eventId, array $data = []): void
    {
        $missatge = [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        Redis::publish('queue:updates', json_encode($missatge));
    }

    /**
     * Notifica que un usuari s'ha unit a la cua.
     * A. Rep ID de l'usuari i ID de l'event.
     * B. Publica l'esdeveniment al canal de l'event.
     */
    public function usuariUnit(string $userId, string $eventId): void
    {
        $this->publicar('user-joined', $eventId, [
            'user_id' => $userId,
        ]);

        $this->publicarGlobal('user-joined', $eventId, [
            'user_id' => $userId,
        ]);
    }

    /**
     * Notifica que un usuari ha sortit de la cua.
     * A. Rep ID de l'usuari i ID de l'event.
     * B. Publica l'esdeveniment al canal de l'event.
     */
    public function usuariSortit(string $userId, string $eventId): void
    {
        $this->publicar('user-left', $eventId, [
            'user_id' => $userId,
        ]);

        $this->publicarGlobal('user-left', $eventId, [
            'user_id' => $userId,
        ]);
    }

    /**
     * Notifica que el threshold N d'un event ha canviat.
     * A. Rep ID de l'event i el nou valor del threshold.
     * B. Publica l'actualització a tots els canals pertinents.
     */
    public function thresholdActualitzat(string $eventId, int $thresholdN): void
    {
        $this->publicar('threshold-updated', $eventId, [
            'threshold_n' => $thresholdN,
        ]);

        $this->publicarGlobal('threshold-updated', $eventId, [
            'threshold_n' => $thresholdN,
        ]);
    }

    /**
     * Notifica que un event ha iniciat (cua preparada).
     * A. Rep ID de l'event.
     * B. Publica l'esdeveniment per inicialitzar la cua a Node.js.
     */
    public function eventIniciat(string $eventId): void
    {
        $this->publicarGlobal('event-started', $eventId);
    }

    /**
     * Notifica que un event ha acabat (cua tancada).
     * A. Rep ID de l'event.
     * B. Publica l'esdeveniment per netejar la cua a Node.js.
     */
    public function eventAcabat(string $eventId): void
    {
        $this->publicarGlobal('event-ended', $eventId);
    }

    /**
     * Publica una comanda administrativa.
     * A. Utilitzat pel dashboard d'administració.
     * B. Publica al canal "admin:commands".
     */
    public function comandaAdministrativa(string $comanda, array $data = []): void
    {
        $missatge = [
            'command' => $comanda,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        Redis::publish('admin:commands', json_encode($missatge));
    }

    /**
     * Activa el mode pànic (atura totes les interaccions).
     * A. Notifica a tots els clients via Socket.IO.
     */
    public function activarPanic(string $missatge = ''): void
    {
        $this->comandaAdministrativa('panic', [
            'message' => $missatge,
        ]);
    }

    /**
     * Desactiva el mode pànic (reprèn les interaccions).
     */
    public function desactivarPanic(): void
    {
        $this->comandaAdministrativa('resume');
    }

    /**
     * Neteja la cua d'un event específic.
     * A. Notifica a Node.js per buidar la cua.
     */
    public function netejarCua(string $eventId): void
    {
        $this->comandaAdministrativa('clear-queue', [
            'event_id' => $eventId,
        ]);
    }
}