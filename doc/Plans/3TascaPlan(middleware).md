# Pla de tasca S1.3 — Middleware de Protecció de Rutes (Rols Admin/Client)

## Identificació

| Camp | Valor |
|------|--------|
| **Tasca** | S1.3 — Crear Middleware de Protecció de Rutes ([UserTasks.md](../UserTasks.md)) |
| **Projecte** | TR3DAW — Plataforma de venda d'entrades |
| **Agents aplicats** | [AgentLaravel.md](../../Agents/backend/AgentLaravel.md) |
| **Font de veritat BD** | [db/init.sql](../../db/init.sql) |
| **Data d'implementació** | Març 2026 |
| **Branca** | `Tasca3-Middleware` |

## Resum executiu

S'ha d'implementar un **Middleware de rol** per a Laravel 13 que complementi el guard `auth:sanctum` ja existent (S1.2). El middleware `RolMiddleware` intercepta les peticions autenticades i verifica que el camp `rol` de l'usuari autenticat (`Usuari::class`) sigui el rol requerit per accedir a la ruta. Si el rol no coincideix, retorna un JSON `403 Forbidden` amb el missatge en **català**. Les rutes protegides per rol s'agrupen amb `middleware(['auth:sanctum', 'rol:admin'])` o `middleware(['auth:sanctum', 'rol:client'])`. No s'utilitzen migracions (consistent amb AgentPosgres). La implementació segueix estrictament les convencions de l'`AgentLaravel`: sense ternaris, blocs `if/else`, comentaris per seccions en català.

---

## Fitxers nous a crear

| Ruta | Descripció |
|------|------------|
| `backend-api/app/Http/Middleware/RolMiddleware.php` | Middleware que comprova el rol de l'usuari autenticat |
| `tests-e2e/middleware-rol.spec.js` | Tests E2E Playwright contra les rutes protegides per rol |

---

## Fitxers a modificar

