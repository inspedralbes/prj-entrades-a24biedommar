# 🎨 Design System Specification: TR3-ENTRADES (Dice Style)

Aquest document detalla la identitat visual, els components d'interfície i l'experiència d'usuari (UX) per a la plataforma **TR3-ENTRADES**, seguint una estètica minimalista d'alt contrast inspirada en l'app **DICE**.

---

## 1. Identitat Visual i Fonaments

L'objectiu és una interfície que transmeti urgència, exclusivitat i modernitat. L'enfocament és **"Mobile-First"** amb elements visuals de gran format.

### A. Paleta de Colors (High Contrast)
- **Primary Black**: `#050505` (Fons principal per a totes les pàgines).
- **Pure White**: `#FFFFFF` (Text principal, icones i targetes destacades).
- **Neon Pink (Dice)**: `#FF0055` (Accions principals, botons de compra, "Botó de Pànic").
- **Electric Blue**: `#00F0FF` (Selecció de seients, estat "Les Meves Entrades", geolocalització activa).
- **Success Green**: `#00FF66` (Seients disponibles, pagament confirmat, estats positius).
- **Muted Gray**: `#1A1A1A` (Seients ocupats, fons de camps d'entrada, elements secundaris).

### B. Tipografia
- **Principal (Headings)**: `Syne` o `Archivo Black`. Pes: **800-900**. (Per a títols en MAJÚSCULES impactants).
- **Cos (Body)**: `Inter` o `Montserrat`. Pes: **400** (Regular), **600** (Semi-bold).
- **Mono (Dades/Timer)**: `JetBrains Mono` o `Space Mono`. (Per a comptadors de cua i dades tècniques).

### C. UI Components
- **Border Radius**: `12px` (Modern però amb identitat).
- **Borders**: `2px solid #FFFFFF` per a elements destacats (estil brutalista minimalista).
- **Shadows**: Cap. S'utilitzen blocs de color sòlids per mantenir la nuesa visual.

---

## 2. Especificacions Detallades per Pàgina

### 🖼️ 2.1. Cartellera (Landing)
- **Layout**: Grid de 2 columnes en mòbil.
- **Visuals**: Targetes *full bleed* (sense marges laterals), overlay amb tipografia `Syne`.
- **Elements Clau**:
    - Icona de cor (`#FF0055`) per a favorits.
    - Badge de Proximitat (`#00F0FF`).
    - Badge "Últimes Entrades" amb efecte de parpelleig (flash) per crear urgència.

### ⏳ 2.2. Cua Virtual (The Waiting Room)
- **Atmosfera**: Fons negre pur per centrar l'atenció.
- **Comptador**: Número gegant en `JetBrains Mono` amb efecte de *flip* (com els rellotges antics).
- **Indicadors**:
    - Barra de progrés en `#FF0055`.
    - Un puntet blau (`#00F0FF`) orbitant el comptador per indicar activitat de la connexió.

### 📍 2.3. Selecció de Seients (The Map)
- **Entorn**: Fons `#121212` lleugerament menys fosc per donar profunditat.
- **Seients (Cercles de 10px)**:
    - **Blanc**: Disponible.
    - **Gris Fosc**: Ocupat/No disponible.
    - **Blau Elèctric + Glow**: Seleccionat per l'usuari actual.
- **Interacció**: *Slider* de preu minimalista a la part inferior.

### 💳 2.4. Checkout (Pasarela de Pagament)
- **Disseny**: Estructura de columna neta i vertical.
- **Resum**: Bloc amb vora blanca de `2px`.
- **Toggle**: Estil iOS en color `#00FF66`.
- **CTA**: Botó gegant `#FF0055` amb el text **"PAGAR ARA"** en negre.

### 🎫 2.5. Les meves Entrades
- **Estètica**: Simula una entrada física digitalitzada.
- **QR**: Codi QR negre d'alt contrast sobre fons blanc (per facilitar l'escaneig).
- **Funcions**: *Bottom Sheet* emergent per transferir entrades a amics.
- **Mini Mapa**: Renderitzat en Blanc i Negre de la zona del recinte.

---

## 3. Panell d'Administració

### 📈 3.1. Dashboard Live
- **Gràfics**: Estil neó en `#00FF66` i `#FF0055`.
- **Eines**: Termòmetre visual de la càrrega de la cua.
- **Emergència**: Botó de Pànic vermell amb ratlles grogues d'avís (estil perill industrial).

### ⚙️ 3.2. Backoffice
- **Formularis**: Inputs amb fons `#1A1A1A` i una única línia inferior blanca que es torna blava (`#00F0FF`) al fer focus.
- **Pre-sale**: Toggle específic amb marc daurat o groc neó per destacar la funcionalitat VIP.

---

## ⚡ 4. Estats de Feedback (Sockets & Real-Time)

La interfície respon a l'instant als canvis de l'ecosistema:

- **Seient Reservat**: Efecte de *fade out* cap a gris fosc per indicar que ja no es pot clicar.
- **Seient Alliberat**: Efecte *pop* (petita expansió) tornant a blanc.
- **La Cua Avança**: Brillantor (glow) verda fugaç sobre el número del torn.
- **Error del Sistema**: Toast flotant en `#FF0055` amb el missatge impactant: **"MASSA TARD!"**.
