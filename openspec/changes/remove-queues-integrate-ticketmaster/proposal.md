## Why

El sistema de cues actual (The Gatekeeper) afegeix complexitat innecessària al projecte: infraestructura Redis, servidor Node.js separat, logic de WebSocket, i una espera virtual per l'usuari que no aporta valor al negoci de vendes d'entrades.同时, el projecte actualment només té una integració parcial amb Ticketmaster (basic events list), i es necessiten tots els detalls dels events (imatges completes, preus, disponibilidad, informació del venue, etc.) per mostrar una experiència completa a l'usuari.

Eliminar les cues simplifica l'arquitectura i millora el flux de compra. Integrar Ticketmaster completament permet obtenir el 100% de la informació per event sense necessitat d'input manual.

## What Changes

- **T1**: Eliminar backend Laravel: QueueController, QueueEventService, i totes les rutes relacionades (/api/queue/*)
- **T2**: Eliminar el servidor Node.js de cues (backend-realtime) o desactivar la part de cues
- **T3**: Eliminar frontend: store Pinia de cues (stores/queue.js) i components de UI de cua
- **T4**: Millorar TicketmasterService per obtenir el 100% de les dades per event
- **T5**: Crear nou endpoint API per sincronitzar esdeveniments des de Ticketmaster (full sync)
- **T6**: Crear comandament Artisan per sincronització manual/batch

## Capabilities

### Removed Capabilities
- `queue-system`: Sistema de cua virtual amb WebSocket/Socket.IO
- `gatekeeper-server`: Servidor Node.js per gestió de cues en temps real

### Modified Capabilities
- `ticketmaster-integration`: De integració parcial (només llistat) a integració completa (100% dades)
- `event-management`: Canviar d'esdeveniments creats manualment a esdeveniments sincronitzats des de Ticketmaster

### New Capabilities
- `event-sync-job`: Comandament Artisan per sincronitzar esdeveniments des de Ticketmaster
- `full-event-details`: Endpoint per obtenir detall complet d'un event amb tots els camps de Ticketmaster

## Impact

- **Backend**:
  - Eliminar QueueController.php, QueueEventService.php
  - Eliminar rutes /api/queue/* de routes/api.php
  - Modificar TicketmasterService.php per obtenir dades completes
  - Nou endpoint POST /api/sync/events
  - Nou comandament php artisan events:sync
- **Frontend**:
  - Eliminar stores/queue.js
  - Eliminar components de UI de cua (QueueStatus, Waitlist, etc.)
  - Actualitzar flux de compra (directe al selector de seats sense cua)
- **Infraestructura**:
  - Desactivar Redis per cues (opcional, mantenir per cache si cal)
  - Aturar servidor Node.js de cues (backend-realtime)
  - Mantenir Socket.IO només per notificacions reals (opcional)