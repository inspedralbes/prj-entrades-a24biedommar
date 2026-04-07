## Why

Per completar el sistema d'autenticació, cal implementar la part frontend: el Store de Pinia per gestionar l'estat d'usuari, les pàgines de Login/Registre amb els estils DICE (fons negre, botons neó), i el middleware de navegació per protegir les rutes segons l'estat d'autenticació. Això permetrà que l'usuari pugui iniciar sessió, enregistrar-se i navegar per l'aplicació de forma segura.

## What Changes

- **S1.4**: Sistema de redirecció `return_to` al backend Laravel per guardar URL destí abans de login
- **S1.5**: Store d'Autenticació amb Pinia per gestionar estat d'usuari, token JWT i perfil
- **S1.6**: Pàgines Login i Registre amb formularis i estils DICE (neó #FF0055, Electric Blue #00F0FF)
- **S1.7**: Middleware de navegació Nuxt per protegir rutes segons estat d'autenticació (auth guard)

## Capabilities

### New Capabilities
- `return-to-redirect`: Sistema de redirecció URL-abans-login al backend Laravel
- `auth-store-pinia`: Store Pinia per gestionar autenticació (token, usuari, logout)
- `login-register-page`: Pàgines frontend de Login/Registre amb estils DICE
- `nav-middleware`: Middleware Nuxt per control d'accés a rutes

### Modified Capabilities
- (cap, basada en tasques anteriors ja implementades)

## Impact

- **Backend**: Nou endpoint per guardar return_to a la sessió
- **Frontend**: Nova estructura de stores/ Pinia, pàgines views/auth/, middleware utils/
- **Libraries**: Pinia per estat, estils Tailwind DICE