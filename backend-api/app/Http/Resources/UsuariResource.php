<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// ================================ CLASSE ============

/**
 * Serialització JSON de l’usuari autenticat (sense camps ocults al model).
 */
class UsuariResource extends JsonResource
{
    // ================================ MÈTODES PÚBLICS ============

    /**
     * Transforma el recurs en una matriu.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'correu_electronic' => $this->correu_electronic,
            'rol' => $this->rol,
            'creat_el' => $this->creat_el,
        ];
    }
}
