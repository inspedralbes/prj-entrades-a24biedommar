<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// ================================ CLASSE ============

/**
 * Validació del registre d’usuari (API).
 */
class RegisterUsuariRequest extends FormRequest
{
    // ================================ MÈTODES PÚBLICS ============

    /**
     * Determina si l’usuari pot fer aquesta petició.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regles de validació (camps alineats amb taula `usuaris`).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'correu_electronic' => ['required', 'string', 'email:rfc', 'max:150', 'unique:usuaris,correu_electronic'],
            'contrasenya' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Missatges en català.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'El nom és obligatori.',
            'correu_electronic.required' => 'El correu electrònic és obligatori.',
            'correu_electronic.unique' => 'Aquest correu electrònic ja està registrat.',
            'contrasenya.required' => 'La contrasenya és obligatòria.',
            'contrasenya.min' => 'La contrasenya ha de tenir com a mínim 8 caràcters.',
            'contrasenya.confirmed' => 'La confirmació de contrasenya no coincideix.',
        ];
    }
}
