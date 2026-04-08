# Sprint 1 Day 5: Integració + Verificació

## Tasca S1.14: Integrar Login → Cua → Landing

### Descripció
Integrar el flux complet des de l'autenticació fins a la cua virtual i la landing page.

### Passos d'Implementació

1. **Verificar flux de redirecció return_to**
   - Revisar que el middleware `return_to` guardi la URL destí abans de login
   - Verificar que after login redirigeix a la URL guardada

2. **Integrar Login amb Cua Virtual**
   - Després de login, verificar si l'usuari ha d'anar a la cua
   - Si té turn_token vàlid → anar directament a Landing/Event
   - Si no té token → anar a la Cua Virtual

3. **Integrar Cua amb Landing**
   - Quan l'usuari arriba a posició 1 a la cua, rebre turn_token
   - Redirigir automàticament a la Landing oEvent seleccionat

4. **Verificar integració del Store Pinia**
   - `authStore` ha de gestionar estat de cua
   - Guardar `turn_token`, `queue_position` al store

### branca: `Tasca5-S14-Integration`

### Tècnologies
- Laravel 13 (API redirecció)
- Nuxt 4 (middleware, store Pinia)
- Socket.IO (Cua)

---

## Tasca S1.15: Verificació i testing Sprint 1

### Descripció
Testejar totes les funcionalitats implementades durant el Sprint 1 i verificar que el codi segueix els estàndards dels agents.

### Passos d'Implementació

1. **Verificació de codi segons Agents**
   - Revisar tots els fitxers PHP de Laravel seguint AgentLaravel:
     - Sense ternaris, ús if/else
     - Comentaris en català per blocs
     - Models manuals a app/Models/
   - Revisar tots els fitxers Vue/Nuxt seguint AgentNuxt:
     - Script setup
     - Estils DICE (fons negre, #FF0055, #00F0FF)
     - Pinia per estat
   - Revisar codi Node.js seguint AgentNode

2. **Testing de funcionalitats**
   - Test API autenticació (register, login, logout, usuari)
   - Test redirecció return_to
   - Test API Events (llistat, detall)
   - Test sistema favorits
   - Test Landing page
   - Test Cua Virtual (WebSocket, posició, turn token)
   - Test middleware de navegació

3. **Verificació d'integració**
   - Flux complet: Register → Login → Cua → Landing → Event
   - WebSocket funciona correctament
   - Redis pub/sub opera correctament

4. **Documentar resultats**
   - Crear informe de testing
   - Identificar issues trobats

### branca: `Tasca5-S15-Verification`

### Tècnologies
- Laravel 13 (Backend)
- Nuxt 4 (Frontend)
- Node.js (Cua)
- Socket.IO
- Redis

---

## Notes d'Implementació

- S1.14 ha de crear branca `Tasca5-S14-Integration`
- S1.15 ha de crear branca `Tasca5-S15-Verification`
- Cada tasca és independent però S1.14 s'ha de completar abans de provar la integració completa
- Verificar que tots els fitxers segueixen els estàndards dels agents abans de marcar com a completat