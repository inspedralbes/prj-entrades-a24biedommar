<?php

namespace App\Http\Requests;

use App\Services\CompraService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validació POST /api/comandes (entrades Ticketmaster).
 */
class StoreComandaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tm_event_id' => ['required', 'string', 'max:120'],
            'quantitat' => ['required', 'integer', 'min:1', 'max:'.CompraService::MAX_ENTRADES],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tm_event_id.required' => 'L\'identificador d\'esdeveniment Ticketmaster és obligatori.',
            'quantitat.required' => 'La quantitat és obligatòria.',
            'quantitat.max' => 'Com a màxim :max entrades per comanda.',
        ];
    }
}
