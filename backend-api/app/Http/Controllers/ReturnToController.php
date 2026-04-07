<?php

//================================ NAMESPACES / IMPORTS ============

namespace App\Http\Controllers;

//================================ PROPIETATS / ATRIBUTS ==========

//================================ MÈTODES / FUNCIONS ===========

class ReturnToController extends Controller
{
    // A. Guardar la URL de retorn a la sessió
    public function save()
    {
        $url = request()->input('url', '/');

        session(['return_to' => $url]);

        return response()->json([
            'success' => true,
            'message' => 'URL saved'
        ]);
    }

    // B. Obtenir la URL de retorn
    public function get()
    {
        $returnTo = session('return_to', '/');

        return response()->json([
            'return_to' => $returnTo
        ]);
    }

    // C. Esborrar la URL de retorn de la sessió
    public function clear()
    {
        session()->forget('return_to');

        return response()->json([
            'success' => true,
            'message' => 'URL cleared'
        ]);
    }
}