# 📋 UserTasks.md - TR3 TicketMaster

## Planificació: 2 Sprints de 5 Dies Cadascun

---

## SPRINT 1: Base (Dies 1-5)
**Objectiu:** Funcionalitats bàsiques operatives - Autenticació, Landing, Cua Virtual

### Dia 1: Infraestructura + Autenticació

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S1.1 | Configurar models Laravel (User, Event, Seat, Ticket, SeatZone) | Backend | Laravel 13 | Crear models manuals a app/Models/ per interactuar amb PostgreSQL |
| S1.2 | Implementar Controlador d'Autenticació (Register, Login, Logout) | Backend | Laravel 13 + Sanctum | API REST per login/register amb JWT via Sanctum |
| S1.3 | Crear Middleware de Protecció de Rutes | Backend | Laravel 13 | auth:sanctum, role check (admin/client) |

### Dia 2: Autenticació Frontend + Redireccions

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S1.4 | Implementar sistema de redirecció return_to | Backend | Laravel 13 | Guardar URL destí abans de login per redirigir posteriorment |
| S1.5 | Crear Store d'Autenticació (Pinia) | Frontend | Nuxt 4 + Pinia | Gestionar estat d'usuari, token JWT, perfil |
| S1.6 | Implementar pàgina Login/Registre | Frontend | Nuxt 4 + Vue 3 | Formularis amb estils DICE (fons negre, botons neó) |
| S1.7 | Configurar Middleware de navegació (auth guard) | Frontend | Nuxt 4 | Protegir rutes segons estat d'autenticació |

### Dia 3: Events + Landing

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S1.8 | Implementar controlador d'Events (llistat, detall) | Backend | Laravel 13 | API per obtenir events, filtrar per proximitat (Geolocation) |
| S1.9 | Implementar sistema "M'interessa" (favorits) | Backend | Laravel 13 | Guardar preferències d'usuari a PostgreSQL |
| S1.10 | Crear pàgina Landing (Cartellera) | Frontend | Nuxt 4 + Tailwind | Grid d'esdeveniments, filtre proximitat, botons "Comprar" |

### Dia 4: The Gatekeeper (Backend + Frontend)

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S1.11 | Implementar lògica The Gatekeeper (Node.js) | Backend | Node.js 24 + Socket.IO | Gestió de cua: validar usuari, generar token de torn, gestionar torns |
| S1.12 | Implementar connexió Redis pub/sub (Laravel → Node) | Backend | Laravel 13 + Redis | Publicar missatges de canvi d'estat de cua |
| S1.13 | Crear pàgina Cua Virtual (The Waiting Room) | Frontend | Nuxt 4 + Socket.IO | Comptador posició, efecte flip, connexió WebSocket |

### Dia 5: Integració + Verificació Sprint 1

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S1.14 | Integrar Login → Cua → Landing | Full Stack | Laravel + Nuxt | Verificar flux complet d'autenticació i entrada a la cua |
| S1.15 | Verificació i testing Sprint 1 | QA | - | Testejar totes les funcionalitats implementades |

---

## SPRINT 2: Complert (Dies 6-10)
**Objectiu:** Mapa de Seients, Checkout, Entrades, Admin Dashboard - Aplicació 100%

### Dia 6: Seients Backend

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S2.1 | Implementar controlador de Seients (llistat, reserva, alliberar) | Backend | Laravel 13 | API per obtenir seients d'un event, reservar seient, validar propietat |
| S2.2 | Implementar sistema de bloqueig de seient (Redis + SQL) | Backend | Laravel 13 + Redis | Reservar seient amb user_id i expiry timer |
| S2.3 | Implementar sincronització temps real seients | Backend | Node.js + Redis | Subscriure's a canals Redis, emetre broadcasts als clients |

### Dia 7: Mapa de Seients Frontend

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S2.4 | Crear component SeatMap (visualització) | Frontend | Nuxt 4 + Vue 3 | Renderitzar mapa de seients amb estats (disponible, ocupat, seleccionat) |
| S2.5 | Implementar selecció de seients (interacció) | Frontend | Nuxt 4 + Socket.IO | Clicar seient → reservar → actualitzar estat en temps real |
| S2.6 | Implementar sistema de filtres (zona, preu) | Frontend | Nuxt 4 | Slider de preu, selector de zona |

