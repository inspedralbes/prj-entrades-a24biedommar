<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// ================================ CLASSE ============

/**
 * Validació del login (API).
 */
class LoginUsuariRequest extends FormRequest
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
     * Regles de validació.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'correu_electronic' => ['required', 'string', 'email:rfc', 'max:150'],
            'contrasenya' => ['required', 'string'],
            'return_to' => ['nullable', 'string', 'max:2048'],
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
            'correu_electronic.required' => 'El correu electrònic és obligatori.',
            'contrasenya.required' => 'La contrasenya és obligatòria.',
            'return_to.max' => 'La ruta de retorn és massa llarga.',
        ];
    }
}
