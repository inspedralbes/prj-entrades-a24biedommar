# 🎫 TR3 TicketMaster - DAW

Plataforma de venda d'entrades d'alta demanda amb una estètica inspirada en **DICE** i una robustesa tècnica similar a **Ticketmaster**.

---

## 🛠️ Tecnologies Clau (Versions)

*   **Node.js:** `24.14.0 (LTS)`
*   **Nuxt:** `4.4.2` (Frontend SSR)
*   **Laravel:** (Backend API REST)
*   **Socket.IO:** `4.8.3` (Real-time Queue & Seat Selection)
*   **PostgreSQL:** `18.3` (Database)
*   **Redis:** `8.6.1` (Cache & Queue Management)
*   **Tailwind CSS:** `4.2.2` (Estil DICE)

---

## 🚀 Guia ràpida de l'Entorn

### 1. Requisits Previs
*   Docker i Docker Compose instal·lats.
*   Node.js 24.x instal·lat per al desenvolupament local (opcional).

### 2. Aixecar el Projecte
```bash
docker-compose up -d
```

### 3. Accés als Serveis
*   **Frontend (Nuxt):** [http://localhost:3000](http://localhost:3000)
*   **Backend API (Laravel):** [http://localhost:8000](http://localhost:8000)
*   **Backend Realtime (Sockets):** [http://localhost:3001](http://localhost:3001)
*   **Gestió BD (phpMyAdmin):** [http://localhost:8080](http://localhost:8080) (Accés directe sense login)

---

## 📋 Documentació Relevancia

Pots trobar els detalls complets del projecte en la carpeta `doc/`:

*   **[Especificacions completes del projecte](./doc/EspecificacionsProjecte.md)**: Detall de rols, funcionalitats i lògica de negoci.
*   **[Pla d'Inicialització](./doc/Plans/PlaInici.md)**: Estructura de directoris, versions i configuració de ports.

---

## 🎨 Disseny i Estètica

El projecte segueix l'estètica **DICE**: fons negre absolut, minimalisme extrem i un botó rosa neó (`#FF0055`) gegant com a element principal de crida a l'acció.
