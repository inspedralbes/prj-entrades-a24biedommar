## 1. Backend - Ticketmaster Integration (S1.8)

- [x] 1.1 Crear EventController a backend-api/app/Http/Controllers/
- [x] 1.2 Implementar mètode index() per obtenir llistat d'esdeveniments de Ticketmaster
- [x] 1.3 Implementar mètode show() per obtenir detall d'un event
- [x] 1.4 Configurar client HTTP per cridar Ticketmaster API
- [x] 1.5 Implementar caché Redis per a respostes de Ticketmaster
- [x] 1.6 Afegir rutes API /api/events i /api/events/{id} a routes/api.php

## 2. Backend - Proximity Filter (S1.8)

- [x] 2.1 Afegir paràmetres de geolocalització a l'API de Ticketmaster
- [x] 2.2 Implementar filtre de radi configurable (default 50km)
- [x] 2.3 Crear endpoint per obtenir ubicació de l'usuari

## 3. Backend - Favorites System (S1.9)

- [x] 3.1 Crear Model Favorite a backend-api/app/Models/
- [x] 3.2 Crear FavoriteController a backend-api/app/Http/Controllers/
- [x] 3.3 Implementar mètode addFavorite() per guardar favorits a PostgreSQL
- [x] 3.4 Implementar mètode removeFavorite() per eliminar favorits
- [x] 3.5 Implementar mètode listFavorites() per llistar favorits de l'usuari
- [x] 3.6 Afegir rutes API /api/favorites a routes/api.php

## 4. Frontend - Events Store (S1.10)

- [ ] 4.1 Crear store events.js a frontend/app/stores/
- [ ] 4.2 Implementar state: events, currentEvent, loading, error
- [ ] 4.3 Implementar actions: fetchEvents(), fetchEvent(id), filterByProximity()
- [ ] 4.4 Implementar actions per favorits: addFavorite(), removeFavorite(), checkFavorite()

## 5. Frontend - Landing Page (S1.10)

- [ ] 5.1 Crear pàgina index.vue a frontend/app/pages/
- [ ] 5.2 Implementar grid d'esdeveniments
- [ ] 5.3 Crear component EventCard per mostrar cada event
- [ ] 5.4 Implementar filtre de proximitat (slider o input de radi)
- [ ] 5.5 Integrar geolocalització del navegador
- [ ] 5.6 Aplicar estils DICE (fons negre, botons neó #FF0055, Electric Blue #00F0FF)
- [ ] 5.7 Implementar botó "M'interessa" amb estat (actiu/inactiu)
- [ ] 5.8 Implementar botó "Comprar" que redirigeix a cua virtual

## 6. Integració i Testing

- [ ] 6.1 Integrar Landing amb API d'esdeveniments
- [ ] 6.2 Integrar sistema de favorits amb autenticació
- [ ] 6.3 Verificar filtre de proximitat
- [ ] 6.4 Testing de tots els flows