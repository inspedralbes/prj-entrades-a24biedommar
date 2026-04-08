## ADDED Requirements

### Requirement: Store de Pinia per a Esdeveniments
El sistema SHALL utilitzar una store de Pinia per gestionar l'estat dels esdeveniments, la geolocalització de l'usuari i la llista de favorits.

#### Scenario: Càrrega d'esdeveniments
- **WHEN** la store d'esdeveniments s'inicialitza o rep una petició de refresh
- **THEN** el sistema fa un fetch a l'API `/api/events` amb la latitud i longitud actual de l'usuari

### Requirement: Filtre de Proximitat Lògica
El sistema SHALL permetre el filtrat d'esdeveniments basat en el radi indicat per l'usuari en quilòmetres.

#### Scenario: Filtrat d'esdeveniments propers
- **WHEN** l'usuari canvia el radi de proximitat
- **THEN** el sistema re-sol·licita els esdeveniments a l'API passant el nou valor de `radius`

### Requirement: Gestió de Favorits ("M'interessa")
El sistema SHALL permetre als usuaris marcar i desmarcar esdeveniments com a favorits, persistint el canvi a l'API de backend.

#### Scenario: Afegir event a favorits
- **WHEN** l'usuari clica el botó "M'interessa" d'un event no favorit
- **THEN** el sistema realitza una mutació optimista al frontend i envia una petició `POST /api/favorites`

#### Scenario: Eliminar event de favorits
- **WHEN** l'usuari clica el botó "M'interessa" d'un event que ja és favorit
- **THEN** el sistema realitza una mutació optimista al frontend i envia una petició `DELETE /api/favorites/{eventId}`
