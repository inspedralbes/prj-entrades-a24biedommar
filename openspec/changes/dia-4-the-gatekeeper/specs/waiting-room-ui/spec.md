## ADDED Requirements

### Agents de Referència per Implementació
- **Nuxt**: `/Agents/frontend/AgentNuxt.md`
- **Pinia**: `/Agents/frontend/AgentPinia.md`
- **Sockets**: `/Agents/frontend/AgentSockets.md` (Client Socket.IO)
- **JavaScript**: `/Agents/frontend/AgentJavaScript.md`
- **Estil DICE**: Fons negre, neon pink #FF0055, electric blue #00F0FF

### Requisit: Connexió WebSocket
La pàgina Waiting Room HA D'establir i mantenir una connexió WebSocket amb el servei Gatekeeper.

#### Escena: Pàgina carrega amb sessió vàlida
- **WHEN** usuari navega a Waiting Room amb autenticació vàlida
- **THEN** pàgina estableix connexió Socket.IO a Gatekeeper, s'uneix a la sala de l'event

#### Escena: Connexió perduda
- **WHEN** connexió WebSocket es perd (problemes de xarxa)
- **THEN** pàgina intenta reconnectar automàticament, mostra estat "Reconnecting..."

#### Escena: Connexió fallida permanentment
- **WHEN** intents de reconnectar esgotats
- **THEN** pàgina mostra missatge d'error amb botó de reintent manual

### Requisit: Display de Posició
La Waiting Room HA DE mostrar la posició actual de l'usuari a la cua.

#### Escena: Posició rebuda del servidor
- **WHEN** Socket.IO emet esdeveniment "position-update"
- **THEN** pàgina actualitza el comptador de posició amb animació

#### Escena: Usuari és primer a la cua
- **THEN** quan la posició és 1, mostra missatge "És el teu torn!", mostra botó "Entrar a l'Event"

### Requisit: Animació Flip d'Esperar
La Waiting Room HA DE mostrar una animació flip/compte enrere mentre l'usuari espera.

#### Escena: Usuari esperant amb posició > 1
- **WHEN** usuari està a la cua amb posició superior a 1
- **THEN** pàgina mostra animació flip-card mostrant temps d'espera estimat o compte enrere de posició

#### Escena: Suavitat de l'animació
- **WHEN** posició s'actualitza
- **THEN** animació de transició és suau (300ms de durada), sense salts visuals

### Requisit: Informació d'Estat de Cua
La Waiting Room HA DE mostrar informació rellevant de la cua a l'usuari.

#### Escena: Mostrar estat de la cua
- **WHEN** usuari està a la cua
- **THEN** pàgina mostra: posició actual, temps d'espera estimat, nom de l'event, hora de l'event

#### Escena: Cua plena
- **WHEN** usuari intenta unir-se a una cua plena
- **THEN** pàgina mostra missatge "Cua plena" amb temps d'espera estimat per la propera ranura

### Requisit: Sortir de la Cua
La Waiting Room HA DE permetre a l'usuari sortir voluntàriament de la cua.

#### Escena: Usuari prem el botó de sortir
- **WHEN** usuari prem botó "Sortir de la Cua"
- **THEN** quadre de diàleg de confirmació apareix, en confirmar usuari és eliminat de la cua i redirigit

#### Escena: Usuari navega away
- **WHEN** usuari tanca navegador/tab mentre està a la cua
- **THEN** desconnexió WebSocket trigua eliminació de la cua (timeout elegant)