## ADDED Requirements

### Requirement: Return-to Redirect
El sistema SHALL desar la URL de destinació a la sessió abans de redirigir a login, per tal de poder redirigir l'usuari a la pàgina original un cop autenticat.

#### Scenario: Guardar return_to abans de login
- **WHEN** usuari accedeix a ruta protegida sense autenticar
- **THEN** sistema guarda URL actual a la sessió sota la clau `return_to`

#### Scenario: Redirigir a return_to après login
- **WHEN** usuari inicia sessió correctament i existeix `return_to` a la sessió
- **THEN** sistema redirigeix a la URL guardada i esborra la clau `return_to`

#### Scenario: Redirigir a dashboard si no hi ha return_to
- **WHEN** usuari inicia sessió correctament i NO existeix `return_to`
- **THEN** sistema redirigeix a `/` (Landing)

#### Scenario: Netejar return_to en logout
- **WHEN** usuuari fa logout
- **THEN** sistema esborra la clau `return_to` de la sessió