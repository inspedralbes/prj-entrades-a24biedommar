/**
 * Comprova que baseURL apunta a l’API Laravel (JSON), no a un altre servei (p. ex. HTML de Moodle al :8000).
 *
 * @param {import('@playwright/test').APIRequestContext} request
 * @param {string} [hint]
 */
async function assertLaravelApiJson(request, hint) {
    const res = await request.get('/api/usuari');
    const status = res.status();
    const ct = (res.headers()['content-type'] || '').toLowerCase();
    if (ct.includes('text/html')) {
        let msg =
            'El servidor HTTP no respon JSON a /api/usuari (s’ha rebut HTML). ' +
            'Defineix API_BASE_URL cap al teu `php artisan serve` (p. ex. --port=8777). ';
        if (status === 500) {
            msg +=
                'Si ja és Laravel però veus 500, revisa PostgreSQL, `db/init.sql` (taula personal_access_tokens) i `backend-api/.env`. ';
        } else {
            msg +=
                'Comprova que el port no el faci servir un altre servei (Moodle, WordPress…). ';
        }
        msg += 'Vegeu tests-e2e/README.md.';
        if (hint) {
            throw new Error(`${msg} (${hint})`);
        }
        throw new Error(msg);
    }
    if (!ct.includes('application/json')) {
        throw new Error(
            `Resposta inesperada (HTTP ${status}, content-type: ${ct || 'cap'}). Esperat JSON de Laravel API.`,
        );
    }
}

module.exports = { assertLaravelApiJson };
