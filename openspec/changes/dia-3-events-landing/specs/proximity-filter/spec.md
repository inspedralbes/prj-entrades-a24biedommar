## ADDED Requirements

### Requirement: Proximity Filter
El sistema SHALL permetre filtrar esdeveniments per proximitat segons la ubicació de l'usuari.

#### Scenario: Obtenir ubicació de l'usuari
- **WHEN** usuari carrega la pàgina Landing
- **THEN** sistema demana permís per obtenir geolocalització

#### Scenario: Filtrar per proximitat
- **WHEN** usuari té geolocalització activa
- **THEN** mostra només esdeveniments dins del radi definit (per defecte 50km)

#### Scenario: Configurar radi de cerca
- **WHEN** usuari canvia el radi de cerca
- **THEN** esdeveniments es filtren segons el nou radi

#### Scenario: Ubicació no disponible
- **WHEN** usuari no permet geolocalització
- **THEN** mostra tots els esdeveniments sense filtre de proximitat