### Dia 8: Checkout + Pagament

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S2.7 | Implementar controlador de Checkout (validar, processar) | Backend | Laravel 13 | Validar reserva activa, processar pagament (mock), generar ticket |
| S2.8 | Crear pàgina Checkout (pasarel·la) | Frontend | Nuxt 4 | Formulari pre-omplit, resum comanda, botó "PAGAR ARA" |
| S2.9 | Implementar generació de QR per entrada | Backend | Laravel 13 | Generar hash únic, renderitzar QR com a imatge |

### Dia 9: Entrades + Admin

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S2.10 | Crear pàgina "Les meves Entrades" | Frontend | Nuxt 4 + Vue 3 | Llistat entrades, mostrar QR, estil DICE |
| S2.11 | Implementar transferència d'entrades | Backend + Frontend | Laravel 13 + Nuxt | Flux de traspàs segur amb acceptació del receptor |
| S2.12 | Implementar Dashboard Admin (Live stats) | Backend + Frontend | Laravel 13 + Nuxt + Socket.IO | Usuaris a cua, usuaris al mapa, vendes en temps real |
| S2.13 | Implementar Botó de Pànic | Backend + Frontend | Laravel 13 + Nuxt | Aturar vendes instantàniament, notificar via Socket |

### Dia 10: Acabats Finals + Demo

| ID | Tasca | Tipus | Tecnologia | Descripció |
|----|-------|-------|------------|------------|
| S2.14 | Implementar ajust de llindar N (cua) | Backend + Frontend | Laravel 13 + Nuxt | Canviar threshold_N d'un event en viu |
| S2.15 | Implementar sistema Pre-sale (llista blanca) | Backend + Frontend | Laravel 13 + Nuxt | Gestionar usuaris autoritzats per comprar abans |
| S2.16 | Estils finals i polish UI/UX | Frontend | Tailwind CSS | Animacions, transicions, efectes DICE (neó, glows) |
| S2.17 | Verificació final i testing 100% | QA | - | Testejar tota l'aplicació, validar completesa |

---

## 🎯 Entregable Final (Dia 10)

Al finalitzar el Sprint 2, l'aplicació ha d'estar **100% operativa** amb:

- ✅ Autenticació JWT (Login/Register/Logout) amb rols Admin/Client
- ✅ Middleware de protecció de rutes (return_to, redireccions)
- ✅ Landing amb filtre de proximitat i sistema "M'interessa"
- ✅ Cua Virtual (The Gatekeeper) amb token de torn JWT
- ✅ Mapa de Seients en temps real (Socket.IO + Redis)
- ✅ Sistema de reserva amb expiry timer
- ✅ Checkout amb validació de propietat
- ✅ Entrades amb QR dinàmic
- ✅ Transferència d'entrades
- ✅ Dashboard Admin Live amb estadístiques
- ✅ Botó de Pànic i ajust de llindar N
- ✅ Estils DICE (fons negre, botons neó #FF0055, Electric Blue #00F0FF)
- ✅ Totes les APIs documentades

---

## 📊 Resum per Tecnologia

| Tecnologia | Tasques |
|------------|---------|
| **Laravel 13** | S1.1, S1.2, S1.3, S1.4, S1.8, S1.9, S1.12, S2.1, S2.2, S2.7, S2.9, S2.11, S2.12, S2.13, S2.14, S2.15 |
| **Node.js 24** | S1.11, S1.12, S2.3 |
| **Nuxt 4** | S1.5, S1.6, S1.7, S1.10, S1.13, S2.4, S2.5, S2.6, S2.8, S2.10, S2.11, S2.12, S2.13, S2.14, S2.15, S2.16 |
| **Pinia 3** | S1.5, S1.7, S2.4, S2.5, S2.10 |
| **Socket.IO** | S1.11, S1.13, S2.3, S2.4, S2.5, S2.12, S2.13 |
| **Redis** | S1.11, S1.12, S2.2, S2.3 |
| **Tailwind CSS** | S1.6, S1.10, S1.13, S2.4, S2.8, S2.10, S2.12, S2.16 |
| **PostgreSQL** | S1.1 (via models), S1.8, S1.9, S2.1, S2.2, S2.7, S2.9 |
