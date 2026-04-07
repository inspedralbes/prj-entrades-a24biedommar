## Why

Per completar la funcionalitat de visualització d'esdeveniments, cal implementar la integració amb l'API de Ticketmaster per obtenir el llistat d'esdeveniments i els detalls de cada un, el sistema de favorits per permetre als usuaris guardar esdeveniments que els interessen, i la pàgina Landing per mostrar la cartellera als usuaris.

## What Changes

- **S1.8**: Integrar API de Ticketmaster per obtenir events (llistat i detall) amb filtre de proximitat (Geolocation)
- **S1.9**: Sistema "M'interessa" (favorits) per guardar preferències d'usuari a PostgreSQL
- **S1.10**: Crear pàgina Landing amb grid d'esdeveniments, filtre de proximitat i botons "Comprar"

## Capabilities

### New Capabilities
- `ticketmaster-integration`: Integració amb API externa de Ticketmaster per obtenir esdeveniments
- `event-detail-api`: API per obtenir detall d'un event des de Ticketmaster
- `proximity-filter`: Filtre de proximitat basat en geolocalització de l'usuari
- `favorites-system`: Sistema de favorits per guardar preferències d'usuari a PostgreSQL
- `landing-page`: Pàgina Landing amb cartellera d'esdeveniments i estils DICE

### Modified Capabilities
- (cap)

## Impact

- **API Externa**: Ticketmaster API per dades d'esdeveniments
- **Backend**: Nou controlador EventController, servei de favorits, base de dades PostgreSQL
- **Frontend**: Nova pàgina Landing, store d'esdeveniments, components de visualització