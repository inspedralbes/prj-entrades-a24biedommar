# Pla de tasca S1.1 — Models Laravel (connexió PostgreSQL)

## Identificació

| Camp | Valor |
|------|--------|
| **Tasca** | S1.1 — Configurar models Laravel (UserTasks) |
| **Projecte** | TR3DAW — Plataforma de venda d’entrades |
| **Agents aplicats** | [AgentLaravel.md](../../Agents/backend/AgentLaravel.md), [AgentPosgres.md](../../Agents/backend/AgentPosgres.md) |
| **Font de veritat BD** | [db/init.sql](../../db/init.sql), [db/insert.sql](../../db/insert.sql) |
| **Data d’implementació** | Març 2026 |

## Resum executiu

S’han creat sis models Eloquent en **català** (`Usuari`, `Esdeveniment`, `ZonaSeient`, `Seient`, `Comanda`, `Tiquet`) que mapegen les taules PostgreSQL definides per scripts SQL (sense migracions d’esquema de negoci). El model `User` per defecte de Laravel s’ha **substituït** per `Usuari` (taula `usuaris`, columnes `nom`, `correu_electronic`, `contrasenya`, `rol`, `creat_el`). La migració inicial deixa de crear la taula `users` per evitar conflicte amb l’esquema SQL. S’ha afegit `UsuariFactory` i s’ha buidat el seeder perquè les dades inicials vinguin de `insert.sql`.

## Fitxers creats

| Ruta |
|------|
| [backend-api/app/Models/Usuari.php](../../backend-api/app/Models/Usuari.php) |
| [backend-api/app/Models/Esdeveniment.php](../../backend-api/app/Models/Esdeveniment.php) |
| [backend-api/app/Models/ZonaSeient.php](../../backend-api/app/Models/ZonaSeient.php) |
| [backend-api/app/Models/Seient.php](../../backend-api/app/Models/Seient.php) |
| [backend-api/app/Models/Comanda.php](../../backend-api/app/Models/Comanda.php) |
| [backend-api/app/Models/Tiquet.php](../../backend-api/app/Models/Tiquet.php) |
| [backend-api/database/factories/UsuariFactory.php](../../backend-api/database/factories/UsuariFactory.php) |

## Fitxers eliminats

| Ruta |
|------|
| `backend-api/app/Models/User.php` |
| `backend-api/database/factories/UserFactory.php` |

## Fitxers modificats

| Ruta | Canvi |
|------|--------|
| [backend-api/config/auth.php](../../backend-api/config/auth.php) | `AUTH_MODEL` per defecte: `App\Models\Usuari::class` |
| [backend-api/.env.example](../../backend-api/.env.example) | `DB_*` per a PostgreSQL; `AUTH_MODEL=App\Models\Usuari` |
| [backend-api/database/migrations/0001_01_01_000000_create_users_table.php](../../backend-api/database/migrations/0001_01_01_000000_create_users_table.php) | Eliminada la creació de la taula `users`; es mantenen `password_reset_tokens` i `sessions` |
| [backend-api/database/seeders/DatabaseSeeder.php](../../backend-api/database/seeders/DatabaseSeeder.php) | Sense creació d’usuari de prova; comentari sobre `insert.sql` |

## Configuració

- **`.env` (còpia des de `.env.example`):** `DB_CONNECTION=pgsql`, base de dades, usuari i contrasenya; `AUTH_MODEL=App\Models\Usuari` si es vol sobreescriure el valor per defecte.
- **`config/auth.php`:** `providers.users.model` → `App\Models\Usuari` (via `env('AUTH_MODEL', App\Models\Usuari::class)`).

## Esquema de base de dades

- Taules: `usuaris`, `esdeveniments`, `zones_de_seient`, `seients`, `comandes`, `tiquets`.
- ENUMs PostgreSQL: `rol_usuari`, `estat_seient` (`disponible`, `reservat`, `venut`), `estat_comanda` (`pendent`, `completada`, `expirada`).

## Relacions Eloquent (resum)

| Model | Mètode | Tipus | Model relacionat |
|-------|--------|-------|------------------|
| `Usuari` | `comandes()` | hasMany | `Comanda` |
| `Usuari` | `seientsRetinguts()` | hasMany | `Seient` |
| `Esdeveniment` | `zonesDeSeient()` | hasMany | `ZonaSeient` |
| `ZonaSeient` | `esdeveniment()` | belongsTo | `Esdeveniment` |
| `ZonaSeient` | `seients()` | hasMany | `Seient` |
| `Seient` | `zona()` | belongsTo | `ZonaSeient` |
| `Seient` | `usuariQueReté()` | belongsTo | `Usuari` |
| `Seient` | `tiquet()` | hasOne | `Tiquet` |
| `Comanda` | `usuari()` | belongsTo | `Usuari` |
| `Comanda` | `tiquets()` | hasMany | `Tiquet` |
| `Tiquet` | `comanda()` | belongsTo | `Comanda` |
| `Tiquet` | `seient()` | belongsTo | `Seient` |

## Codi destacat (comportament d’usuari)

- `getAuthPasswordName()` retorna `'contrasenya'`.
- `getRememberTokenName()` retorna `null` (no hi ha columna `remember_token` a `usuaris`).
- `CREATED_AT = 'creat_el'`, `UPDATED_AT = null` per alineació amb l’esquema SQL.

## Proves realitzades

| Ordre | Resultat esperat |
|-------|------------------|
| `php artisan tinker --execute="echo (new App\Models\Usuari)->getTable();"` | Imprimeix `usuaris` (sense necessitat de taula existent per al nom de taula). |
| Consultes `Usuari::count()` després d’aplicar `init.sql` + `insert.sql` i configurar PostgreSQL | Retorna el nombre d’usuaris (p. ex. 3 amb el seed SQL). |

**Nota:** Si el `.env` apunta a SQLite sense fitxer o sense taules, les ordres que consulten la BD fallaran. Cal configurar PostgreSQL i executar els scripts SQL abans de validar consultes reals.

## Decisions i riscos

| Tema | Decisió |
|------|---------|
| Migracions vs SQL | La taula `users` de Laravel **no** es crea per migració; el domini és només a `init.sql` (AgentPosgres). |
| `remember_token` | Desactivat al model; la columna no existeix a `usuaris`. |
| Restabliment de contrasenya | La taula `password_reset_tokens` usa `email` com a clau; el model usa `correu_electronic`. Caldrà personalitzar el broker o la taula en una tasca futura si es fa servir el flux estàndard de Laravel. |
| `User` / `Usuari` | Qualsevol referència antiga a `App\Models\User` s’ha d’actualitzar a `Usuari`. |

## Pendents / següents passos

- S1.2 — Autenticació API (Sanctum/JWT) amb `correu_electronic` i `contrasenya`.
- Opcional: `php artisan key:generate` i `.env` complet per a tests Feature.
- Opcional: adaptar `password_reset_tokens` o el model per a restabliment de contrasenya amb columna catalana.
