const { test, expect } = require('@playwright/test');
const { assertLaravelApiJson } = require('./helpers/assertLaravelApi');

/**
 * E2E del flux return_to al login.
 */
test.describe('API auth — return_to', () => {
    test.beforeAll(async ({ request }) => {
        await assertLaravelApiJson(request);
    });

    test('login amb return_to intern vàlid retorna return_to_resolta igual', async ({ request }) => {
        const email = `e2e_returnto_ok_${Date.now()}@test.local`;
        const password = 'password12';

        const reg = await request.post('/api/register', {
            data: {
                nom: 'ReturnTo OK',
                correu_electronic: email,
                contrasenya: password,
                contrasenya_confirmation: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(reg.status()).toBe(201);

        const login = await request.post('/api/login', {
            data: {
                correu_electronic: email,
                contrasenya: password,
                return_to: '/events/42',
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(login.status()).toBe(200);

        const body = await login.json();
        expect(body.return_to_resolta).toBe('/events/42');
    });

    test('login amb return_to extern bloquejat retorna fallback', async ({ request }) => {
        const email = `e2e_returnto_ext_${Date.now()}@test.local`;
        const password = 'password12';

        const reg = await request.post('/api/register', {
            data: {
                nom: 'ReturnTo extern',
                correu_electronic: email,
                contrasenya: password,
                contrasenya_confirmation: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(reg.status()).toBe(201);

        const login = await request.post('/api/login', {
            data: {
                correu_electronic: email,
                contrasenya: password,
                return_to: 'https://evil.example/phishing',
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(login.status()).toBe(200);

        const body = await login.json();
        expect(body.return_to_resolta).toBe('/');
    });

    test('login amb return_to malformat retorna fallback', async ({ request }) => {
        const email = `e2e_returnto_bad_${Date.now()}@test.local`;
        const password = 'password12';

        const reg = await request.post('/api/register', {
            data: {
                nom: 'ReturnTo malformat',
                correu_electronic: email,
                contrasenya: password,
                contrasenya_confirmation: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(reg.status()).toBe(201);

        const login = await request.post('/api/login', {
            data: {
                correu_electronic: email,
                contrasenya: password,
                return_to: '//evil.com',
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(login.status()).toBe(200);

        const body = await login.json();
        expect(body.return_to_resolta).toBe('/');
    });

    test('login sense return_to usa fallback', async ({ request }) => {
        const email = `e2e_returnto_none_${Date.now()}@test.local`;
        const password = 'password12';

        const reg = await request.post('/api/register', {
            data: {
                nom: 'ReturnTo buit',
                correu_electronic: email,
                contrasenya: password,
                contrasenya_confirmation: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(reg.status()).toBe(201);

        const login = await request.post('/api/login', {
            data: {
                correu_electronic: email,
                contrasenya: password,
            },
            headers: { 'Content-Type': 'application/json' },
        });
        expect(login.status()).toBe(200);

        const body = await login.json();
        expect(body.return_to_resolta).toBe('/');
    });

    test('login fallit no retorna return_to_resolta', async ({ request }) => {
        const login = await request.post('/api/login', {
            data: {
                correu_electronic: `e2e_returnto_fail_${Date.now()}@test.local`,
                contrasenya: 'incorrecta',
                return_to: '/events/42',
            },
            headers: { 'Content-Type': 'application/json' },
        });

        expect(login.status()).toBe(401);
        const body = await login.json();
        expect(body.return_to_resolta).toBeUndefined();
    });
});
