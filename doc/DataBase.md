# Base de dades — Font de veritat (TR3DAW 2025-26)

Aquest document és la **font de veritat** per al model de dades del projecte **Plataforma de Venda d’Entrades en Temps Real**. Qualsevol canvi d’esquema, regla de negoci o integració amb serveis externs ha de reflectir-se aquí abans d’implementar-se al codi o als scripts SQL.

---

## Context del projecte

El sistema és una plataforma d’**alta demanda**, comparable en càrrega i expectatives d’experiència a serveis com TicketMaster: molts usuaris intenten reservar o comprar els mateixos seients simultàniament.

L’arquitectura prevista combina:

- **Node.js** com a capa d’aplicació per gestionar peticions HTTP, lògica de negoci i coordinació amb la base de dades.
- **Socket.IO** per emetre i rebre esdeveniments en temps real (per exemple, canvis d’estat dels seients, cues, confirmacions de reserva).
- **Redis** com a magatzem ràpid per a bloquejos curts, cues, sessions de reserva i coordinació entre instàncies de l’aplicació abans que l’estat definitiu quedi consolidat a **PostgreSQL**.

PostgreSQL manté la **persistència** i la **integritat referencial**; Redis i Socket.IO redueixen la contenció i permeten una UX fluida sense contradir les regles que la base de dades ha d’aplicar en el moment del compromís final (transacció atòmica).

---

## Convencions generals

- Els identificadors primaris són enters auto-generats (`SERIAL` / `BIGSERIAL` o equivalent) llevat que el projecte defineixi el contrari.
- Els imports monetaris es persisteixen amb tipus fixos (`NUMERIC` / `DECIMAL`) per evitar errors d’aritmètica en coma flotant.
- Els camps d’estat es poden modelar amb tipus `ENUM` de PostgreSQL o amb `TEXT`/`VARCHAR` amb restriccions `CHECK`, sempre que els valors permesos coincideixin exactament amb els descrits en aquest document.

---

## Estructura de taules

### `users`

**Responsabilitat:** Emmagatzemar comptes d’accés a la plataforma (clients i administradors), credencials i rols per autorització.

| Camp | Tipus PostgreSQL (orientatiu) | Restriccions | Justificació |
|------|-------------------------------|--------------|--------------|
| `id` | `SERIAL` / `INTEGER` | **PK**, NOT NULL | Identificador estable de l’usuari; clau per FK en comandes, retencions de seients i traçabilitat. |
| `name` | `VARCHAR` | NOT NULL | Nom visible o complet per a interfície i comunicacions. |
| `email` | `VARCHAR` | **UNIQUE**, NOT NULL | Identificador d’inici de sessió i contacte; ha de ser únic per evitar comptes duplicats. |
| `password` | `VARCHAR(255)` | NOT NULL | Hash de contrasenya (p. ex. bcrypt/argon2); mai emmagatzemar text pla. |
| `role` | `ENUM` o tipus equivalent (`admin`, `client`) | NOT NULL (o valor per defecte `client`) | Separa privilegis (gestió d’esdeveniments, informes) dels usuaris finals. |
| `created_at` | `TIMESTAMPTZ` | NOT NULL (o per defecte `now()`) | Auditoria i suport (fraus, incidències, analítica temporal). |

---

### `events`

**Responsabilitat:** Descriure cada esdeveniment venible (concert, partit, etc.), metadades de lloc i data, límit de compra per usuari i estat de publicació.

