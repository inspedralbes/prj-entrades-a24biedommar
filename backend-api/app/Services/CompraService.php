<?php

namespace App\Services;

use App\Models\Comanda;
use App\Models\Tiquet;
use App\Models\Usuari;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Creació de comandes demo (sense passarel·la) per entrades Ticketmaster generals.
 */
class CompraService
{
    public const MAX_ENTRADES = 6;

    public function __construct(
        private TicketmasterService $ticketmasterService
    ) {}

    /**
     * Crea comanda completada i N tiquets sense seient local.
     *
     * @throws InvalidArgumentException missatge per respondre 422
     */
    public function crearCompraTicketmaster(Usuari $usuari, string $tmEventId, int $quantitat): Comanda
    {
        if ($quantitat < 1 || $quantitat > self::MAX_ENTRADES) {
            throw new InvalidArgumentException('La quantitat ha d\'estar entre 1 i '.self::MAX_ENTRADES.'.');
        }

        $detall = $this->ticketmasterService->getEventById($tmEventId);

        if ($detall === null) {
            throw new InvalidArgumentException('Esdeveniment no trobat o no disponible.');
        }

        if (empty($detall['pot_comprar'])) {
            throw new InvalidArgumentException('Les entrades no estan disponibles per a la venda (esgotades o fora de venda).');
        }

        $preuUnitari = $this->ticketmasterService->preuUnitariPerCompra($detall);

        if ($preuUnitari === null) {
            throw new InvalidArgumentException('No s\'ha pogut determinar el preu de l\'esdeveniment.');
        }

        $importTotal = round($preuUnitari * $quantitat, 2);

        $u = [];
        if (isset($detall['ubicacio']) && is_array($detall['ubicacio'])) {
            $u = $detall['ubicacio'];
        }
        $imatge = null;
        if (isset($detall['imatge_gran']) && $detall['imatge_gran'] !== null && $detall['imatge_gran'] !== '') {
            $imatge = $detall['imatge_gran'];
        } elseif (isset($detall['imatge'])) {
            $imatge = $detall['imatge'];
        }

        $nom = '';
        if (isset($detall['nom'])) {
            $nom = $detall['nom'];
        }

        $data = null;
        if (isset($detall['data'])) {
            $data = $detall['data'];
        }

        $hora = null;
        if (isset($detall['hora'])) {
            $hora = $detall['hora'];
        }

        $recinte = null;
        if (isset($u['recinte'])) {
            $recinte = $u['recinte'];
        }

        $ciutat = null;
        if (isset($u['ciutat'])) {
            $ciutat = $u['ciutat'];
        }

        $snapshot = [
            'tm_event_id' => $tmEventId,
            'nom' => $nom,
            'data' => $data,
            'hora' => $hora,
            'imatge' => $imatge,
            'recinte' => $recinte,
            'ciutat' => $ciutat,
        ];

        return DB::transaction(function () use ($usuari, $tmEventId, $quantitat, $importTotal, $snapshot) {
            /** @var Comanda $comanda */
            $comanda = Comanda::query()->create([
                'usuari_id' => $usuari->id,
                'import_total' => $importTotal,
                'estat' => 'completada',
                'id_intencio_pagament' => null,
                'tm_event_id' => $tmEventId,
                'detall_event_json' => $snapshot,
            ]);

            for ($i = 0; $i < $quantitat; $i++) {
                $hash = hash('sha256', $comanda->id.':'.($i + 1).':'.Str::uuid()->toString().':'.config('app.key'));

                Tiquet::query()->create([
                    'comanda_id' => $comanda->id,
                    'seient_id' => null,
                    'hash_qr' => $hash,
                ]);
            }

            return $comanda->load('tiquets');
        });
    }
}
