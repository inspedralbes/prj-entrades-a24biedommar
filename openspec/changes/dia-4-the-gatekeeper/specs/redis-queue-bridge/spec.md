## ADDED Requirements

### Requisit: Publicació de Canvis d'Estat de Cua
Laravel HA DE publicar els canvis d'estat de la cua a Redis quan l'estat de la cua canviï.

#### Escena: Usuari s'uneix a la cua
- **WHEN** usuari s'uneix a la cua exitosament (validat per Gatekeeper)
- **THEN** Laravel publica esdeveniment "user-joined" al canal Redis "queue:{event_id}"

#### Escena: Usuari surt de la cua
- **WHEN** usuari és eliminat de la cua (entra a l'event o surt voluntàriament)
- **THEN** Laravel publica esdeveniment "user-left" al canal Redis "queue:{event_id}"

#### Escena: Threshold N actualitzat
- **WHEN** administrador ajusta el threshold N per a un event
- **THEN** Laravel publica esdeveniment "threshold-updated" al canal Redis "queue:{event_id}"

### Requisit: Convenció de Nomenclatura de Canals
Laravel HA DE fer servir nomenclatura consistent per als canals de cua.

#### Escena: Publicació a canal específic d'event
- **WHEN** es publica esdeveniment de cua
- **THEN** el nom del canal HA DE seguir el format "queue:{event_id}" (ex: "queue:123")

### Requisit: Format del Missatge
Laravel HA DE publicar missatges en format JSON amb estructura consistent.

#### Escena: Estructura del missatge
- **WHEN** es publica esdeveniment de cua
- **THEN** el missatge HA DE contenir: event_type, event_id, user_id (si aplica), timestamp, data payload

### Requisit: Consulta de Threshold N per Event
Laravel HA DE proporcionar endpoint per obtenir el threshold N específic de cada esdeveniment.

#### Escena: Consulta de threshold per event
- **WHEN** Node.js demana informació del threshold N per a un event
- **THEN** Laravel retorna el valor des de la taula events o configuració
- **EXEMPLE**: Event 1 → threshold=50, Event 2 → threshold=200

### Requisit: Gestió de Subscripcions
Node.js Gatekeeper HA DE subscriure's als canals de Redis per rebre esdeveniments de cua.

#### Escena: Gatekeeper subscriu a cua d'event
- **WHEN** servei Gatekeeper s'inicia o un event esdevé actiu
- **THEN** Node.js es subscriu al canal Redis "queue:{event_id}" per rebre actualitzacions en temps real

#### Escena: Gatekeeper gestiona fallida de connexió Redis
- **WHEN** la connexió Redis es perd
- **THEN** Gatekeeper intenta reconnectar amb backoff exponencial, manté estat local durant la desconnexió