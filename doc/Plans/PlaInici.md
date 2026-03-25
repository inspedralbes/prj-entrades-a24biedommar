# 🚀 Pla d'Inicialització Professional: TR3 TicketMaster

Aquest document és el full de ruta definitiu per a la inicialització de la plataforma **TR3 TicketMaster**, detallant cada component tecnològic, la seva configuració Docker i la lògica de dades profunda.

---

## 🛠️ 1. Pila Tecnològica (JavaScript Core + PHP Engine)

| Tecnologia | Versió | Rol en el Projecte |
| :--- | :--- | :--- |
| **Node.js** | `24.14.0 (LTS)` | Real-Time Gateway (Socket.IO). |
| **Nuxt** | `4.4.2` | Framework SSR per al Frontend (Estil DICE). |
| **Vue.js** | `3.5.30` | Lògica de components reactius. |
| **Laravel** | `13.1.1` | API REST, CRUD i Lògica de Negoci. |
| **PHP-FPM** | `8.3+` | Motor d'execució optimitzat per a consultes SQL ràpides i baixa latència. |
| **Socket.IO** | `4.8.3` | Orquestració de la Cua i el Mapa en temps real. |
| **Tailwind CSS** | `4.2.2` | Framework CSS per a l'estètica DICE. |
| **Redis** | `8.6.1` | Bus de dades (Pub/Sub) i Cache. |
| **PostgreSQL** | `18.3` | Persistència robusta de dades. |
| **Pinia** | `3.0.4` | Magatzem d'estat reactiu per al frontend. |

---

## 🚀 2. Arquitectura "Ultra Real-Time" (10001%) - Hybrid Backend Edition

1.  **Nuxt 4 & Pinia 3:** Reactivitat automàtica via Sockets.
2.  **Laravel 13 & PHP-FPM:** Lògica de domini i persistència SQL.
3.  **Real-Time Node.js Gateway:** Pont d'alta velocitat entre Redis i el Frontend.
4.  **Redis 8.6:** Sistema Pub/Sub (Laravel PUB / Node SUB).

---

## 🐳 3. Infraestructura Docker i Ports

| Contenidor | Port Host | Descripció |
| :--- | :--- | :--- |
| `front_nuxt` | **3000** | Interfície d'usuari. |
| `back_laravel` | **8000** | API REST. |
| `back_node_io` | **3001** | Servidor de Sockets. |
| `db_postgres` | **5432** | PostgreSQL Database. |
| `cache_redis` | **6379** | Redis Storage. |
| `phpmyadmin` | **8080** | Admin de BD (Dev). |

---

## 📁 4. Estructura de Directoris (Extensa en Directoris, Essencial en Fitxers)

Basada en les especificacions, aquesta és la jerarquia de carpetes necessària per organitzar el projecte professionalment. S'inclouen només els fitxers d'arrencada imprescindibles.

### 4.1. Arrel i Infraestructura
```text
/
├── docker-compose.yml          # Orquestració global
├── .env                        # Variables d'entorn
├── README.md                   # Setup ràpid
├── db/                         # ÚNICA FONT DE VERITAT DE LA BD
│   ├── init.sql                # Esquema de la base de dades
│   └── insert.sql              # Seeders dades inicials
├── doc/                        # Documentació del projecte
│   ├── Plans/                  # Pla d'Inici i Sprints
│   └── EspecificacionsProjecte.md
└── docker/                     # Configuracions de contenidors
    ├── nginx/
    ├── postgres/
    ├── redis/
    └── php/
```

### 4.2. Frontend (Nuxt 4 / JavaScript)
```text
frontend/
├── assets/                     # Estils globals (Tailwind 4)
├── components/                 # UI: Mapa de seients, botons neó, etc.
├── composables/                # Lògica: useSocket, useQueue, useAuth
├── layouts/                    # Plantilles: Client, Admin, Auth
├── middleware/                 # Protecció de rutes: gatekeeper, adminGuard
├── pages/                      # Totes les vistes (Landing, Cua, Mapa, Checkout, Entrades)
├── plugins/                    # Configuració global de Sockets i Pinia
├── public/                     # Imatges, favicon, fitxers estàtics
├── server/                     # API interna de Nuxt
├── store/                      # Pinia: AuthStore, EventStore, QueueStore
├── utils/                      # Helper JS functions
├── nuxt.config.js              # FITXER IMPRESCINDIBLE
└── package.json                # FITXER IMPRESCINDIBLE
```

### 4.3. Backend API (Laravel 13 / PHP 8.3)
```text
backend-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Lògica d'API REST
│   │   └── Middleware/         # Validació de rols (Admin/Client)
│   ├── Models/                 # Eloquent: User, Event, Seat, Ticket (Models manuals)
│   └── Providers/              # Broadcast i Redis providers
├── bootstrap/                  # Arrencada interna del framework
├── config/                     # Configuració de DB, Redis i Auth
├── database/                   # [AVÍS: NO S'USEN MIGRACIONS]
│   └── seeders/                # Només si calgués per a Eloquent post-init
├── public/                     # Entry point Laravel
├── resources/                  # Vistes de correu o configuració extra
├── routes/                     # Definició de punts clau (api.php)
├── storage/                    # Logs i fitxers persistents
├── tests/                      # Unit i Feature tests de PHP
├── artisan                     # FITXER IMPRESCINDIBLE
└── composer.json               # FITXER IMPRESCINDIBLE
```

---

## ⚙️ 5. Comandes d'Instal·lació

