# Proves E2E (Playwright)

## Requisits abans de `npm test`

1. **Base de dades PostgreSQL** amb l’esquema del projecte (`db/init.sql` al repositori arrel), inclosa la taula `personal_access_tokens` (Sanctum).
2. **`backend-api/.env`** configurat (`DB_*`, `APP_KEY` amb `php artisan key:generate`).
3. **API Laravel en marxa** en un port lliure (els ports `8000` / `8080` sovint estan ocupats per altres serveis al mateix equip):

```bash
cd backend-api
php artisan serve --host=127.0.0.1 --port=8777
```

4. **Variable d’entorn** (opcional si uses el port per defecte del `playwright.config.js`):

```bash
# PowerShell
$env:API_BASE_URL="http://127.0.0.1:8777"; npm test

# bash
API_BASE_URL=http://127.0.0.1:8777 npm test
```

## Proves d’auth API

- Fitxer: `auth-api.spec.js` — cobreix `GET/POST` sobre `/api/register`, `/api/login`, `/api/logout`, `/api/usuari` (Sanctum Bearer).
- Si el `baseURL` apunta a un servei que **no** és Laravel (HTML de Moodle, WordPress, etc.), la comprovació inicial falla amb un missatge explícit.

## Proves de `return_to`

- Fitxer: `return-to.spec.js` — cobreix resolució segura del destí després de login:
  - `return_to` intern vàlid;
  - bloqueig de `return_to` extern;
  - bloqueig de path malformat;
  - fallback quan no arriba `return_to`;
  - login fallit sense `return_to_resolta`.

- Execució només d’aquest fitxer:

```bash
npx playwright test return-to.spec.js
```

## Smoke

- `smoke.spec.js` — inclou una petició a `/api/usuari` (usa el mateix `baseURL`).
