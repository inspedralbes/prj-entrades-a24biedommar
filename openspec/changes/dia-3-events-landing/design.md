## Context

Projecte TR3 TicketMaster amb sistema d'autenticació implementat. Cal implementar la visualització d'esdeveniments mitjançant la integració amb l'API de Ticketmaster, el sistema de favorits i la pàgina Landing.

## Goals / Non-Goals

**Goals:**
- Integrar API de Ticketmaster per obtenir llistat d'esdeveniments i detalls
- Implementar filtre de proximitat basat en geolocalització de l'usuari
- Crear sistema de favorits (M'interessa) persistit a PostgreSQL
- Desenvolupar pàgina Landing amb grid d'esdeveniments i estils DICE

**Non-Goals:**
- No s'implementa sistema de reserves (només visualització)
- No s'implementa pagament
- No s'implementa autenticació d'usuari per veure events (accessible a tothom)

## Decisions

1. **API Ticketmaster**: Usar API externa de Ticketmaster per obtenir dades d'esdeveniments. Cal obtenir API key des de variables d'entorn.

2. **Caché Redis**: Emmagatzemar resposta de Ticketmaster a Redis per reduir crides externes.

3. **Favorits a PostgreSQL**: Taula `favorits` amb relació usuari-event per persistir preferències.

4. **Geolocalització**: Utilitzar latitud/longitud de l'usuari per filtrar events propers. Radius configurable.

5. **Frontend JavaScript**: Tot el codi frontend en JavaScript (no TypeScript) seguint AgentJavaScript.

## Risks / Trade-offs

- [Risk] API Ticketmaster no disponible → Mitigació: Usar caché Redis i missatge d'error a UI
- [Risk] Geolocalització no disponible → Mitigació: Permetre introduir ubicació manual o usar valor per defecte
- [Risk] many requests a API externa → Mitigació: Rate limiting i caché