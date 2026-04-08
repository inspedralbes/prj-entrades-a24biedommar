## 1. Frontend - Events Store (Pinia)

- [x] 1.1 Crear el fitxer `frontend/app/stores/events.js`.
- [x] 1.2 Definir l'estat: `events`, `loading`, `error`, `userLocation`, `radius`.
- [x] 1.3 Implementar acció `fetchEvents()` que utilitzi la geolocalització i cridi a l'API de Laravel.
- [x] 1.4 Implementar accions `addFavorite()` i `removeFavorite()` amb mutació optimista.
- [x] 1.5 Implementar acció `setRadius(newRadius)` que torni a carregar els esdeveniments.

## 2. Frontend - Components UI

- [x] 2.1 Crear el component `frontend/app/components/EventCard.vue`.
- [x] 2.2 Implementar el disseny de la targeta amb fons negre, títol, data i imatge.
- [x] 2.3 Afegir botó "M'interessa" amb estats visuals segons si és favorit.
- [x] 2.4 Afegir botó "Comprar" amb estils neó (#FF0055).

## 3. Frontend - Landing Page

- [x] 3.1 Crear la pàgina `frontend/app/pages/index.vue`.
- [x] 3.2 Implementar el layout de fons negre i títol de l'esdeveniment (DICE style).
- [x] 3.3 Implementar el grid per mostrar les `EventCard`.
- [x] 3.4 Afegir el selector de radi de proximitat (slider o input).
- [x] 3.5 Integrar la lògica de geolocalització del navegador a l'inici.

## 4. Integració i Verificació

- [x] 4.1 Connectar la pàgina Landing amb el store de Pinia.
- [x] 4.2 Verificar que els botons "M'interessa" persisteixen els canvis a la base de dades.
- [x] 4.3 Verificar que el filtre de proximitat actualitza correctament el llistat.
- [x] 4.4 Realitzar proves d'estils i responsivitat.