| Camp | Tipus PostgreSQL (orientatiu) | Restriccions | Justificació |
|------|-------------------------------|--------------|--------------|
| `id` | `SERIAL` / `INTEGER` | **PK**, NOT NULL | Identificador de l’esdeveniment; arrel de zones i seients. |
| `title` | `VARCHAR` | NOT NULL | Títol comercial per a llistats i SEO bàsic. |
| `description` | `TEXT` | NULL permès | Detall opcional per a la fitxa de l’esdeveniment. |
| `event_date` | `TIMESTAMPTZ` | NOT NULL | Moment en què té lloc l’esdeveniment; necessari per ordenació, filtres i tancament de vendes. |
| `venue_name` | `VARCHAR` | NOT NULL | Nom del recinte per a informació al client i tiquets. |
| `image_url` | `VARCHAR` o `TEXT` | NULL permès | URL d’imatge promocional; pot ser externa (CDN). |
| `threshold_n` | `INTEGER` | NOT NULL (o per defecte amb valor segur) | **Llindar de demanda** per activar comportaments de cua o limitació agressiva (vegeu justificació clau més avall). |
| `latitude` | `NUMERIC(10,8)` o `DOUBLE PRECISION` | NULL permès | Coordenada per mapes o informació de localització. |
| `longitude` | `NUMERIC(11,8)` o `DOUBLE PRECISION` | NULL permès | Parell amb `latitude`. |
| `is_active` | `BOOLEAN` | NOT NULL (per defecte `TRUE`) | Permet ocultar o desactivar esdeveniments sense esborrar dades històriques. |

---

### `seat_zones`

**Responsabilitat:** Agrupar seients d’un mateix esdeveniment amb **preu base** i identitat visual per zona (p. ex. pista, graderia nord).

| Camp | Tipus PostgreSQL (orientatiu) | Restriccions | Justificació |
|------|-------------------------------|--------------|--------------|
| `id` | `SERIAL` / `INTEGER` | **PK**, NOT NULL | Identificador de la zona. |
| `event_id` | `INTEGER` | **FK** → `events(id)`, NOT NULL, `ON DELETE CASCADE` (recomanat) | Vincula la zona a un esdeveniment concret. |
| `zone_name` | `VARCHAR` | NOT NULL | Etiqueta visible (ex.: «Platea»). |
| `base_price` | `NUMERIC(10,2)` | NOT NULL | Preu base de la zona; els seients hereten aquest marc per escalabilitat de preus per zona sense duplicar columnes a cada seient. |
| `color_code` | `VARCHAR` (p. ex. `#RRGGBB`) | NULL permès | Color a la interfície de selecció de seients (mapa). |

**Justificació clau — separació `seat_zones` / `seats`:**  
Modelar zones apart dels seients permet **escalar la política de preus**: es poden crear moltes files i seients sense repetir `base_price` ni regles de color per cada fila; els canvis de preu per zona s’apliquen de forma coherent. A més, facilita consultes i manteniment (p. ex. «tota la zona X passa a preu Y»).

---

### `seats`

**Responsabilitat:** Representar cada seient físic o lògic dins d’una zona, el seu estat de disponibilitat i la **retenció temporal** associada a un usuari durant el flux de compra.

| Camp | Tipus PostgreSQL (orientatiu) | Restriccions | Justificació |
|------|-------------------------------|--------------|--------------|
| `id` | `SERIAL` / `INTEGER` | **PK**, NOT NULL | Identificador únic del seient a tot el sistema. |
| `zone_id` | `INTEGER` | **FK** → `seat_zones(id)`, NOT NULL, `ON DELETE CASCADE` (recomanat) | Enllaç a la zona (preu base, esdeveniment). |
| `row_label` | `VARCHAR` | NULL permès | Identificador de fila (ex.: «A», «12») per al mapa i el tiquet. |
| `seat_number` | `INTEGER` | NULL permès | Número dins la fila. |
| `status` | `ENUM` o `VARCHAR` amb `CHECK` | NOT NULL (per defecte `available`) | Valors: `available`, `reserved`, `sold`. Estat persistent a BD per al cicle de vida del seient. |
| `held_by_user_id` | `INTEGER` | **FK** → `users(id)`, NULL permès | Usuari que **té la reserva activa** (bloqueig lògic); NULL quan no hi ha retenció vàlida. |
| `reservation_expiry` | `TIMESTAMPTZ` | NULL permès | **Caducitat de la reserva** per alliberar el seient automàticament si no es completa el pagament (vegeu lògica de temps real). |

