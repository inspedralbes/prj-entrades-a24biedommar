## ADDED Requirements

### Requirement: Ticketmaster Events List
El sistema SHALL obtenir el llistat d'esdeveniments des de l'API de Ticketmaster i mostrar-los a la pàgina Landing.

#### Scenario: Obtenir llistat d'esdeveniments
- **WHEN** usuari accedeix a la pàgina Landing
- **THEN** sistema crida l'API de Ticketmaster i mostra la llista d'esdeveniments

#### Scenario: Mostrar informació de l'esdeveniment
- **WHEN** sistema rep dades d'un esdeveniment de Ticketmaster
- **THEN** mostra: nom, data, imatge, ubicació, preu mínim

#### Scenario: Error en obtenir esdeveniments
- **WHEN** l'API de Ticketmaster no respon
- **THEN** mostra missatge d'error i intenta carregar des de caché

#### Scenario: Cache d'esdeveniments
- **WHEN** es fan múltiples peticions a Ticketmaster
- **THEN** les respostes es guarden a Redis per reduir crides externes