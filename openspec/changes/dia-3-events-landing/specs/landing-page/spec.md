## ADDED Requirements

### Requirement: Landing Page
El sistema SHALL mostrar la pàgina Landing (Cartellera) amb tots els esdeveniments disponibles, filtre de proximitat i estils DICE.

#### Scenario: Renderitzar pàgina Landing
- **WHEN** usuari accedeix a la ruta /
- **THEN** mostra la cartellera d'esdeveniments en format grid

#### Scenario: Grid d'esdeveniments
- **WHEN** sistema carrega esdeveniments
- **THEN** mostra cards en grid responsiu (3-4 columnes a desktop, 1-2 a mobil)

#### Scenario: Card d'esdeveniment
- **WHEN** sistema mostra una card d'event
- **THEN** mostra: imatge, nom, data, ubicació, preu, botó "M'interessa", botó "Comprar"

#### Scenario: Filtre de proximitat
- **WHEN** usuari activa el filtre de proximitat
- **THEN** només mostra esdeveniments dins del radi seleccionat

#### Scenario: Botó Comprar
- **WHEN** usuari fa clic a "Comprar"
- **THEN** redirigeix a la cua virtual (The Gatekeeper) o a la pàgina de detall

#### Scenario: Estil DICE - Fons negre
- **WHEN** usuari visualitza la pàgina Landing
- **THEN** fons és negre (#000000) amb cards amb fons negre

#### Scenario: Estil DICE - Botons neó
- **WHEN** usuari visualitza botons
- **THEN** botó "Comprar" és neó (#FF0055), botó "M'interessa" és Electric Blue (#00F0FF)