**Justificació clau — `reservation_expiry`:**  
Definir una caducitat explícita permet que el sistema (worker, esdeveniment Socket.IO o neteja periòdica) **alliberi seients** quan el pagament no es finalitza, evitant que quedin bloquejats indefinidament i millorant la rotació en pic de demanda.

---

### `orders`

**Responsabilitat:** Registrar una comanda de compra (import total, estat del pagament, vinculació a proveïdor de pagaments).

| Camp | Tipus PostgreSQL (orientatiu) | Restriccions | Justificació |
|------|-------------------------------|--------------|--------------|
| `id` | `SERIAL` / `INTEGER` | **PK**, NOT NULL | Identificador de la comanda. |
| `user_id` | `INTEGER` | **FK** → `users(id)`, NOT NULL | Comprador responsable de la comanda. |
| `total_amount` | `NUMERIC(10,2)` | NOT NULL | Import total acordat en el moment de la comanda (consistència amb passarel·la de pagament). |
| `status` | `ENUM` o `VARCHAR` amb `CHECK` | NOT NULL | Valors: `pending`, `completed`, `expired`. Reflexiona el cicle de vida del pagament i l’emissió de tiquets. |
| `payment_intent_id` | `VARCHAR` | NULL permès, **UNIQUE** si el proveïdor garanteix unicitat | Identificador del proveïdor (p. ex. Stripe) per reconciliar estats i webhooks. |
| `created_at` | `TIMESTAMPTZ` | NOT NULL (o per defecte `now()`) | Ordre temporal i suport a disputes o auditoria. |

---

### `tickets`

**Responsabilitat:** Emmagatzemar cada entrada emesa (vinculada a una comanda i un seient) amb un **identificador de validació únic**.

| Camp | Tipus PostgreSQL (orientatiu) | Restriccions | Justificació |
|------|-------------------------------|--------------|--------------|
| `id` | `SERIAL` / `INTEGER` | **PK**, NOT NULL | Identificador intern del tiquet. |
| `order_id` | `INTEGER` | **FK** → `orders(id)`, NOT NULL | Pertinença a una comanda concreta (agrupació de línia de compra). |
| `seat_id` | `INTEGER` | **FK** → `seats(id)`, NOT NULL, **UNIQUE** (recomanat: un tiquet actiu per seient venut) | Seient assignat; la unicitat evita doble venda del mateix seient en estat final. |
| `qr_hash` | `TEXT` o `VARCHAR` | **UNIQUE**, NOT NULL | **Hash o token secret** per generar/validar el QR sense exposar claus internes; impedeix duplicació fàcil de codis vàlids. |
| `purchased_at` | `TIMESTAMPTZ` | NOT NULL (o per defecte `now()`) | Moment d’emissió per control d’accés i informes. |

**Justificació clau — `qr_hash`:**  
El camp actua com a **mètode de validació únic**: el codi QR es pot derivar d’aquest valor (o signar-se amb ell) de manera que cada entrada sigui distingible i verificable a porta sense revelar seqüències previsibles d’`id`.

---

## Relacions entre taules

- **`users` → `orders`:** relació **1:N**. Un usuari pot tenir diverses comandes al llarg del temps.
- **`users` → `seats` (via `held_by_user_id`):** relació **1:N** opcional. Un usuari pot retenir diversos seients en fluxos paral·lels (segons regles de negoci); cada seient en retenció apunta com a màxim a un usuari.
- **`events` → `seat_zones`:** **1:N**. Un esdeveniment té diverses zones.
- **`seat_zones` → `seats`:** **1:N**. Cada zona conté molts seients.
- **`orders` → `tickets`:** **1:N**. Una comanda pot incloure diverses entrades (diversos seients).
- **`seats` → `tickets`:** en la pràctica **1:1** per comanda completada (un seient venut genera un tiquet); la restricció **UNIQUE** sobre `seat_id` a `tickets` (o regla equivalent) reforça que no hi hagi dos tiquets vàlids per al mateix seient.

