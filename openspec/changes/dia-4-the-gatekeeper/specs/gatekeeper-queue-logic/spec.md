## ADDED Requirements

### Requisit: Validació d'Usuari a la Cua
El servei Gatekeeper HA DE validar que l'usuari tingui un token d'autenticació vàlid abans d'afegir-lo a la cua.

#### Escena: Usuari vàlid entra a la cua
- **WHEN** usuari es connecta a Gatekeeper WebSocket amb token Bearer vàlid
- **THEN** servei valida token, extreu user_id i event_id, afegeix usuari a la cua amb posició

#### Escena: Token invàlid rebutjat
- **WHEN** usuari es connecta a Gatekeeper WebSocket amb token invàlid o expirat
- **THEN** servei rebutja connexió amb missatge d'error, usuari no afegit a la cua

### Requisit: Generació de Turn Token (TicketMaster)
El servei Gatekeeper HA DE generar un token de torn (JWT) per a cada usuari quan arriba al davant de la cua. Aquest token funciona com a entrada directa al mapa de seients.

#### Escena: Usuari arriba al davant de la cua
- **WHEN** la posició de l'usuari a la cua és 1 (primer de la cua)
- **THEN** servei genera token JWT amb user_id, event_id, expiry, emiteix esdeveniment "turn-granted"

#### Escena: Turn token contingut obligatori
- **WHEN** turn token és generat
- **THEN** token HA DE contenir: user_id, event_id, issued_at, expires_at, token_type="turn", turn_token=true

#### Escena: Turn token amb TTL
- **WHEN** turn token és generat
- **THEN** token té temps de vida limitat (ex: 5 minuts) per evitar especulació de torns

### Requisit: Gestió de Posicions Decreixent
El servei Gatekeeper HA DE mantenir posicions actualitzades de forma decreixent: quan un usuari davant compra (surt de la cua), tots els usuaris posteriors avancen una posició automàticament.

#### Escena: Posició s'actualitza quan usuari davant compra
- **WHEN** un usuari és eliminat de la cua (entra a l'event o surt voluntàriament)
- **THEN** totes les posicions restants decrementen en 1, Socket.IO fa broadcast de les posicions actualitzades

#### Escena: Usuari surt voluntàriament de la cua
- **WHEN** usuari es desconnecta del WebSocket o demana sortir
- **THEN** usuari eliminat de la cua, posició alliberada, posicions restants ajustades

### Requisit: aïllament de Cua per Event
El servei Gatekeeper HA DE mantenir cues separades per cada esdeveniment.

#### Escena: Usuaris entren a cues de diferents esdeveniments
- **WHEN** usuaris es connecten a la cua de diferents esdeveniments
- **THEN** cada esdeveniment té cua independent amb la seva pròpia numeració de posicions

### Requisit: Aplicació del Threshold N per Event
El servei Gatekeeper HA DE fer servir el llindar N per definir quants usuaris poden estar simultàniament al mapa de seients. La resta esperen a la cua.

#### Escena: Cua plena segons threshold N
- **WHEN** la cua arriba al threshold N d'un esdeveniment i un nou usuari intenta entrar
- **THEN** usuari rep missatge "queue-full", no pot entrar fins que s'alliberi una plaça

#### Escena: Threshold N variable per event
- **WHEN** el sistema rep la sol·licitud d'entrada a la cua per a un event
- **THEN** el threshold N es consulta des de Laravel (taula events o configuració), cada event pot tenir valor diferent
- **EXEMPLE**: Event popular → N=50, Event menys demandat → N=200

#### Escena: S'allibera plaça a la cua plena
- **WHEN** usuari surt de la cua (entra a l'event o es desconnecta)
- **THEN** següent usuari de la llista d'espera (si n'hi ha) es mou a la cua activa, notificat de la nova posició