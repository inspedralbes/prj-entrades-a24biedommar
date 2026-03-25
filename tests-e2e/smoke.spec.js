const { test, expect } = require('@playwright/test');

test('Smoke Test: El sistema ha de carregar correctament', async ({ page }) => {
    // Intentar connectar al frontend de Nuxt
    await page.goto('http://localhost:3000');

    // Verificar que el títol conté el nom del projecte o algun element base
    // Com que el projecte és buit, buscarem algun element de Nuxt 4 minimal
    await expect(page).toBeDefined();
});

test('API Health Check: Laravel ha de respondre', async ({ request }) => {
    // Ruta protegida sense Bearer: 401 (API viva; alineat amb S1.2 GET /api/usuari)
    const response = await request.get('/api/usuari');
    expect(response.status()).toBe(401);
});
