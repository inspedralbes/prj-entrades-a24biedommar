## ADDED Requirements

### Requirement: Interfície de la Landing Page
El sistema SHALL mostrar una pàgina principal amb fons negre i estètica DICE que contingui una llista d'esdeveniments.

#### Scenario: Visualització inicial de la landing
- **WHEN** l'usuari accedeix a la URL arrel (`/`)
- **THEN** el sistema mostra el títol de la plataforma i un grid d'esdeveniments

### Requirement: Component EventCard
Cada esdeveniment SHALL visualitzar-se en una targeta amb el títol, data, imatge i botons d'acció ("M'interessa" i "Comprar").

#### Scenario: Detalls de la targeta d'esdeveniment
- **WHEN** es carrega la llista d'esdeveniments
- **THEN** cada targeta mostra la imatge de l'esdeveniment, el nom i un botó rosa neó per a la compra

### Requirement: Filtre de Proximitat UI
El sistema SHALL oferir un control (slider o input) per permetre a l'usuari ajustar el radi de cerca d'esdeveniments.

#### Scenario: Ajust del radi de proximitat
- **WHEN** l'usuari canvia el valor del radi al selector
- **THEN** el sistema actualitza la llista d'esdeveniments segons la nova distància
