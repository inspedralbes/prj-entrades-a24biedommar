<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComandaRequest;
use App\Models\Comanda;
use App\Models\Usuari;
use App\Services\CompraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Comandes d’entrades (Ticketmaster, demo sense passarel·la).
 */
class ComandaController extends Controller
{
    public function __construct(
        private CompraService $compraService
    ) {}

    /**
     * Crea una comanda amb entrades generals TM.
     */
    public function store(StoreComandaRequest $request): JsonResponse
    {
        /** @var Usuari $usuari */
        $usuari = $request->user();
        $dades = $request->validated();

        try {
            $comanda = $this->compraService->crearCompraTicketmaster(
                $usuari,
                $dades['tm_event_id'],
                (int) $dades['quantitat']
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'missatge' => $e->getMessage(),
            ], 422);
        }

        $tiquetsOut = [];
        foreach ($comanda->tiquets as $t) {
            $compratEl = null;
            if ($t->comprat_el !== null) {
                $compratEl = $t->comprat_el->toIso8601String();
            }
            $tiquetsOut[] = [
                'id' => $t->id,
                'hash_qr' => $t->hash_qr,
                'comprat_el' => $compratEl,
            ];
        }

        $creatElComanda = null;
        if ($comanda->creat_el !== null) {
            $creatElComanda = $comanda->creat_el->toIso8601String();
        }

        return response()->json([
            'missatge' => 'Comanda registrada correctament.',
            'comanda' => [
                'id' => $comanda->id,
                'import_total' => $comanda->import_total,
                'estat' => $comanda->estat,
                'tm_event_id' => $comanda->tm_event_id,
                'detall_event_json' => $comanda->detall_event_json,
                'creat_el' => $creatElComanda,
                'tiquets' => $tiquetsOut,
            ],
        ], 201);
    }

    /**
     * Entrades Ticketmaster de l’usuari autenticat, agrupades per esdeveniment.
     */
    public function mevesEntrades(Request $request): JsonResponse
    {
        /** @var Usuari $usuari */
        $usuari = $request->user();

        $comandes = Comanda::query()
            ->where('usuari_id', $usuari->id)
            ->whereNotNull('tm_event_id')
            ->with('tiquets')
            ->orderByDesc('creat_el')
            ->get();

        $perTm = [];
        foreach ($comandes as $c) {
            $tmId = $c->tm_event_id;
            if ($tmId === null) {
                continue;
            }
            if (! array_key_exists($tmId, $perTm)) {
                $perTm[$tmId] = [
                    'tm_event_id' => $tmId,
                    'detall_event_json' => $c->detall_event_json,
                    'entrades_total' => 0,
                    'comandes' => [],
                ];
            }

            $filaTiquets = [];
            foreach ($c->tiquets as $t) {
                $compratEl = null;
                if ($t->comprat_el !== null) {
                    $compratEl = $t->comprat_el->toIso8601String();
                }
                $filaTiquets[] = [
                    'id' => $t->id,
                    'hash_qr' => $t->hash_qr,
                    'comprat_el' => $compratEl,
                ];
                $perTm[$tmId]['entrades_total']++;
            }

            $creatEl = null;
            if ($c->creat_el !== null) {
                $creatEl = $c->creat_el->toIso8601String();
            }

            $perTm[$tmId]['comandes'][] = [
                'id' => $c->id,
                'import_total' => $c->import_total,
                'estat' => $c->estat,
                'creat_el' => $creatEl,
                'tiquets' => $filaTiquets,
            ];
        }

        $agrupat = [];
        foreach ($perTm as $fila) {
            $agrupat[] = $fila;
        }

        return response()->json([
            'esdeveniments' => $agrupat,
        ]);
    }
}
