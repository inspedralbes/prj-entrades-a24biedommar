# 🎫 Especificacions del Projecte: Plataforma de Venda d'Entrades TR3 DAW

Aquest document detalla les funcionalitats, rols i lògica de negoci per a la plataforma de venda d'entrades d'alta demanda, seguint una estètica inspirada en **DICE** i una robustesa tècnica similar a **Ticketmaster**.

---

## 🔐 1. Lògica d'Autenticació i Redirecció

El sistema garanteix la seguretat i la traçabilitat de les reserves mitjançant regles de sessió estrictes.

### 1.1. Protecció de Rutes
- **Pàgines Públiques:** Només la Cartellera d'Esdeveniments (Landing) és accessible sense autenticació.
- **Pàgines Protegides:** Tota la resta del flux (Cua, Mapa, Checkout, Entrades, Admin) requereix un compte actiu.

### 1.2. Redirecció Intel·ligent (return_to)
- Si un usuari intenta accedir a una funcionalitat protegida (exemple: clicar en "Comprar" o "M'interessa"):
    1. El sistema desa la URL o acció pendent en un paràmetre `return_to`.
    2. Es redirigeix a la pàgina de Login/Registre amb un missatge informatiu: *"Inicia sessió per reservar les teves entrades"*.
    3. Un cop completat el login/registre amb èxit, el sistema llegeix el paràmetre `return_to` i retorna l'usuari al seu destí original o executa l'acció pendent.

### 1.3. Rols d'Usuari
- **Usuari Client:**
    - Accés a compra, cues virtuals, mapes real-time i gestió d'entrades pròpies.
    - **Prohibit:** Accés a qualsevol ruta sota `/admin` (Error 403).
- **Administrador:**
    - Accés total al Dashboard Live, Backoffice i eines de suport.
    - Pot accedir a les rutes de client per realitzar proves de flux.

---

## 🚀 2. Lògica de Concurrència i Cua Virtual (The Gatekeeper)

Gestió de càrrega dinàmica per al servidor de Sockets i la base de dades.

### 2.1. Llindar de Trànsit ($N$)
- Cada esdeveniment té un paràmetre $N$ (personalitzable per l'admin).
- Representa el nombre màxim d'usuaris que poden estar simultàniament al mapa de seients.

### 2.2. Activació Dinàmica de la Cua
- La cua s'activa automàticament quan: `Usuaris Actius al Mapa > N`.
- Els següents usuaris que intentin entrar seran retinguts a "The Gatekeeper".

### 2.3. Identificació Obligatòria
- Per entrar a la cua, l'usuari **ha d'estar loguejat**.
- Això bloqueja bots i garanteix que cada lloc a la cua és una persona real amb un ID únic.

---

## 👤 3. ROL: Usuari Client (Comprador)

### Pàgina 1: Cartellera d'Esdeveniments (Landing)
*Pàgina pública d'entrada.*
- **Filtre per Proximitat:** Ús de la **Geolocation API** per ordenar els esdeveniments més propers a l'usuari.
- **Sistema "M'interessa":** Permet marcar favorits. Requereix login; si no n'hi ha, redirigeix i torna automàticament per marcar-lo.
- **Accés Intel·ligent:** El botó "Comprar" valida la sessió. Si està loguejat, el servidor de sockets comprova la capacitat actual i decideix si l'envia a la **Cua** o al **Mapa**.

### Pàgina 2: Cua Virtual (The Gatekeeper)
*Gestió d'espera ordenada.*
- **Sessió Persistent:** Si l'usuari perd la connexió o refresca, el sistema el reconeix pel seu `user_id` i manté la seva posició exacta.
- **Token de Torn:** Es genera un **JWT** vinculat a l'ID d'usuari. Aquest token és intransferible i expira si l'usuari no accedeix al mapa quan li toca.

### Pàgina 3: Selecció de Seients (Mapa Real-Time)
*Interacció en viu mitjançant Sockets.*
- **Reserva Nominal:** En clicar un seient, queda bloquejat a la base de dades i a Redis amb el `user_id`. Cap altre usuari el pot seleccionar durant el temps de reserva.
- **Millor Seient / Filtres:** Cercador intel·ligent per tipus de zona o preu.

### Pàgina 4: Procés de Pagament (Checkout)
*Finalització de la compra.*
- **Dades Pre-omplertes:** El formulari agafa automàticament el nom i email del compte.
- **Validació de Propietat:** El servidor verifica que el `user_id` que realitza el pagament és exactament el mateix que té la reserva activa del seient al mapa.

### Pàgina 5: Les meves Entrades (Estil DICE)
*L'experiència post-compra.*
- **Seguretat QR:** El codi QR és dinàmic o només es visible si la sessió activa coincideix amb el `owner_id`.
- **Transferència d'Entrada:** Flux de traspàs segur. El receptor ha de tenir un compte actiu per acceptar l'entrada.

---

## 🛠️ 4. ROL: Administrador (Gestor del Sistema)

### Pàgines 6, 7 i 8: Administració
- **Control d'Accés:** Protecció robusta de rutes. Redirecció a Landing amb 403 per a clients.
- **Login d'Emergència:** Si un admin fa login a la pantalla general, el sistema detecta el seu rol i l'envia directament al Dashboard.
- **Dashboard Live:** Visualització en temps real d'usuaris a la cua, usuaris al mapa i vendes.
- **Accions d'Admin (Logades):**
    - **Botó de Pànic:** Aturar la venda instantàniament.
    - **Canvi de $N$:** Ajustar el llindar de la cua en viu.
    - **Pre-sale Control:** Gestió de llistes blanques.
    - *Totes les accions guarden un historial amb el timestamp i l'ID de l'admin.*

---

## 🎨 5. Especificacions de Disseny (UI/UX)

- **Estètica:** Inspirada en l'app DICE.
- **Paleta:** Fons negre absolut, tipografia sans-serif minimalista.
- **Accents:** Botons Rosa Neó (`#FF0055`) de gran tamany.
- **Interaccions:** Animacions suaus en els canvis de posició de la cua i feedback immediat en la selecció de seients.
