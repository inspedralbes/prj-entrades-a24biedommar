## Context

Implementació del frontend de la Landing Page (Cartellera) per al projecte TR3 TicketMaster. Es requereix una interfície moderna, ràpida i amb estètica DICE, integrada amb les APIs de Laravel per a esdeveniments i favorits.

## Goals / Non-Goals

**Goals:**
- Crear una pàgina principal (`index.vue`) amb grid interactiu d'esdeveniments.
- Implementar el store `events.js` a Pinia 3 per centralitzar l'estat.
- Desenvolupar el component `EventCard` seguint les guies de l'AgentTailwind.
- Integrar geolocalització per al filtrat de proximitat.
- Implementar accions de favorits ("M'interessa") amb feedback immediat.

**Non-Goals:**
- No s'implementa el mapa de seients en aquesta tasca (pertany a S2.4).
- No s'implementa la passarel·la de pagament ni el checkout.

## Decisions

1. **Nuxt 4 + Composition API**: S'utilitza `<script setup>` i Nuxt 4 per a una millor reactivitat i estructura de directoris moderna.
2. **Pinia 3 Setup Store**: S'implementa `useEventsStore` per gestionar la llista d'esdeveniments, el filtrat i els favorits de forma centralitzada.
3. **Optimistic UI per a Favorits**: Quan l'usuari clica "M'interessa", l'estat canvia immediatament al frontend abans de la confirmació de l'API (AgentPinia).
4. **Tailwind CSS 4 + DICE Style**: Ús de classes utilitàries per aconseguir el look & feel neó sobre fons negre.
5. **Geolocalització del Navegador**: S'utilitza l'API `navigator.geolocation` per obtenir les coordenades de l'usuari i enviar-les al backend per al filtrat.

## Risks / Trade-offs

- [Risk] L'usuari denega el permís de geolocalització → Mitigació: Mostrar tots els esdeveniments per defecte i un avís opcional.
- [Risk] Latència en la càrrega d'imatges de Ticketmaster → Mitigació: Utilitzar esquelets de càrrega (skeletons) i imatges optimitzades si és possible.
- [Risk] Inconsistència d'estat entre dispositius → Mitigació: Sincronització via API al carregar la pàgina (AuthStore + EventsStore).
