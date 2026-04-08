## Context

The TicketMaster application needs a virtual queue system ("The Gatekeeper") to manage user access to high-demand events. Currently, users can access the seat selection map directly, causing race conditions and system overload when many users compete for limited seats. The queue system will manage user flow by giving each user a turn token and position in queue.

Current architecture:
- Laravel 13 API handles authentication, events, seat management
- Nuxt 4 frontend with Pinia state management
- PostgreSQL for data persistence
- Redis available for caching and pub/sub

## Goals / Non-Goals

**Goals:**
- Implement Node.js Gatekeeper service with Socket.IO for real-time queue management
- Create Redis pub/sub bridge between Laravel and Node.js for queue state synchronization
- Build Waiting Room frontend with real-time position tracking and countdown animation

**Non-Goals:**
- Seat reservation logic (Sprint 2)
- Payment processing
- Admin dashboard queue management
- Persistence of queue state across server restarts (in-memory is acceptable)

## Decisions

1. **Node.js service architecture** - Per què: Node.js amb JavaScript (ES6+) seguint AgentNode.js, Socket.IO per WebSocket.
   - Agent: `/Agents/backend/AgentNode.md`
   - Agent Sockets: `/Agents/backend/AgentSockets.md`
   - Agent Redis: `/Agents/backend/AgentRedis.md`
   - Agent JavaScript: `/Agents/frontend/AgentJavaScript.md`

2. **Turn Token JWT com TicketMaster** - El turn token funciona com a entrada directa al mapa de seients. Quan l'usuari arriba a posició 1, rep el token que li permet accedir directament al seat-map.
   - El token conté: user_id, event_id, issued_at, expires_at, token_type="turn", turn_token=true
   - El token té TTL (ex: 5 minuts) per evitar especulació de torns

3. **Gestió de posicions decreixent** - Quan un usuari davant compra (surt de la cua), tots els usuaris posteriors avancen una posició automàticament. Això millora l'experiència: si esperes i la gent davant compra, la teva espera es reduïx.

4. **Threshold N per event** - Cada event té el seu propi llindar N configurable:
   - Exemple: Event A (popular) → N=50 (50 usuaris al mapa, resta a cua)
   - Exemple: Event B (menys demandat) → N=200 (gairebé tots directes al mapa)
   - El threshold es consulta des de Laravel (taula events o configuració)

5. **Redis pub/sub per Laravel → Node.js** - Agent Laravel: `/Agents/backend/AgentLaravel.md`
   - Agent Redis: `/Agents/backend/AgentRedis.md`

6. **Frontend Nuxt 4 amb JavaScript** - Agent Nuxt: `/Agents/frontend/AgentNuxt.md`
   - Agent Pinia (gestió estat): `/Agents/frontend/AgentPinia.md`
   - Estil DICE: fons negre, neon pink #FF0055, electric blue #00F0FF

## Risks / Trade-offs

- **[Risk]** Node.js service as single point of failure → **[Mitigation]** Implement graceful degradation - if Node.js is down, show "temporarily unavailable" message; queue positions can be recalculated from Redis on restart
- **[Risk]** JWT tokens could be reused after expiry → **[Mitigation]** Store active turn tokens in Redis with TTL matching JWT expiry; validate on each WebSocket connection
- **[Risk]** Redis pub/sub messages could be lost → **[Mitigation]** Node.js maintains local queue state; periodic reconciliation with Redis
- **[Risk]** WebSocket reconnection causing position jumps → **[Mitigation]** Client stores last known position; smooth transition animations; server sends current position on reconnect