No hi ha una relació **N:M** directa entre taules principals en aquest esquema: la relació molts-a-molts «usuaris ↔ seients» es resol amb entitats intermèdies (`orders`, `tickets`, estats de `seats`) i amb la capa de temps real (Redis), no amb una taula d’unió independent només «usuari–seient».

---

## Lògica de temps real: `seats`, Socket.IO i Redis

Els camps **`status`**, **`held_by_user_id`** i **`reservation_expiry`** treballen junts per evitar el **doble bloqueig** (double-booking) quan molts clients competeixen pel mateix seient.

1. **Intenció de reserva (client A):**  
   L’aplicació intenta passar el seient a `reserved`, assignar `held_by_user_id = A` i establir `reservation_expiry = now() + TTL` (per exemple 10–15 minuts). Això ha de fer-se dins una **transacció** amb comprovació d’estat (`status = 'available'`) o amb un **bloqueig optimistic/pessimistic** coherent.

2. **Redis:**  
   Es pot mantenir un **bloqueig curt** o una clau `seat:{id}` amb TTL igual al de la reserva per coordinar instàncies Node.js i reduir condicions de cursa abans d’escriure a PostgreSQL. Redis **no substitueix** la veritat final: la BD ha de reflectir l’estat vàlid després del compromís.

3. **Socket.IO:**  
   En confirmar la reserva (o en denegar-la), el servidor emet un esdeveniment als clients subscrits a l’esdeveniment o al mapa de seients, de manera que la interfície mostri el seient com a no disponible **sense** recarregar la pàgina.

4. **Caducitat (`reservation_expiry`):**  
   Si el pagament no es completa abans d’aquest instant, un procés (cron, worker o lògica al intentar pagar) ha de: posar `status` novament a `available`, buidar `held_by_user_id` i `reservation_expiry`, i opcionalment notificar via Socket.IO que el seient ha tornat a estar lliure.

5. **Estat `sold`:**  
   Quan la comanda passa a `completed` i es generen `tickets`, el seient ha de quedar `sold` sense retenció (`held_by_user_id` NULL, `reservation_expiry` NULL), ja que la venda és definitiva.

Aquest disseny alinea la **UX en temps real** amb la **integritat**: cap dos usuaris no poden persistir una reserva vàlida sobre el mateix seient al mateix temps si les transaccions i les comprovacions d’estat s’apliquen correctament.

---

## Regles de negoci crítiques

1. **Reserva de seients:** Un seient **no pot ser reservat** (passar a `reserved` amb retenció vàlida) si el seu estat ja és **`reserved`** (amb una reserva encara vàlida segons `reservation_expiry`) o **`sold`**. Qualsevol intent ha de rebutjar-se o reintentar-se amb feedback clar al client.

2. **Transacció de compra atòmica:** La confirmació del pagament, l’actualització d’`orders.status`, la creació de les files a **`tickets`** i l’actualització dels **`seats`** afectats a **`sold`** han d’executar-se dins una **única transacció de base de dades** (o patró equivalent amb compensació estricta), de manera que no existeixi un estat intermedi on s’hagi cobrat però no hi hagi tiquet, ni tiquet sense seient coherent.

3. **Coherència amb `threshold_n`:** Quan la demanda superi el llindar definit per esdeveniment, el sistema ha d’aplicar les polítiques acordades (cua, limitació de peticions, etc.) sense violar els límits de reserva per usuari definits a nivell d’aplicació o esdeveniment.

4. **Unicitat del tiquet i del seient venut:** No s’han de generar dos tiquets vàlids per al mateix `seat_id` en venda completada; el camp `qr_hash` ha de romandre únic per permetre validació fiable a l’accés.

---

*Document generat com a referència tècnica per al curs TR3DAW 2025-26. Actualitzeu la data de revisió en el control de versions quan es modifiqui l’esquema o les regles aquí descrites.*
