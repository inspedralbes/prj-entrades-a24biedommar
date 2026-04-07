## 1. Backend - Return-to Redirect (S1.4)

- [x] 1.1 Crear Controlador ReturnToController a backend-api/app/Http/Controllers/
- [x] 1.2 Implementar mètode saveReturnTo() per desar URL a sessió
- [x] 1.3 Implementar mètode clearReturnTo() per esborrar de sessió
- [x] 1.4 Afegir ruta POST /api/return-to/save a routes/api.php
- [x] 1.5 Modificar AuthController per recuperar return_to après login (ja implementat amb ReturnToResolver)

## 2. Frontend - Auth Store Pinia (S1.5)

- [x] 2.1 Crear store auth.ts a frontend/stores/ usant agents
- [x] 2.2 Implementar state: token, user, isAuthenticated
- [x] 2.3 Implementar actions: login(), logout(), fetchUser(), initAuth()
- [x] 2.4 Configurar persistència amb cookie per SSR (localStorage per ara)

## 3. Frontend - Login/Register Pages (S1.6)

- [x] 3.1 Crear pàgina login.vue a frontend/pages/auth/
- [x] 3.2 Crear pàgina register.vue a frontend/pages/auth/
- [x] 3.3 Implementar formulari Login amb validació
- [x] 3.4 Implementar formulari Registre amb validació
- [x] 3.5 Aplicar estils DICE (fons negre, botons neó #FF0055, Electric Blue #00F0FF)
- [x] 3.6 Integrar amb auth store per login/register

## 4. Frontend - Navigation Middleware (S1.7)

- [x] 4.1 Crear middleware auth.global.ts a frontend/middleware/
- [x] 4.2 Definir rutes públiques: /, /events, /login, /register
- [x] 4.3 Definir rutes protegides: /checkout, /profile, /tickets, /waiting-room
- [x] 4.4 Implementar guard per a rutas protegides (redirigir a login)
- [x] 4.5 Implementar guard per a rutes auth quan ja autenticat (redirigir a Landing)
- [x] 4.6 Integrar return_to per preservar URL original

## 5. Integració i Testing

- [x] 5.1 Integrar Login → ReturnTo → Landing
- [x] 5.2 Verificar flux complet d'autenticació
- [x] 5.3 Testing de middleware de navegació