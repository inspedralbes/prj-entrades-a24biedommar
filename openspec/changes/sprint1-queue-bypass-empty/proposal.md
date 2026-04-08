## Why

El problema és que quan un usuari fa clic a "Comprar" a la Landing, sempre es redirigeix a la pàgina de cua (waiting-room), fins i tot quan no hi ha ningú a la cua i el threshold N és 0. Això genera una experiència d'usuari pobra - l'usuari ha d'esperar a la cua quan podria accedir directament a l'event.

## What Changes

- **Fix**: Modificar el flux perquè quan la cua estigui buida (queueSize < thresholdN), l'usuari vagi directe a la Landing/Event en comptes d'entrar a la cua.
- **Backend**: El Gatekeeper ha de permetre accés directe quan la cua està buida.
- **Frontend**: El botó "Comprar" ha de verificar l'estat de la cua abans de redirigir.

## Capabilities

### New Capabilities
- `queue-bypass-empty`: Quan la cua està buida, bypass directe a l'event

### Modified Capabilities
- `EventCard`: Botó "Comprar" amb lògica de bypass
- `waiting-room`: Redirigir a landing si no cal esperar

## Impact

- **Frontend**: Canvi menor al flux de navegació del botó Comprar
- **UX**: Millora - usuaris no esperen a la cua si no cal