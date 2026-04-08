## Why

Per completar el Sprint 1 de la plataforma TicketMaster, cal implementar la pàgina Landing (Cartellera) que permeti als usuaris visualitzar els esdeveniments disponibles, filtrar-los per proximitat i gestionar els seus favorits ("M'interessa"). Aquesta pàgina és el punt d'entrada principal per a la venda d'entrades.

## What Changes

- Implementació de la pàgina principal (`index.vue`) amb un grid d'esdeveniments.
- Creació d'un store de Pinia per gestionar l'estat dels esdeveniments i favorits al frontend.
- Integració de components de visualització d'esdeveniments (`EventCard`).
- Implementació del filtre de proximitat basat en la geolocalització de l'usuari.
- Integració dels botons d'acció "M'interessa" (favorits) i "Comprar" (redirecció a la cua virtual).

## Capabilities

### New Capabilities
- `landing-page-ui`: Interfície d'usuari per a la cartellera d'esdeveniments amb estils DICE.
- `frontend-events-logic`: Lògica de frontend per a l'obtenció, filtrat i gestió de favorits d'esdeveniments.

### Modified Capabilities
- (cap)

## Impact

- **Frontend**: Nova pàgina `index.vue`, nou store `events.js`, nous components `EventCard`.
- **Estils**: Ús de Tailwind CSS amb el sistema de disseny DICE (neó, fons negre).
- **Integració**: Connexió amb les APIs de backend d'esdeveniments i favorits.
