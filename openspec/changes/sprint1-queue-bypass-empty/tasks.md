# Sprint 1: Correcció Bypass Cua Buida

## Problema

Quan un usuari fa clic a "Comprar" a la Landing (EventCard.vue), sempre es redirigeix a la pàgina de cua (waiting-room), fins i tot quan no hi ha ningú a la cua (queueSize = 0). Això és incorrecte - si la cua està buida, l'usuari hauria d'anar directe a l'event.

## Tasca: Implementar Bypass de Cua Buida

### Passos d'Implementació

- [x] 1. Modificar EventCard.vue (goToQueue) - Implementada lògica de bypass
- [x] 2. API per obtenir estat de cua - Creat endpoint a backend-realtime (index.js)

### branca: `fix-queue-bypass-empty`

### Tècnologies
- Nuxt 4 (frontend)
- Node.js (backend-realtime)

---

## Notes d'Implementació

- La solució més neta és fer la verificació al frontend abans de redirigir
- Si la cua està buida, l'usuari pot anar directe a la pàgina de l'event (Sprint 2 - mapa de seients)
- Mantenir autenticació requerida per comprar
- El flux ha de ser: Login → Verificar Cua → (Bypass si buida) → Event / (Cua si plena) → Waiting Room