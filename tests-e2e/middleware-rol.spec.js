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
        expect(resClient.status()).toBe(201);
        var bodyClient = await resClient.json();
        tokenClient = bodyClient.token;

        // Login usuari admin (existent a insert.sql)
        var resAdmin = await request.post('/api/login', {
            data: {
                correu_electronic: 'admin@tr3.daw',
                contrasenya: 'password',
            },
            headers: { 'Content-Type': 'application/json' },
        });
        if (resAdmin.status() === 200) {
            var bodyAdmin = await resAdmin.json();
            tokenAdmin = bodyAdmin.token;
        }
    });

    // ——— Proves sense token ———

    test('GET /api/admin/estat sense token retorna 401', async function ({ request }) {
        var res = await request.get('/api/admin/estat');
        expect(res.status()).toBe(401);
    });

    test('GET /api/client/perfil-extens sense token retorna 401', async function ({ request }) {
        var res = await request.get('/api/client/perfil-extens');
        expect(res.status()).toBe(401);
    });

    // ——— Proves amb token de client ———

    test('GET /api/admin/estat amb token de client retorna 403 amb missatge de rol', async function ({ request }) {
        var res = await request.get('/api/admin/estat', {
            headers: { Authorization: 'Bearer ' + tokenClient },
        });
        expect(res.status()).toBe(403);
        var body = await res.json();
        expect(body.missatge).toBeDefined();
        expect(body.rol_requerit).toBe('admin');
        expect(body.rol_actual).toBe('client');
    });

    test('GET /api/client/perfil-extens amb token de client retorna 200', async function ({ request }) {
        var res = await request.get('/api/client/perfil-extens', {
            headers: { Authorization: 'Bearer ' + tokenClient },
        });
        expect(res.status()).toBe(200);
        var body = await res.json();
        expect(body.missatge).toBeDefined();
    });

    // ——— Proves amb token d'admin ———

    test("GET /api/admin/estat amb token d'admin retorna 200", async function ({ request }) {
        if (tokenAdmin === null) {
            test.skip();
            return;
        }
        var res = await request.get('/api/admin/estat', {
            headers: { Authorization: 'Bearer ' + tokenAdmin },
        });
        expect(res.status()).toBe(200);
        var body = await res.json();
        expect(body.missatge).toBeDefined();
    });

    test("GET /api/client/perfil-extens amb token d'admin retorna 403 amb missatge de rol", async function ({ request }) {
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
        expect(body.rol_actual).toBe('admin');
    });
});
