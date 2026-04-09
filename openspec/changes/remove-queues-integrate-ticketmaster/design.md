## Context

El projecte TR3 - Entrades actualment té un sistema de cues (The Gatekeeper) implementat que complica l'arquitectura. Simultáneamente, la integració amb Ticketmaster és parcial (només obtenir llistat d'esdeveniments bàsics). Cal simplificar l'arquitectura eliminant les cues i completar la integració amb Ticketmaster per obtenir tota la informació dels events.

## Goals / Non-Goals

**Goals:**
- Eliminar completament el sistema de cues (backend Laravel, servidor Node.js, frontend)
- Completar la integració amb Ticketmaster (100% de dades per event)
- Crear sistema de sincronització d'esdeveniments (manual i automàtic)
- Mantenir funcionalitat de compra d'entrades sense cua

**Non-Goals:**
- No s'implementa altre proveïdor d'entrades (només Ticketmaster)
- No s'implementa sistema de reserves amb temps limitat
- No s'implementa sistema de pagament (ja implementat prèviament)
- No s'elimina Socket.IO completament (pot оставаться per notificacions futures)

## Decisions

1. **Ticketmaster com a única font**: Els esdeveniments vindran exclusivament de Ticketmaster. No es permetrà creació manual d'esdeveniments des del dashboard admin.

2. **Sincronització sota demanda**: Els esdeveniments es sincronitzen sota demanda (manual des del dashboard) o periòdicament via comandament Artisan (cron).

3. **Flux de compra directe**: L'usuari selecciona event → veu detall → selecciona seients → paga. Sense etapa de cua.

4. **Mantenir Redis per cache**: Redis es manté per cache de Ticketmaster (evitar rate limits), però no per cues.

5. **backend-realtime opcional**: El servidor Node.js pot оставаться actiu però sense funcionalitat de cues, o aturar-lo completament si no s'usa per altre cosa.

## Risks / Trade-offs

- [Risk] Canvi de model de dades d'esdeveniments → Mitigació: Fer migració per actualitzar taula events
- [Risk] Rate limits de Ticketmaster API → Mitigació: Cache agressiu + batch sync
- [Risk] Pèrdua de funcionalitat de cues (control d'aforo) → Mitigació: Control via disponibilidad real de Ticketmaster
- [Risk] Eliminació de backend-realtime trenca altre funcionalitat → Mitigació: Revisar primer si té altres usos

## Technical Details

### Eliminació de Cues

**Backend Laravel:**
- Eliminar `app/Http/Controllers/QueueController.php`
- Eliminar `app/Services/QueueEventService.php`
- Eliminar rutes `/api/queue/*` de `routes/api.php`
- Mantenir Redis per cache però no per cues

**Backend Node.js (backend-realtime):**
- Aturar servidor o desactivar handlers de cua
- Mantenir Socket.IO si s'usa per notificacions
- Eliminar `src/services/QueueService.js`
- Eliminar handlers de cua a `gatekeeperHandlers.js`

**Frontend:**
- Eliminar `stores/queue.js`
- Eliminar components de UI de cua (QueueStatus, WaitingRoom, etc.)
- Modificar flux de compra: event → detall → seats → checkout

### Integració Ticketmaster

**TicketmasterService.php - Dades a obtenir per event:**
- ID, nom, dates (localDate, localTime)
- Imatges (totes les mides: 16:9, 4:3, original)
- Venue complet (nom, adreça, ciutat, país, coordenades, capacitat)
- Classificacions (segment, genre, subgenre)
- PriceRanges (min, max, currency)
- Info, pleaseNote (descripcions)
- Seatmap (staticUrl)
- Accessibility, parking, doorsInfo
- Attractions (artistes)
- URL oficial de Ticketmaster

**Nou endpoint sync:**
- `POST /api/sync/events` - Sincronitzar tots els esdeveniments des de Ticketmaster
- Resposta: `{ success: bool, synced: int, errors: string[] }`

**Nou comandament Artisan:**
- `php artisan events:sync` - Sincronitzar esdeveniments des de CLI
- Opcions: `--force`, `--limit=50`, `--event-id=xxx`

### Estructura de Fitxers a Modificar/Crear

**Eliminar:**
- `backend-api/app/Http/Controllers/QueueController.php`
- `backend-api/app/Services/QueueEventService.php`
- `backend-realtime/src/services/QueueService.js`
- `backend-realtime/src/sockets/gatekeeperHandlers.js` (part de cues)
- `frontend/app/stores/queue.js`
- Components de cua al frontend (buscar a `frontend/app/components/`)

**Modificar:**
- `backend-api/routes/api.php` (eliminar rutes de cua)
- `backend-api/app/Models/Esdeveniment.php` (afegir camps Ticketmaster)
- `backend-api/app/Http/Controllers/EventController.php` (afegir sync)
- Flux de compra al frontend

**Crear:**
- `backend-api/app/Console/Commands/SyncEventsCommand.php`
- Nova migració per actualitzar taula `esdeveniments`
- Modificar TicketmasterService per API completa