### 5.1. Inicialització Base
- **Frontend:** `npx nuxi@latest init frontend`
- **Laravel:** `composer create-project laravel/laravel backend-api`
- **Node Backend:** `mkdir backend-realtime && cd backend-realtime && npm init -y`
- **Tests E2E:** `mkdir tests-e2e && cd tests-e2e && npm init -y`

### 5.2. Dependències Crítiques
- **Frontend:** `npm install pinia socket.io-client @tailwindcss/vite`
- **Laravel:** `composer require predis/predis laravel/sanctum`
- **Node Backend:** `npm install socket.io redis express dotenv`
- **Tests E2E:** `npm install -D @playwright/test playwright`

---

## 🤖 6. Automatització Docker per Tecnologia

1.  **Frontend (Nuxt):** Imatge Node 24.
2.  **Backend API (Laravel 13 + PHP-FPM):** Imatge `php:8.3-fpm`. PHP-FPM gestionarà el pool de processos per optimitzar el rendiment de les consultes SQL a PostgreSQL 18.3, garantint una latència mínima. **NO executarà `php artisan migrate`**.
3.  **Backend Realtime (Node):** Relay de Sockets ultra-ràpid.
4.  **Base de Dades (PostgreSQL):** El Docker importarà automàticament `/db/init.sql` i `/db/insert.sql` en el primer arrencada. Aquesta és la font de veritat obligatòria.

---

## 🗄️ 7. Lògica de la Base de Dades: Esquema SQL (Única Font de Veritat)

⚠️ **ATENCIÓ:** No hi haurà migracions de Laravel per a la definició de l'esquema. Tot canvi estructural s'ha de reflectir en el fitxer `db/init.sql`.

### 📝 7.1. Esquema Compleat (`db/init.sql`)
```sql
-- 🎫 TR3 TicketMaster Schema (PostgreSQL 18.3)
CREATE TYPE user_role AS ENUM ('admin', 'client');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role user_role DEFAULT 'client'
);

CREATE TABLE events (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    threshold_n INTEGER DEFAULT 50, -- Llindar N per a la cua
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE seat_zones (
    id SERIAL PRIMARY KEY,
    event_id INTEGER REFERENCES events(id) ON DELETE CASCADE,
    zone_name VARCHAR(100) NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL
);

CREATE TABLE seats (
    id SERIAL PRIMARY KEY,
    zone_id INTEGER REFERENCES seat_zones(id) ON DELETE CASCADE,
    row_label VARCHAR(10),
    seat_number INTEGER,
    status VARCHAR(20) DEFAULT 'available', 
    user_id INTEGER REFERENCES users(id), 
    reservation_expiry TIMESTAMP
);

CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    seat_id INTEGER REFERENCES seats(id),
    qr_hash TEXT UNIQUE NOT NULL
);
```

### 📝 7.2. Dades de Prova Extenses (`db/insert.sql`)
```sql
-- Dades inicials forçades via SQL
INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@master.da3', 'pass', 'admin');
INSERT INTO events (title, threshold_n, latitude, longitude) VALUES ('Concert TR3 - DICE Experience', 5, 41.3851, 2.1734);
INSERT INTO seat_zones (event_id, zone_name, base_price) VALUES (1, 'Vip Row', 100.00), (1, 'General', 50.00);
INSERT INTO seats (zone_id, row_label, seat_number, status) VALUES 
(1, 'A', 1, 'available'), (1, 'A', 2, 'available'), (1, 'A', 3, 'available');
```

---

## 📄 8. contingut final del README.md (Arrel)
Guia de setup ràpida amb `docker-compose up -d --build`.

---

## 📅 9. Llista de Tasques d'Inicialització (Pas a Pas)

S'ha de completar cada tasca i verificar-ne el funcionament al 100% abans de passar a la següent.

### Fase 1: Entorn i Documentació
- [x] **Tasca 1.1**: Creació de l'estructura física de directoris (`db`, `doc`, `docker`, `frontend`, `backend-api`, `backend-realtime`, `tests-e2e`).
- [x] **Tasca 1.2**: Creació dels fitxers SQL font (`db/init.sql` i `db/insert.sql`) amb l'esquema extens.
- [x] **Tasca 1.3**: Creació de la guia d'estil i sistema de disseny (`doc/Design.md`).

### Fase 2: Inicialització de Frameworks (Natius)
- [x] **Tasca 2.1**: Inicialització de Nuxt 4 a la carpeta `frontend`.
- [x] **Tasca 2.2**: Inicialització de Laravel 13 a la carpeta `backend-api`.
- [x] **Tasca 2.3**: Inicialització de Node.js i dependències de Sockets/Redis a `backend-realtime`.
- [x] **Tasca 2.4**: Inicialització de Playwright a la carpeta `tests-e2e`.

### Fase 3: Dockerització i Orquestració
- [x] **Tasca 3.1**: Creació del fitxer `docker-compose.yml` global regulant els 6 serveis i els seus volums.
- [x] **Tasca 3.2**: Configuració de les variables d'entorn globals (`.env.example`).
- [x] **Tasca 3.3**: Creació dels Dockerfiles per a PHP-FPM, Node i Nginx.

### Fase 4: Verificació i Sprint Zero
- [ ] **Tasca 4.1**: Arrencada de l'ecosistema (`docker-compose up`).
- [ ] **Tasca 4.2**: Verificació de la comunicació Sockets -> Redis -> Laravel.
- [ ] **Tasca 4.3**: Execució del primer test E2E de "fum" (Smoke Test) per validar que la BD està poblada.