| Ruta | Canvi |
|------|-------|
| `backend-api/bootstrap/app.php` | Registrar l'àlies `rol` del middleware al `withMiddleware()` |
| `backend-api/routes/api.php` | Afegir grup de rutes per a `admin` i grup per a `client` (exemples d'endpoint protegit: `GET /api/admin/estat`) |

---

## Contingut dels fitxers

### `backend-api/app/Http/Middleware/RolMiddleware.php`

```php
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
                'rol_requerit'  => $rol,
                'rol_actual'    => $usuari->rol,
            ], 403);
        }

        // B. Consulta o modificació de la base de dades SQL.
        // (cap acció directa a BD en aquest middleware)

        // C. Notificació a Redis (si cal) i retorn de la resposta JSON.
        return $next($request);
    }
}
```

---

### Canvi a `backend-api/bootstrap/app.php`

Dins del bloc `->withMiddleware(function (Middleware $middleware) { ... })` afegir:

```php
$middleware->alias([
    'rol' => \App\Http\Middleware\RolMiddleware::class,
]);
```

---

### Canvi a `backend-api/routes/api.php`

Afegir dos grups de rutes protegides com a **exemple** (els endpoints reals s'ampliaran a S1.8, S2.1…):

```php
// ——— Rutes protegides: rol admin ———
Route::middleware(['auth:sanctum', 'rol:admin'])->group(function () {
    Route::get('/admin/estat', function () {
        return response()->json(['missatge' => 'Benvingut al panell d\'administrador.']);
    });
});

// ——— Rutes protegides: rol client ———
Route::middleware(['auth:sanctum', 'rol:client'])->group(function () {
    Route::get('/client/perfil-extens', function () {
        return response()->json(['missatge' => 'Àrea exclusiva de client.']);
    });
});
```

---

### `tests-e2e/middleware-rol.spec.js`

```js
const { test, expect } = require('@playwright/test');
const { assertLaravelApiJson } = require('./helpers/assertLaravelApi');

/**
 * E2E contra les rutes protegides per rol (RolMiddleware).
 * Requisit: backend escoltant a baseURL (vegeu playwright.config.js).
 */

test.describe('Middleware de rol — protecció de rutes', function () {

    var tokenAdmin = null;
    var tokenClient = null;

    test.beforeAll(async function ({ request }) {
        await assertLaravelApiJson(request);

        // Registrar un usuari client per a les proves
        var emailClient = 'e2e_client_' + Date.now() + '@test.local';
        var resClient = await request.post('/api/register', {
            data: {
                nom: 'Client E2E',
                correu_electronic: emailClient,
                contrasenya: 'password12',
                contrasenya_confirmation: 'password12',
            },
            headers: { 'Content-Type': 'application/json' },
        });
        var bodyClient = await resClient.json();
        tokenClient = bodyClient.token;

        // Login usuari admin (existent a insert.sql)
        var resAdmin = await request.post('/api/login', {
            data: {
                correu_electronic: 'admin@ticketmaster.cat',
                contrasenya: 'password',
            },
            headers: { 'Content-Type': 'application/json' },
        });
        if (resAdmin.status() === 200) {
            var bodyAdmin = await resAdmin.json();
            tokenAdmin = bodyAdmin.token;
        }
    });

    test('GET /api/admin/estat sense token retorna 401', async function ({ request }) {
        var res = await request.get('/api/admin/estat');
        expect(res.status()).toBe(401);
    });

    test('GET /api/admin/estat amb token de client retorna 403', async function ({ request }) {
        var res = await request.get('/api/admin/estat', {
            headers: { Authorization: 'Bearer ' + tokenClient },
        });
        expect(res.status()).toBe(403);
        var body = await res.json();
        expect(body.missatge).toBeDefined();
        expect(body.rol_requerit).toBe('admin');
    });

    test('GET /api/admin/estat amb token d\'admin retorna 200', async function ({ request }) {
        if (tokenAdmin === null) {
            test.skip();
            return;
        }
        var res = await request.get('/api/admin/estat', {
            headers: { Authorization: 'Bearer ' + tokenAdmin },
        });
        expect(res.status()).toBe(200);
    });

    test('GET /api/client/perfil-extens amb token de client retorna 200', async function ({ request }) {
        var res = await request.get('/api/client/perfil-extens', {
            headers: { Authorization: 'Bearer ' + tokenClient },
        });
        expect(res.status()).toBe(200);
    });

    test('GET /api/client/perfil-extens amb token d\'admin retorna 403', async function ({ request }) {
        if (tokenAdmin === null) {
            test.skip();
            return;
        }
        var res = await request.get('/api/client/perfil-extens', {
            headers: { Authorization: 'Bearer ' + tokenAdmin },
        });
        expect(res.status()).toBe(403);
        var body = await res.json();
        expect(body.rol_requerit).toBe('client');
    });
});
```

---

## Tasques pas a pas

| Pas | Acció | Fitxer afectat |
|-----|-------|----------------|
| 1 | Crear `RolMiddleware.php` amb la lògica de comprovació de rol | `app/Http/Middleware/RolMiddleware.php` [NOU] |
| 2 | Registrar l'àlies `'rol'` a `bootstrap/app.php` dins `withMiddleware()` | `bootstrap/app.php` [MODIFICA] |
| 3 | Afegir els grups de rutes d'exemple `admin` i `client` a `routes/api.php` | `routes/api.php` [MODIFICA] |
| 4 | Crear el fitxer de tests E2E `middleware-rol.spec.js` | `tests-e2e/middleware-rol.spec.js` [NOU] |
| 5 | Verificar que `insert.sql` té un usuari amb `rol = 'admin'` (per als tests) | `db/insert.sql` [REVISAR] |
| 6 | Executar els tests E2E: `npx playwright test middleware-rol.spec.js` | — |

---

## Tests E2E generats (`tests-e2e/`)

| Fitxer | Casos de prova |
|--------|----------------|
| `middleware-rol.spec.js` | 5 casos: 401 sense token, 403 client a ruta admin, 200 admin a ruta admin, 200 client a ruta client, 403 admin a ruta client |

---

## Agents aplicats

| Agent | Ús |
|-------|----|
| [AgentLaravel.md](../../Agents/backend/AgentLaravel.md) | Convencions de codi PHP (sense ternaris, comentaris en català, blocs `if/else`), estructura de controllers i middleware, regla GET/CUD |

---

## Tipologia de programació

- **Idioma de comentaris:** Català obligatori.
- **Estil:** Sense operadors ternaris (`? :`). Ús de `if/else` explícit.
- **Estructura de comentaris** (secció 5 d'`AgentLaravel.md`):
  - `//================================ NAMESPACES / IMPORTS ============`
  - `//================================ PROPIETATS / ATRIBUTS ==========`
  - `//================================ MÈTODES / FUNCIONS ===========`
  - `//================================ LÒGICA PRIVADA ================`
- **Dins de cada mètode:** seccions `// A.`, `// B.`, `// C.`
- **Sense migracions:** Cap canvi d'esquema (d'acord amb AgentPosgres).
- **Respostes JSON:** Sempre en format `{ "missatge": "..." }` + codi HTTP adequat.
- **Tests E2E:** `var` (ES5-like), `function()` clàssic, sense `const`/`let`/arrow functions per coherència amb les regles de backend ultra-tradicional.

---

## Decisions i riscos

| Tema | Decisió |
|------|---------|
| Ordre dels middlewares | `auth:sanctum` ha d'executar-se **primer** per garantir que `$request->user()` no sigui `null` quan el `RolMiddleware` s'executi. |
| Rutes d'exemple vs. reals | Els endpoints `/api/admin/estat` i `/api/client/perfil-extens` són **placeholders**; les rutes reals s'afegiràn a S1.8, S2.1, S2.12... usant els mateixos grups de middleware. |
| Usuari admin als tests | Depenent de que `insert.sql` tingui un usuari amb `rol = 'admin'` i credencials conegudes. Si no, el test d'admin es marca com `skip`. |
| `rol` vs. `role` | El camp a la BD és `rol` (en català, tal com definit a `init.sql` i al model `Usuari`). |

---

## Pendents / següents passos

- S1.4 — Sistema de redirecció `return_to` (Backend Laravel).
- S1.5 — Store d'Autenticació Pinia (Frontend Nuxt 4).
- S1.8 — Controlador d'Events: aplicar `middleware(['auth:sanctum', 'rol:admin'])` als endpoints d'administració.
