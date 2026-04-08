## Context

El projecte TR3 TicketMaster té implementada la cua virtual (The Gatekeeper). El problema és que quan un usuari fa clic a "Comprar" a la Landing, sempre es redirigeix a la pàgina de cua (waiting-room), independentment de si hi ha gent a la cua o no.

Quan la cua està buida (0 persones esperant), l'usuari no hauria d'entrar a la cua - hauria d'accedir directe a l'event.

## Goals / Non-Goals

**Goals:**
- Implementar bypass de cua quan la cua està buida (queueSize = 0 o < thresholdN)
- Quan no hi ha ningú esperant, l'usuari va directe a la Landing/Event

**Non-Goals:**
- Canviar la lògica interna del Gatekeeper
- Modificar el sistema de turn tokens

## Decisions

1. **Solució al Frontend (EventCard.vue)** - Quan l'usuari fa clic a "Comprar":
   - Primer verificar si la cua està buida (feu crida API o use queue store)
   - Si queueSize = 0 o < thresholdN → anar directe a Landing (o página de l'event)
   - Si hi ha gent a la cua → anar a waiting-room

2. **Verificació de l'estat de la cua** - Abans de redirigir, verificar:
   - Fer crida a API de Laravel o al servei de Node.js per obtenir queueSize
   - Alternativament, usar el queue store si ja té les dades

3. **Flux correcte**:
   ```
   Usuari click "Comprar" 
   → Verificar estat cua (API) 
   → Si buida:anar a Landing/Event 
   → Si plena:anar a Waiting Room
   ```

4. **Seguretat**: Even if bypassing queue, still require authentication.

## Risks / Trade-offs

- [Risk] API no respon → [Mitigació] Per defecte anar a cua (com ara)
- [Risk] Race condition (cua es buida entre verificació i accés) → [Mitigació] El propi Gatekeeper validarà l'accés