## ADDED Requirements

### Requirement: Login/Register Pages
El sistema SHALL proporcionar pàgines de Login i Registre amb formularis funcionals i estils DICE (fons negre, botons neó #FF0055, Electric Blue #00F0FF).

#### Scenario: Renderitzar formulari Login
- **WHEN** usuari accedeix a `/login`
- **THEN** mostra formulari amb camps: email, password, botó "INICIAR SESSIÓ", enllaç a Registre

#### Scenario: Renderitzar formulari Registre
- **WHEN** usuari accedeix a `/register`
- **THEN** mostra formulari amb camps: nom, email, password, confirm_password, botó "CREAR COMPTE", enllaç a Login

#### Scenario: Validar formulari Login buit
- **WHEN** usuari envia formulari login buit
- **THEN** mostra errors de validació (email obligatori, password obligatori)

#### Scenario: Validar formulari Registre
- **WHEN** usuari envia formulari registre amb dades invàlides
- **THEN** mostra errors: email vàlid, password mínim 8 caràcters, passwords coincideixen

#### Scenario: Login amb èxit
- **WHEN** usuari envia formulari login amb credencials correctes
- **THEN** redirigeix a ruta original (return_to) o Landing, mostra missatge d'èxit

#### Scenario: Registre amb èxit
- **WHEN** usuari envia formulari registre amb dades vàlides
- **THEN** redirigeix a Login, mostra missatge "Compte creat correctament"

#### Scenario: Login amb error
- **WHEN** usuari envia formulari login amb credencials incorrectes
- **THEN** mostra missatge d'error "Credencials incorrectes"

#### Scenario: Estil DICE - Fons negre
- **WHEN** usuari visualitza pàgina
- **THEN** fons és negre (#000000) amb contrast suficient

#### Scenario: Estil DICE - Botons neó
- **WHEN** usuari visualitza botons
- **THEN** botó primari és neó (#FF0055), botó secundari és Electric Blue (#00F0FF)