## 1. Backend - Eliminar Sistema de Cues Laravel

- [x] 1.1 Eliminar `backend-api/app/Http/Controllers/QueueController.php`
- [x] 1.2 Eliminar `backend-api/app/Services/QueueEventService.php`
- [x] 1.3 Modificar `backend-api/routes/api.php`:
  - [x] Eliminar imports de QueueController
  - [x] Eliminar totes les rutes /api/queue/*
- [x] 1.4 Revisar `backend-api/bootstrap/cache/services.php` per eliminar referències de cues si cal
- [x] 1.5 Verificar que no queden referències a QueueEventService a altres fitxers

## 2. Backend - Millorar TicketmasterService

- [x] 2.1 Modificar `backend-api/app/Services/TicketmasterService.php`:
  - [x] 2.1.1 Afegir mètode per obtenir tots els camps d'un event (getFullEventDetails)
  - [x] 2.1.2 Extendre transformEventDetail() per incloure:
    - [x] Totes les imatges (ratio, url)
    - [x] Venue complet (address, city, country, coordinates, capacity)
    - [x] Classificacions (segment, genre, subgenre, type, subType)
    - [x] PriceRanges complet
    - [x] Info, pleaseNote
    - [x] Seatmap staticUrl
    - [x] Attractions/artistes
    - [x] Accessibility, parking, doorsInfo
  - [x] 2.1.3 Afegir mètode getAllEvents() per obtenir llistat complet (més de 20)
  - [x] 2.1.4 Afegir mètode per cercar per ID o nom

## 3. Backend - Crear Sistema de Sincronització

- [x] 3.1 Crear comandament Artisan `events:sync`:
  - [x] 3.1.1 Crear fitxer `backend-api/app/Console/Commands/SyncEventsCommand.php`
  - [x] 3.1.2 Implementar opció --force (força sincronització)
  - [x] 3.1.3 Implementar opció --limit=N (limita nombre d'esdeveniments)
  - [x] 3.1.4 Implementar opció --event-id=xxx (sincronitza un sol event)
  - [x] 3.1.5 Cridar TicketmasterService per obtenir dades
  - [x] 3.1.6 Desar/actualitzar a la base de dades (taula esdeveniments)
- [x] 3.2 Crear endpoint API per sincronització:
  - [x] 3.2.1 Afegir mètode syncEvents() a EventController o nou SyncController
  - [x] 3.2.2 Afegir ruta POST /api/sync/events a routes/api.php
  - [x] 3.2.3 Resposta JSON: { success, synced, errors }
- [x] 3.3 Crear/actualitzar model Esdeveniment:
  - [x] 3.3.1 Afegir camps per emmagatzemar dades completes de Ticketmaster
  - [x] 3.3.2 Camps nous: tm_id, tm_image_urls, tm_venue_details, tm_classifications, tm_price_ranges_json, tm_attractions_json, tm_accessibility, etc.
- [x] 3.4 Crear migració per afegir nous camps a la taula esdeveniments

## 4. Frontend - Eliminar Cues i Actualitzar Flux

- [x] 4.1 Eliminar `frontend/app/stores/queue.js`
- [x] 4.2 Identificar i eliminar components de cua:
  - [x] 4.2.1 Buscar components de cua a `frontend/app/components/`
  - [x] 4.2.2 Eliminar QueueStatus.vue, WaitingRoom.vue, etc. si existeixen
- [x] 4.3 Modificar flux de compra:
  - [x] 4.3.1 Quan usuari clica a event → anar directament a detall (sense cua)
  - [x] 4.3.2 Quan usuari clica "Comprar entrades" → anar directament a selector de seients
  - [x] 4.3.3 Eliminar qualsevol referència a "espera" o "turn token"
- [x] 4.4 Revisar pàgines que usaven la cua:
  - [x] 4.4.1 Modificar landing per eliminar missatges de cua
  - [x] 4.4.2 Modificar event-detail per mostrar botó directe de compra

## 5. Backend - Aturar o Modificar backend-realtime

**Nota:** El backend-realtime (port 3001) queda actiu però sense funcionalitat de cues. Per aturar-lo completament, cal executar `npm stop` o eliminar el fitxer `backend-realtime/package.json` del scripts de docker-compose.

## 6. Integració i Verificació

- [ ] 6.1 Test de sincronització:
  - [ ] 6.1.1 Executar `php artisan events:sync` manualment
  - [ ] 6.1.2 Verificar que els esdeveniments es desaven a la base de dades
  - [ ] 6.1.3 Verificar que les dades completes (imatges, preus, venue) es guarden
- [ ] 6.2 Test de flux de compra:
  - [ ] 6.2.1 Anar a landing → seleccionar event → veure detall → comprar entrades
  - [ ] 6.2.2 Verificar que NO apareix cap pantalla de cua
  - [ ] 6.2.3 Verificar que el selector de seients carrega directament
- [ ] 6.3 Verificar eliminació completa:
  - [ ] 6.3.1 Cercar "queue" al codi per verificar que no queden referències
  - [ ] 6.3.2 Verificar que els endpoints /api/queue/* retornen 404
- [ ] 6.4 Verificar integració Ticketmaster:
  - [ ] 6.4.1 Test endpoint /api/events per veure dades completes
  - [ ] 6.4.2 Test endpoint /api/events/{id} per veure detall complet

## 7. Documentació

- [ ] 7.1 Actualitzar README del projecte amb el nou flux de compra
- [ ] 7.2 Documentar comandament events:sync
- [ ] 7.3 Documentar nous endpoints API
- [ ] 7.4 Netejar codi i comments relacionats amb cues