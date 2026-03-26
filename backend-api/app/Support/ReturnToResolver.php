<?php

namespace App\Support;

// ================================ CLASSE ============

/**
 * Resolutor de ruta de retorn després del login.
 *
 * Objectiu: evitar open-redirect i retornar sempre una ruta interna segura.
 */
class ReturnToResolver
{
    // ================================ PROPIETATS ============

    /**
     * Ruta fallback quan no arriba cap `return_to` o és invàlida.
     */
    private string $fallbackRuta = '/';

    // ================================ MÈTODES PÚBLICS ============

    /**
     * Resol i saneja la ruta de retorn.
     */
    public function resolve(?string $returnTo): string
    {
        // A. Cas buit o null: ruta fallback.
        if ($returnTo === null) {
            return $this->fallbackRuta;
        }

        $ruta = trim($returnTo);
        if ($ruta === '') {
            return $this->fallbackRuta;
        }

        // B. Validació de ruta interna.
        if (! $this->esRutaInternaValida($ruta)) {
            return $this->fallbackRuta;
        }

        // C. Ruta segura final.
        return $ruta;
    }

    // ================================ LÒGICA PRIVADA ============

    /**
     * Determina si la ruta és interna i segura.
     */
    private function esRutaInternaValida(string $ruta): bool
    {
        if (! str_starts_with($ruta, '/')) {
            return false;
        }

        // Evita protocol-relative URL (`//domini.com`).
        if (str_starts_with($ruta, '//')) {
            return false;
        }

        // Evita vectors típics de redirecció/script.
        if (str_contains($ruta, '://')) {
            return false;
        }

        $rutaLower = strtolower($ruta);
        if (str_starts_with($rutaLower, '/javascript:')) {
            return false;
        }

        return true;
    }
}
