# Updated Design System Specification: TR3-ENTRADES (Clean Kinetic Style)

Aquest document defineix el sistema visual i d'interacció complet de TR3-ENTRADES per a totes les pantalles de client i administració. El to és "clean kinetic": net, premium, modern i amb energia controlada.

## 1. Principis de Disseny

1. **Claredat abans que decoració:** cada pantalla prioritza llegibilitat i jerarquia.
2. **Energia subtil:** microanimacions, gradients suaus i contrast elevat, sense agressivitat visual.
3. **Consistència sistèmica:** mateixa gramàtica de components entre client i admin.
4. **Mobile-first real:** dissenyat primer per mòbil, escalat a tablet i desktop.
5. **Feedback immediat:** cada acció crítica té resposta visual instantània (loading, success, error, real-time).

## 2. Identitat Visual i Fonaments

### 2.1 Paleta de Colors (Balanced)
- **Primary Black:** `#050505` (fons base global, profunditat i contrast).
- **Soft White:** `#F8F8F8` (text principal en fons fosc, superfícies clares i targetes premium).
- **Neon Yellow (Action):** `#FFEE32` (CTA principal, highlights d'estat i alerts).
- **Deep Slate:** `#202020` (fons secundari, panells, zones de contingut).
- **Medium Gray:** `#D6D6D6` (bordes, separadors i text secundari).

### 2.2 Colors Funcionals (derivats)
- **Success:** `#A7F3A0` (confirmacions, tickets pagats, validacions OK).
- **Warning:** `#FFD166` (avisos no crítics, pre-sale, temps limitat).
- **Error:** `#FF6B6B` (errors de pagament, errors de xarxa, seient no disponible).
- **Info:** `#9AD1FF` (tooltips, ajuda contextual, estats informatius).

### 2.3 Contrastos i accessibilitat
- Text normal: contrast mínim AA sobre fons.
- CTA en groc: text negre (`#050505`) per màxima llegibilitat.
- No dependre només de color: combinar color + icona + text d'estat.

## 3. Tipografia

### 3.1 Famílies
- **Headings:** Archivo Black o Syne, pes 800, tracking lleugerament positiu.
- **Body:** Inter o Helvetica Neue, pes 400/500.
- **Mono:** JetBrains Mono per xifres, codis, cues i IDs.

### 3.2 Escala tipogràfica recomanada
- **Display:** 48/56 (pantalles hero o comptador de cua desktop).
- **H1:** 36/42
- **H2:** 28/34
- **H3:** 22/28
- **Body L:** 18/28
- **Body:** 16/24
- **Body S:** 14/20
- **Caption:** 12/16

### 3.3 Regles d'ús
- Headings en frases curtes, mai blocs llargs.
- Cos de text amb amplada de lectura controlada.
- Dades operatives (temps, posició, codis): sempre en monospace.

## 4. Tokens de UI i Composició

### 4.1 Radi, bordes i ombres
- **Border radius principal:** `24px` (cards, botons, inputs, modals).
- **Radius secundari:** `16px` (badges, chips, petits contenidors).
- **Bordes:** `1px` o `1.5px` en `#D6D6D6`.
- **Shadows:** elevació suau (shadow-sm, shadow-md, shadow-xl segons jerarquia).

### 4.2 Espaiat (sistema base 8)
- Escala recomanada: `4, 8, 12, 16, 24, 32, 40, 48, 64`.
- Separació entre seccions majors: mínim `32`.
- Densitat baixa en pantalles de compra/pagament per reduir estrès cognitiu.

### 4.3 Grid i responsive
- **Mòbil:** 4 columnes, marges laterals 16.
- **Tablet:** 8 columnes, marges 24.
- **Desktop:** 12 columnes, max width 1280, marges 32/48.
- Zones crítiques (checkout, cua): layout centrat i ample controlat.

### 4.4 Motion i transicions
- Duracions recomanades: 120ms (micro), 220ms (normal), 320ms (entrada/sortida).
- Easing principal: `ease-out` per entrada i `ease-in-out` per canvis d'estat.
- Evitar animacions agressives: moviments curts i opacitat progressiva.

## 5. Components Base

### 5.1 Botons
- **Primary:** fons `#FFEE32`, text negre, radius 24, padding generós.
- **Secondary:** fons `#202020`, text `#F8F8F8`, borda `#D6D6D6`.
- **Ghost:** transparent amb borda subtil.
- **Disabled:** opacitat 45%, cursor no-interactiu.

### 5.2 Inputs i formularis
- Fons `#202020`, text clar, borda fina.
- Focus: halo suau groc + borda més visible.
- Errors: text i borda en vermell suau, missatge curt sota camp.
- Labels sempre visibles (no dependre només de placeholder).

### 5.3 Cards
- Fons principal segons context (clar en premium ticket, fosc en app general).
- Radius 24, padding 24, jerarquia amb títol + metadades + CTA.

### 5.4 Badges i chips
- Petits indicadors per estat: proximitat, últimes entrades, pre-sale, sold out.
- Fons translúcid o pastís, borda fina i text de contrast alt.

### 5.5 Feedback i notificacions
- Toasts ancorats a zona superior/baix (segons plataforma).
- Tipus: success, warning, error, info.
- Durada 3-5s i opció de tancar.

## 6. Experiència per Pàgina (Client)

### 6.1 Login / Registre
**Objectiu:** accés ràpid, confiança i baixa fricció.

- **Layout:** columna única centrada; logo + títol + formulari + accions secundàries.
- **Fons:** gradient subtil `#050505` -> `#202020`.
- **Superfície formulari:** card fosca amb borda `#D6D6D6` i radius 24.
- **CTA principal:** "Entrar" / "Crear compte" en groc neó.
- **Elements clau:** camp email, password, recorda'm, recuperar contrasenya, canvi login/registre.
- **Estats:** loading al botó, error inline per camp, error global de credencials.
- **Responsive:** a desktop pot aparèixer split layout (form + missatge visual de marca).

### 6.2 Cartellera (Landing)
**Objectiu:** descobrir esdeveniments i convertir a compra.

- **Layout:** grid airejat (1 col mòbil, 2 tablet, 3-4 desktop).
- **Header:** cerca, filtres (proximitat, data, categoria), accés a perfil/favorits.
- **Card d'esdeveniment:**
  - imatge principal amb ratio constant;
  - títol i data en alta jerarquia;
  - localització i preu base en text secundari;
  - badges (proxim, últimes entrades, pre-sale);
  - CTA "Veure seients" / "Comprar".
- **Colors:** base fosca amb contrast clar; accents grocs en accions.
- **Interaccions:** hover suau en desktop, press state en mòbil, skeleton en càrrega.
- **Empty state:** missatge clar + reset filtres.

### 6.3 Cua Virtual (The Waiting Room)
**Objectiu:** reduir ansietat mentre l'usuari espera torn.

- **Layout:** totalment centrat vertical i horitzontal; molt espai negatiu.
- **Comptador principal:** número de posició gran en JetBrains Mono.
- **Indicadors:** barra de progrés suau + text "avançant automàticament".
- **Metadades:** temps estimat, estat de connexió socket, event actiu.
- **Feedback real-time:**
  - connexió OK (punt verd suau),
  - reconnectant (warning),
  - desconnectat (error amb retry).
- **Animació:** transició del número amb fade/slide curt (no flip agressiu).
- **Accions:** avisar quan sigui el torn, abandonar cua (confirmació).

### 6.4 Selecció de Seients (Seat Map)
**Objectiu:** seleccionar seients de forma ràpida i sense confusió.

- **Layout:**
  - Mòbil: mapa + panell resum inferior sticky.
  - Desktop: mapa a l'esquerra, resum i filtres a la dreta.
- **Mapa:**
  - fons `#202020` amb guia de zones clara;
  - seients com nodes amb mida coherent i espai regular.
- **Estats de seient:**
  - disponible: contorn `#D6D6D6`;
  - ocupat: omplert fosc i opacitat alta;
  - seleccionat (meu): gradient suau amb accent groc;
  - bloquejat temporalment (altre usuari): warning discret.
- **Llegenda:** sempre visible.
- **Filtres:** zona, rang de preu, accessibilitat.
- **Regles UX:** max seients seleccionables, validació instantània, expiració de bloqueig visible amb countdown.

### 6.5 Checkout
**Objectiu:** tancar compra amb mínima fricció.

- **Layout:** columna única neta (mòbil i desktop amb ample màxim controlat).
- **Seccions:**
  1. Resum d'entrades (event, seients, import desglossat).
  2. Dades comprador (prefill des d'usuari quan sigui possible).
  3. Mètode de pagament (mock o real segons entorn).
  4. Confirmació legal (checkbox termes).
- **Colors:** superfícies fosques suaus, CTA groc dominant.
- **Inputs:** focus subtil, validació en temps real i errors contextuals.
- **Acció final:** botó gran "PAGAR ARA" amb loading bloquejant doble clic.
- **Post-acció:** estat processant -> èxit (redirect a entrades) o error recuperable.

### 6.6 Les Meves Entrades
**Objectiu:** consulta i gestió de tickets de manera premium.

- **Layout:** llista de tickets en cards grans; detall expandible.
- **Card de ticket:**
  - capçalera amb nom event i data;
  - QR clar i escanejable;
  - seient, zona, estat d'ús;
  - accions: transferir, descarregar, afegir a wallet (si aplica).
- **Estètica:** acabat tipus tiquet premium, cantonades arrodonides, contrast net.
- **Transferència:** flux guiat amb modal/stepper + validació receptor.
- **Estats:** actiu, transferit, utilitzat, cancel·lat.

### 6.7 Perfil d'Usuari (recomanat per consistència)
**Objectiu:** configuració i dades personals.

- Dades bàsiques, canvi contrasenya, preferències de notificació.
- Historial curt d'activitat (compres recents, transferències).
- Botó logout destacat però no dominant.

## 7. Experiència per Pàgina (Administració)

### 7.1 Dashboard Admin Live
**Objectiu:** monitoratge en temps real i control operacional.

- **Layout desktop:** graella modular (KPIs + gràfics + alertes).
- **Widgets principals:**
  - usuaris a la cua,
  - usuaris al mapa de seients,
  - conversió a checkout,
  - vendes en temps real,
  - incidències.
- **Visualització:** gràfics nets (línia/barra) amb paleta coherent.
- **Actualització:** refresh via socket sense hard reload.
- **Alertes:** targeta prioritària per anomalies (caiguda connexió, pic d'errors).

### 7.2 Gestió d'Esdeveniments (Backoffice)
**Objectiu:** crear/editar events i configuració operativa.

- Formularis de dades principals (nom, aforament, dates, preus, zones).
- Seccions col·lapsables per no saturar.
- Validacions immediates i preview de configuració.

### 7.3 Control de Cua (Threshold N)
**Objectiu:** ajustar capacitat de pas de cua en viu.

- Slider/input numèric amb límits clars.
- Canvi amb confirmació i impacte estimat visible.
- Historial curt de canvis (qui, quan, valor antic/nou).

### 7.4 Pre-sale / Llista Blanca
**Objectiu:** activar vendes anticipades a usuaris autoritzats.

- Taula de usuaris habilitats (cerca, afegir, eliminar).
- Estat global pre-sale ON/OFF visible.
- Indicadors de cobertura (% usuaris autoritzats).

### 7.5 Botó de Pànic
**Objectiu:** aturar vendes instantàniament de forma segura.

- Botó d'alt impacte visual però aïllat per evitar clic accidental.
- Doble confirmació obligatòria.
- Després d'activar: banner global + bloqueig de compra en client.
- Opció de restablir amb registre d'auditoria.

## 8. Estats Globals i Real-Time (Socket + Redis)

### 8.1 Seients
- **Reservat per un altre usuari:** canvi instantani de disponible -> ocupat.
- **Alliberat:** retorn a disponible amb transició suau.
- **Reserva expirada:** toast informatiu i actualització del resum.

### 8.2 Cua
- Posició decremental en viu.
- Reconnexió automàtica amb missatge de continuïtat.
- Timeout de sessió amb opció de reentrada controlada.

### 8.3 Checkout
- Bloqueig optimista de seients durant pagament.
- Si falla, rollback visual i missatge accionable.

## 9. Microcopy i To de Marca

- Curt, directe i útil.
- Orientat a acció ("Selecciona", "Confirma", "Continua").
- Errors amb solució ("No s'ha pogut processar el pagament. Torna-ho a provar o canvia mètode.").

## 10. Guia de QA Visual

Checklist mínim per cada pantalla abans de donar per acabada:
1. Contrast i llegibilitat correctes.
2. Espaiat consistent amb sistema base.
3. Estats hover/focus/disabled/error implementats.
4. Skeleton/loading visible en crides lentes.
5. Comportament responsive verificat en mòbil/tablet/desktop.
6. Feedback en temps real sense parpellejos abruptes.
7. CTA principal clar i únic per pantalla.

## 11. Resum de Direcció Visual

TR3-ENTRADES ha de transmetre tecnologia i confiança amb estètica premium accessible: fons foscos elegants, superfícies netes, accions grogues molt clares, formes arrodonides i interaccions fluides en temps real. La percepció final ha de ser moderna, ràpida i robusta.
