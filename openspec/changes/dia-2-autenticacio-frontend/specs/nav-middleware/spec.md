## ADDED Requirements

### Requirement: Navigation Middleware
El sistema SHALL proporcionar middleware de navegació per protegir rutes segons l'estat d'autenticació, redirigint usuaris no autenticats a login i usuaris autenticats lluny de login.

#### Scenario: Accés a ruta protegida sense autenticació
- **WHEN** usuari no autenticat intenta accedir a ruta protegida (ex: `/checkout`)
- **THEN** middleware guarda `return_to` a la sessió i redirigeix a `/login`

#### Scenario: Accés a ruta pública quan ja està autenticat
- **WHEN** usuari autenticat intenta accedir a `/login` o `/register`
- **THEN** middleware redirigeix a `/` (Landing)

#### Scenario: Accés a ruta protegida amb autenticació
- **WHEN** usuari autenticat accedeix a ruta protegida
- **THEN** middleware permet el pas

#### Scenario: Verificació de token expirat
- **WHEN** usuari autenticat intent accedir a ruta protegida amb token expirat
- **THEN** middleware fa logout, guarda return_to, redirigeix a `/login`

#### Scenario: Rutes públiques definides
- **WHEN** usuari accedeix a ruta pública (/, /events, /login, /register)
- **THEN** middleware permet el pas sense verificació

#### Scenario: Rutes protegides definides
- **WHEN** usuari intenta accedir a ruta protegida (/checkout, /profile, /tickets, /waiting-room)
- **THEN** middleware verifica autenticació abans de permetre accés