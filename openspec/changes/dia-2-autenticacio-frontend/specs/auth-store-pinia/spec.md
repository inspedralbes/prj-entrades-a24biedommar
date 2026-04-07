## ADDED Requirements

### Requirement: Auth Store Pinia
El sistema SHALL proporcionar un store Pinia per gestionar l'estat d'autenticació de l'usuari, incloent token JWT, perfil d'usuari, i mètodes per login, logout i verificació d'estat.

#### Scenario: Inicialitzar store sense token
- **WHEN** s'inicialitza l'app sense token guardat
- **THEN** store mostra `isAuthenticated: false`, `user: null`, `token: null`

#### Scenario: Login correcte
- **WHEN** usuari fa login correctament (email + password vàlids)
- **THEN** store guarda token a localStorage/cookie, estableix `isAuthenticated: true`, guarda dades usuari

#### Scenario: Login incorrecte
- **WHEN** usuari fa login amb credencials invàlides
- **THEN** store mostra error, no guarda token, manté estat no autenticat

#### Scenario: Logout
- **WHEN** usuari fa logout
- **THEN** store esborra token, estableix `isAuthenticated: false`, `user: null`

#### Scenario: Obtenir perfil usuari
- **WHEN** usuari autenticat crida `fetchUser()`
- **THEN** store carrega dades usuari des de `/api/usuari`

#### Scenario: Verificar token vàlid
- **WHEN** usuari refresca pàgina amb token existent
- **THEN** store valida token i restaura estat d'autenticació