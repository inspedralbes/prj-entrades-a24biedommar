## Context

El projecte TR3 TicketMaster ha implementat durant els dies 1-4 les funcionalitats bàsiques:
- Autenticació (Login/Register/Logout) amb Laravel Sanctum
- Redirecció return_to
- Store Pinia d'autenticació
- Pàgines Login/Registre amb estils DICE
- Middleware de navegació
- Controlador d'Events (llistat, detall)
- Sistema "M'interessa" (favorits)
- Landing amb cartellera
- The Gatekeeper (Node.js + Socket.IO)
- Redis pub/sub bridge
- Cua Virtual (Waiting Room)

Cal integrar tots aquests components i verificar que funcionen correctament.

## Goals / Non-Goals

**Goals:**
- Integrar flux complet: Login → Cua → Landing
- Verificar redirecció return_to funciona correctament
- Verificar connexió WebSocket de la cua
- Verificar integració amb API d'esdeveniments
- Verificar sistema de favorits
- Executar tests de totes les funcionalitats del Sprint 1
- Verificar codi segons estàndards dels agents

**Non-Goals:**
- Implementar noves funcionalitats
- Modificar arquitectura existent
- Optimitzar rendiment (només verificar funcionament)

## Decisions

1. **Flux d'integració** - Login authentic → return_to redirect → Cua (si escau) → Landing
   - Agent Laravel: `/Agents/backend/AgentLaravel.md`
   - Agent Nuxt: `/Agents/frontend/AgentNuxt.md`

2. **Verificació de codi** - Tots els fitxers implementats han de seguir les directrius dels agents:
   - **AgentLaravel**: PHP 8.3+, Laravel 13, PostgreSQL, estil sense ternaris, comentaris en català
   - **AgentNuxt**: Nuxt 4, Vue 3, Pinia, Tailwind CSS, estils DICE (fons negre, #FF0055, #00F0FF)
   - **AgentNode**: Node.js 24, JavaScript ES6+, Socket.IO
   - **AgentRedis**: Redis 8.6.1 per pub/sub

3. **Branques** - Crear dues branques noves:
   - `Tasca5-S14-Integration` per S1.14
   - `Tasca5-S15-Verification` per S1.15

4. **Testeig** - Verificar:
   - API endpoints de Laravel
   - Pàgines de Nuxt
   - WebSocket de la cua
   - Flux d'autenticació complet
   - Estils DICE

## Risks / Trade-offs

- [Risk] Flux de redirecció no funciona → [Mitigació] Verificar middleware i store de Pinia
- [Risk] WebSocket no connecta → [Mitigació] Verificar configuració Socket.IO client i servidor
- [Risk] API Events no respon → [Mitigació] Verificar integració Ticketmaster i caché Redis