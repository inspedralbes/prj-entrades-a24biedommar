# Pla de tasca S1.2 — Controlador d’autenticació API (Sanctum)

## Identificació

| Camp | Valor |
|------|--------|
| **Tasca** | S1.2 — Implementar Controlador d’Autenticació (Register, Login, Logout) ([UserTasks.md](../UserTasks.md)) |
| **Projecte** | TR3DAW — Plataforma de venda d’entrades |
| **Agents aplicats** | [AgentLaravel.md](../../Agents/backend/AgentLaravel.md), [AgentPosgres.md](../../Agents/backend/AgentPosgres.md) |
| **Font de veritat BD** | [db/init.sql](../../db/init.sql), [db/insert.sql](../../db/insert.sql) |
| **Data d’implementació** | Març 2026 |

## Resum executiu

S’ha afegit **Laravel Sanctum** per emetre **tokens d’accés personal** (Bearer) per al model `Usuari`. L’API REST exposa `POST /api/register`, `POST /api/login`, `POST /api/logout` (protegit) i `GET /api/usuari` (perfil, protegit). Les contrasenyes es guarden amb el cast `hashed` del model; el registre assigna **rol `client`** per defecte. La taula `personal_access_tokens` s’ha definit a **`db/init.sql`** (sense migració d’esquema de negoci, coherent amb AgentPosgres). Els missatges de validació són en **català**. **Nota:** el document de tasques menciona «JWT via Sanctum»; Sanctum usa tokens opacs persistits a BD, no JWT; el client envia `Authorization: Bearer {token}` igualment.

## Fitxers creats

| Ruta |
|------|
| [backend-api/routes/api.php](../../backend-api/routes/api.php) |
| [backend-api/app/Http/Controllers/Api/AuthController.php](../../backend-api/app/Http/Controllers/Api/AuthController.php) |
| [backend-api/app/Http/Requests/RegisterUsuariRequest.php](../../backend-api/app/Http/Requests/RegisterUsuariRequest.php) |
| [backend-api/app/Http/Requests/LoginUsuariRequest.php](../../backend-api/app/Http/Requests/LoginUsuariRequest.php) |
| [backend-api/app/Http/Resources/UsuariResource.php](../../backend-api/app/Http/Resources/UsuariResource.php) |
| [backend-api/config/sanctum.php](../../backend-api/config/sanctum.php) (publicat des del paquet) |
| [backend-api/config/cors.php](../../backend-api/config/cors.php) (publicat) |

## Fitxers modificats

| Ruta | Canvi |
|------|--------|
| [backend-api/composer.json](../../backend-api/composer.json) | Dependència `laravel/sanctum` |
| [backend-api/bootstrap/app.php](../../backend-api/bootstrap/app.php) | Registre de `routes/api.php` (prefix `/api`) |
| [backend-api/config/auth.php](../../backend-api/config/auth.php) | Guard `sanctum` |
| [backend-api/app/Models/Usuari.php](../../backend-api/app/Models/Usuari.php) | Trait `HasApiTokens` |
| [db/init.sql](../../db/init.sql) | `DROP`/`CREATE` + índexs `personal_access_tokens` |
| [backend-api/.env.example](../../backend-api/.env.example) | `SANCTUM_STATEFUL_DOMAINS`, `FRONTEND_URL` |

## Rutes API

| Mètode | Ruta | Autenticació |
|--------|------|--------------|
| POST | `/api/register` | Pública |
| POST | `/api/login` | Pública |
| POST | `/api/logout` | `auth:sanctum` (Bearer) |
| GET | `/api/usuari` | `auth:sanctum` (Bearer) |

## Cos del JSON (resum)

- **Register:** `nom`, `correu_electronic`, `contrasenya`, `contrasenya_confirmation` (camp `confirmed` de Laravel).
- **Login:** `correu_electronic`, `contrasenya`.
- **Resposta correcta (register/login):** `missatge`, `token` (plain text, només en aquesta resposta), `usuari` (recurs sense contrasenya).

## Configuració

- **`.env`:** `DB_*` PostgreSQL; `AUTH_MODEL=App\Models\Usuari`; opcional `SANCTUM_STATEFUL_DOMAINS` i `FRONTEND_URL` per al Nuxt (veig [backend-api/.env.example](../../backend-api/.env.example)).
- **CORS:** [backend-api/config/cors.php](../../backend-api/config/cors.php) inclou `api/*` i `allowed_origins` `*` per desenvolupament (ajustar en producció).

## Proves manuals (curl)

Després d’aplicar `init.sql` + `insert.sql` i arrencar l’API (`php artisan serve` o Docker):

```bash
curl -s -X POST http://127.0.0.1:8000/api/register -H "Content-Type: application/json" -d "{\"nom\":\"Prova\",\"correu_electronic\":\"prova@example.com\",\"contrasenya\":\"password12\",\"contrasenya_confirmation\":\"password12\"}"
curl -s -X POST http://127.0.0.1:8000/api/login -H "Content-Type: application/json" -d "{\"correu_electronic\":\"prova@example.com\",\"contrasenya\":\"password12\"}"
curl -s http://127.0.0.1:8000/api/usuari -H "Authorization: Bearer TOKEN"
curl -s -X POST http://127.0.0.1:8000/api/logout -H "Authorization: Bearer TOKEN"
```

## Proves E2E (Playwright)

Al repositori: [tests-e2e/auth-api.spec.js](../../tests-e2e/auth-api.spec.js) — registre, login, perfil Bearer, logout i validacions 401/422. Requisits i `API_BASE_URL`: [tests-e2e/README.md](../../tests-e2e/README.md).

## Decisions i riscos

| Tema | Decisió |
|------|---------|
| Migracions vs SQL | No s’executa migració de `personal_access_tokens`; DDL a `init.sql`. S’ha eliminat la migració publicada accidentalment per evitar duplicar l’esquema. |
| Sessió web | Login API usa `Hash::check` + `createToken`; no es depèn de sessió web per al flux Bearer. |
| S1.3 (middleware rols) | Fora d’abast; només `auth:sanctum` als endpoints protegits. |

## Pendents / següents passos

- S1.3 — Middleware de protecció de rutes (rol admin/client).
- Frontend S1.5–S1.6 — consum dels endpoints des de Nuxt + Pinia.
