# Pla de tasca S1.4 — Sistema de redirecció `return_to` (Backend Laravel)

## Identificació

| Camp | Valor |
|------|--------|
| **Tasca** | S1.4 — Implementar sistema de redirecció `return_to` ([UserTasks.md](../UserTasks.md)) |
| **Projecte** | TR3DAW — Plataforma de venda d'entrades |
| **Agents aplicats** | [AgentLaravel.md](../../Agents/backend/AgentLaravel.md), [AgentPosgres.md](../../Agents/backend/AgentPosgres.md), [AgentNuxt.md](../../Agents/frontend/AgentNuxt.md) (contracte) |
| **Font de veritat BD** | [db/init.sql](../../db/init.sql) (sense canvis d'esquema) |
| **Data d'implementació** | Març 2026 |
| **Branca** | `Tasca4-ReturnToRedirect` |

## Resum executiu

S'afegeix suport backend per `return_to` al flux de login de Laravel API. El camp `return_to` entra per `POST /api/login`, es valida com a camp opcional i es resol de forma segura via `ReturnToResolver`, evitant open-redirect. La resposta de login incorpora `return_to_resolta` perquè el frontend pugui navegar al destí final validat. Quan el valor és invàlid o no arriba, es retorna fallback `/`.

## Fitxers creats

| Ruta |
|------|
| [backend-api/app/Support/ReturnToResolver.php](../../backend-api/app/Support/ReturnToResolver.php) |
| [tests-e2e/return-to.spec.js](../../tests-e2e/return-to.spec.js) |

## Fitxers modificats

| Ruta | Canvi |
|------|--------|
| [backend-api/app/Http/Requests/LoginUsuariRequest.php](../../backend-api/app/Http/Requests/LoginUsuariRequest.php) | Validació del camp opcional `return_to` |
| [backend-api/app/Http/Controllers/Api/AuthController.php](../../backend-api/app/Http/Controllers/Api/AuthController.php) | Injecció de `ReturnToResolver` i resposta login amb `return_to_resolta` |
| [backend-api/routes/api.php](../../backend-api/routes/api.php) | Normalització sintàctica de rutes (sense canviar endpoints) |
| [tests-e2e/README.md](../../tests-e2e/README.md) | Secció específica de proves `return_to` |

## Contracte API

- **Endpoint:** `POST /api/login`
- **Entrada:** `correu_electronic`, `contrasenya`, `return_to` (opcional)
- **Sortida 200:** `missatge`, `token`, `usuari`, `return_to_resolta`
- **Sortida 401:** `missatge` (credencials incorrectes), sense `return_to_resolta`

### Regles de seguretat de `return_to`

- S'accepten només rutes internes que comencin amb `/`.
- Es bloquegen URL externes (`https://...`) i protocol-relative (`//domini...`).
- Es bloquegen valors potencialment perillosos (`/javascript:...`).
- Fallback segur: `/`.

## Tipologia de codi aplicada

- PHP 8.2+ (Laravel 11 al repositori actual).
- Comentaris en català.
- Sense operadors ternaris; flux explícit amb `if/else`.
- Validació en `FormRequest`.
- Lògica de sanejament encapsulada a `Support/ReturnToResolver` (responsabilitat única).

## Proves manuals (curl)

```bash
# login amb return_to intern
curl -s -X POST http://127.0.0.1:8777/api/login \
  -H "Content-Type: application/json" \
  -d "{\"correu_electronic\":\"prova@example.com\",\"contrasenya\":\"password12\",\"return_to\":\"/events/42\"}"

# login amb return_to extern (ha de retornar fallback)
curl -s -X POST http://127.0.0.1:8777/api/login \
  -H "Content-Type: application/json" \
  -d "{\"correu_electronic\":\"prova@example.com\",\"contrasenya\":\"password12\",\"return_to\":\"https://evil.example\"}"
```

## Proves E2E (Playwright)

Fitxer: [tests-e2e/return-to.spec.js](../../tests-e2e/return-to.spec.js)

Casos coberts:

1. `return_to` intern vàlid -> es manté.
2. `return_to` extern -> fallback `/`.
3. `return_to` malformat (`//evil.com`) -> fallback `/`.
4. login sense `return_to` -> fallback `/`.
5. login amb credencials incorrectes -> 401 sense `return_to_resolta`.

## Decisions i riscos

| Tema | Decisió |
|------|---------|
| Open redirect | Mitigat centralitzant la validació a `ReturnToResolver` |
| Compatibilitat frontend | Login manté camps existents i només afegeix `return_to_resolta` |
| Esquema SQL | Sense migracions ni canvis a `db/init.sql` (AgentPosgres) |
| README global Plans | Fora d'abast per petició explícita de l'usuari |

## Pendents / següents passos

- S1.5 — Integrar `return_to_resolta` al store Pinia i navegació Nuxt.
- S1.6 — Login/Registre frontend: persistir destí i aplicar redirecció final.
- Opcional — afegir tests unitaris PHP per `ReturnToResolver`.
