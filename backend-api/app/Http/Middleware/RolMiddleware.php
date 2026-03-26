<?php

//================================ NAMESPACES / IMPORTS ============
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de comprovació de rol d'usuari.
 *
 * Verifica que l'usuari autenticat tingui el rol requerit per
 * accedir a la ruta. Ha d'executar-se DESPRÉS de auth:sanctum.
 */
class RolMiddleware
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Gestiona la petició entrant.
     *
     * @param  Request  $request  Petició HTTP actual.
     * @param  Closure  $next     Següent handler de la cadena.
     * @param  string   $rol      Rol requerit ('admin' o 'client').
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $rol): Response
    {
        // A. Validació de la sessió i permisos de l'usuari.
        $usuari = $request->user();

        if ($usuari === null) {
            return response()->json([
                'missatge' => 'No autenticat.',
            ], 401);
        }

        if ($usuari->rol !== $rol) {
            return response()->json([
                'missatge' => 'Accés denegat. No tens el rol necessari per accedir a aquest recurs.',
                'rol_requerit' => $rol,
                'rol_actual' => $usuari->rol,
            ], 403);
        }

        // B. Consulta o modificació de la base de dades SQL.
        // (cap acció directa a BD en aquest middleware)

        // C. Notificació a Redis (si cal) i retorn de la resposta JSON.
        return $next($request);
    }
}