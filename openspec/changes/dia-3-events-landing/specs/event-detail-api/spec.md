## ADDED Requirements

### Requirement: Event Detail
El sistema SHALL obtenir els detalls complets d'un esdeveniment des de l'API de Ticketmaster.

#### Scenario: Obtenir detall d'un event
- **WHEN** usuari fa clic a un event de la llista
- **THEN** sistema mostra la pàgina de detall de l'esdeveniment

#### Scenario: Informació mostrada al detall
- **WHEN** sistema mostra el detall d'un event
- **THEN** mostra: imatge gran, descripció completa, ubicació, data/hora, preus per zona, enllaç a Ticketmaster

#### Scenario: Event no trobat
- **WHEN** l'ID de l'event no existeix a Ticketmaster
- **THEN** mostra missatge d'error i enllaç a la Landing