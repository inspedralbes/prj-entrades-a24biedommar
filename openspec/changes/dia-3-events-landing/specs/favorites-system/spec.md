## ADDED Requirements

### Requirement: Favorites System
El sistema SHALL permetre als usuaris guardar esdeveniments com a favorits ("M'interessa") i persistir-los a la base de datos PostgreSQL.

#### Scenario: Afegir event a favorits
- **WHEN** usuari fa clic a "M'interessa" en un event
- **THEN** sistema guarda la relació usuari-event a la taula favorits de PostgreSQL

#### Scenario: Treure event de favorits
- **WHEN** usuari fa clic a "Ja no m'interessa" en un event favoritat
- **THEN** sistema elimina la relació de la taula favorits

#### Scenario: Veure llista de favorits
- **WHEN** usuari autenticat accedeix al seu perfil
- **THEN** mostra la llista d'esdeveniments guardats com a favorits

#### Scenario: Verificar si event es favoritat
- **WHEN** usuari visualitza un event
- **THEN** sistema mostra l'estat "M'interessa" segons si esta guardat a favorits

#### Scenario: Favorits només per usuaris autenticats
- **WHEN** usuari no autenticat intenta afegir favorits
- **THEN** sistema redirigeix a la pàgina de login