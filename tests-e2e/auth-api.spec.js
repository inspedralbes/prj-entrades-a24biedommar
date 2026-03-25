const { test, expect } = require('@playwright/test');
const { assertLaravelApiJson } = require('./helpers/assertLaravelApi');

/**
 * E2E contra l’API REST d’autenticació (Laravel Sanctum).
 * Requisit: backend escoltant a baseURL (vegeu playwright.config.js).
 */

test.describe('API auth — Sanctum', () => {
    test.beforeAll(async ({ request }) => {
        await assertLaravelApiJson(request);
    });
    test('GET /api/usuari sense Bearer retorna 401', async ({ request }) => {
        const response = await request.get('/api/usuari');
        expect(response.status()).toBe(401);
    });

    test('POST /api/login amb credencials incorrectes retorna 401', async ({ request }) => {
        const response = await request.post('/api/login', {
            data: {
                correu_electronic: 'inexistent@test.local',
                contrasenya: 'incorrecta0000',
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(response.status()).toBe(401);
        const body = await response.json();
        expect(body.missatge).toBeDefined();
    });

    test('registre → GET perfil → logout → perfil amb mateix token retorna 401', async ({ request }) => {
        const email = `e2e_${Date.now()}@test.local`;
        const password = 'password12';

        const registerRes = await request.post('/api/register', {
            data: {
                nom: 'Usuari E2E',
                correu_electronic: email,
                contrasenya: password,
                contrasenya_confirmation: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(registerRes.status()).toBe(201);
        const registerJson = await registerRes.json();
        expect(registerJson.token).toBeTruthy();
        expect(registerJson.usuari.correu_electronic).toBe(email);
        expect(registerJson.usuari.rol).toBe('client');
        expect(registerJson.usuari.contrasenya).toBeUndefined();

        const token = registerJson.token;

        const meRes = await request.get('/api/usuari', {
            headers: { Authorization: `Bearer ${token}` },
        });
        expect(meRes.status()).toBe(200);
        const meJson = await meRes.json();
        const perfil = meJson.data !== undefined ? meJson.data : meJson;
        expect(perfil.correu_electronic).toBe(email);

        const logoutRes = await request.post('/api/logout', {
            headers: { Authorization: `Bearer ${token}` },
        });
        expect(logoutRes.status()).toBe(200);

        const afterLogout = await request.get('/api/usuari', {
            headers: { Authorization: `Bearer ${token}` },
        });
        expect(afterLogout.status()).toBe(401);
    });

    test('POST /api/register correu duplicat retorna 422', async ({ request }) => {
        const email = `e2e_dup_${Date.now()}@test.local`;
        const password = 'password12';
        const payload = {
            nom: 'Duplicat',
            correu_electronic: email,
            contrasenya: password,
            contrasenya_confirmation: password,
        };

        const first = await request.post('/api/register', {
            data: payload,
            headers: { 'Content-Type': 'application/json' },
        });
        expect(first.status()).toBe(201);

        const second = await request.post('/api/register', {
            data: payload,
            headers: { 'Content-Type': 'application/json' },
        });
        expect(second.status()).toBe(422);
    });

    test('POST /api/login retorna token i GET /api/usuari coincideix (usuari existent del registre)', async ({
        request,
    }) => {
        const email = `e2e_login_${Date.now()}@test.local`;
        const password = 'password12';

        const reg = await request.post('/api/register', {
            data: {
                nom: 'Login E2E',
                correu_electronic: email,
                contrasenya: password,
                contrasenya_confirmation: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(reg.status()).toBe(201);

        const loginRes = await request.post('/api/login', {
            data: {
                correu_electronic: email,
                contrasenya: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(loginRes.status()).toBe(200);
        const loginJson = await loginRes.json();
        expect(loginJson.token).toBeTruthy();
        expect(loginJson.usuari.correu_electronic).toBe(email);

        const meRes = await request.get('/api/usuari', {
            headers: { Authorization: `Bearer ${loginJson.token}` },
        });
        expect(meRes.status()).toBe(200);
        const meJson = await meRes.json();
        const perfil = meJson.data !== undefined ? meJson.data : meJson;
        expect(perfil.correu_electronic).toBe(email);
    });
});
