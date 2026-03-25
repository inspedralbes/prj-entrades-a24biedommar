-- =============================================================================
-- TAULES / ESTRUCTURES (DDL)
-- Motor: PostgreSQL 18.3 — Plataforma TR3 (venda d’entrades en temps real)
-- Font de veritat: doc/DataBase.md
-- =============================================================================

-- Neteja idempotent per a entorns de desenvolupament (executar abans de recrear)
DROP TABLE IF EXISTS tiquets CASCADE;
DROP TABLE IF EXISTS comandes CASCADE;
DROP TABLE IF EXISTS seients CASCADE;
DROP TABLE IF EXISTS zones_de_seient CASCADE;
DROP TABLE IF EXISTS esdeveniments CASCADE;
-- Tokens Sanctum (morph cap a usuaris; cal eliminar abans de usuaris si hi hagués FK lògica)
DROP TABLE IF EXISTS personal_access_tokens CASCADE;
DROP TABLE IF EXISTS usuaris CASCADE;

DROP TYPE IF EXISTS estat_comanda CASCADE;
DROP TYPE IF EXISTS estat_seient CASCADE;
DROP TYPE IF EXISTS rol_usuari CASCADE;

-- Tipus enumerats per estats i rols (valors alineats amb la documentació)
CREATE TYPE rol_usuari AS ENUM ('admin', 'client');

CREATE TYPE estat_seient AS ENUM ('disponible', 'reservat', 'venut');

CREATE TYPE estat_comanda AS ENUM ('pendent', 'completada', 'expirada');

-- -----------------------------------------------------------------------------
-- usuaris
-- Propòsit: comptes d’accés (administradors i clients), autenticació i autorització.
-- Relacions: rep FK des de comandes (usuari_id), seients (retingut_per_usuari_id).
-- Camps crítics: correu_electronic únic; contrasenya només com a hash; creat_el per auditoria.
-- -----------------------------------------------------------------------------
CREATE TABLE usuaris (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    correu_electronic VARCHAR(150) NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    rol rol_usuari NOT NULL DEFAULT 'client',
    creat_el TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_usuaris_correu UNIQUE (correu_electronic)
);

-- -----------------------------------------------------------------------------
-- personal_access_tokens (Laravel Sanctum)
-- Propòsit: tokens d’API Bearer (hash SHA-256) vinculats polimòrficament a `Usuari`.
-- No s’usa migració Laravel; esquema alineat amb Sanctum 4.x.
-- -----------------------------------------------------------------------------
CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT,
    last_used_at TIMESTAMPTZ,
    expires_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ,
    updated_at TIMESTAMPTZ,
    CONSTRAINT uq_personal_access_tokens_token UNIQUE (token)
);

CREATE INDEX idx_personal_access_tokens_tokenable ON personal_access_tokens (tokenable_type, tokenable_id);
CREATE INDEX idx_personal_access_tokens_expires_at ON personal_access_tokens (expires_at);

-- -----------------------------------------------------------------------------
-- esdeveniments
-- Propòsit: esdeveniments venibles, recinte, data i llindar de demanda (cua).
-- Relacions: 1:N amb zones_de_seient.
-- Camps crítics: llindar_n per polítiques de cua; data_esdeveniment per filtres i tancament de vendes.
-- -----------------------------------------------------------------------------
CREATE TABLE esdeveniments (
    id SERIAL PRIMARY KEY,
    titol VARCHAR(200) NOT NULL,
    descripcio TEXT,
    data_esdeveniment TIMESTAMPTZ NOT NULL,
    nom_recinte VARCHAR(200) NOT NULL,
    url_imatge TEXT,
    llindar_n INTEGER NOT NULL DEFAULT 50,
    latitud NUMERIC(10, 8),
    longitud NUMERIC(11, 8),
    actiu BOOLEAN NOT NULL DEFAULT TRUE
);

-- -----------------------------------------------------------------------------
-- zones_de_seient
-- Propòsit: agrupar seients per zona amb preu base i color al mapa.
-- Relacions: FK a esdeveniments; 1:N amb seients.
-- Camps crítics: preu_base per política de preus per zona sense duplicar-la a cada seient.
-- -----------------------------------------------------------------------------
CREATE TABLE zones_de_seient (
    id SERIAL PRIMARY KEY,
    esdeveniment_id INTEGER NOT NULL REFERENCES esdeveniments (id) ON DELETE CASCADE,
    nom_zona VARCHAR(100) NOT NULL,
    preu_base NUMERIC(10, 2) NOT NULL,
    codi_color VARCHAR(7)
);

-- -----------------------------------------------------------------------------
-- seients
-- Propòsit: estat de cada seient (disponible / reservat / venut) i retenció temporal.
-- Relacions: FK a zones_de_seient; opcional a usuaris (retingut_per_usuari_id).
-- Camps crítics: estat + caducitat_reserva + retingut_per_usuari_id per temps real i anti double-booking.
-- -----------------------------------------------------------------------------
CREATE TABLE seients (
    id SERIAL PRIMARY KEY,
    zona_id INTEGER NOT NULL REFERENCES zones_de_seient (id) ON DELETE CASCADE,
    etiqueta_fila VARCHAR(10),
    numero_seient INTEGER,
    estat estat_seient NOT NULL DEFAULT 'disponible',
    retingut_per_usuari_id INTEGER REFERENCES usuaris (id) ON DELETE SET NULL,
    caducitat_reserva TIMESTAMPTZ
);

-- -----------------------------------------------------------------------------
-- comandes
-- Propòsit: comanda de compra (import, estat de pagament, vinculació a passarel·la).
-- Relacions: FK a usuaris; 1:N amb tiquets.
-- Camps crítics: id_intencio_pagament per webhooks; import_total coherent amb el pagament.
-- -----------------------------------------------------------------------------
CREATE TABLE comandes (
    id SERIAL PRIMARY KEY,
    usuari_id INTEGER NOT NULL REFERENCES usuaris (id) ON DELETE RESTRICT,
    import_total NUMERIC(10, 2) NOT NULL,
    estat estat_comanda NOT NULL,
    id_intencio_pagament VARCHAR(255),
    creat_el TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_comandes_id_intencio_pagament UNIQUE (id_intencio_pagament)
);

-- -----------------------------------------------------------------------------
-- tiquets
-- Propòsit: entrada emesa (comanda + seient) amb hash únic per validació (QR).
-- Relacions: FK a comandes i seients.
-- Camps crítics: hash_qr únic; un seient venut no pot tenir dos tiquets.
-- -----------------------------------------------------------------------------
CREATE TABLE tiquets (
    id SERIAL PRIMARY KEY,
    comanda_id INTEGER NOT NULL REFERENCES comandes (id) ON DELETE CASCADE,
    seient_id INTEGER NOT NULL REFERENCES seients (id) ON DELETE RESTRICT,
    hash_qr TEXT NOT NULL,
    comprat_el TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_tiquets_seient UNIQUE (seient_id),
    CONSTRAINT uq_tiquets_hash_qr UNIQUE (hash_qr)
);

-- =============================================================================
-- RESTRICCIONS / CLAUS ESTRANGERES (FK)
-- =============================================================================
-- Les FKs declarades inline cobreixen les relacions principals. Resum:
-- esdeveniments ← zones_de_seient ← seients; usuaris → comandes → tiquets; seients → tiquets.

-- =============================================================================
-- ÍNDEXS / OPTIMITZACIÓ
-- =============================================================================
CREATE INDEX idx_zones_de_seient_esdeveniment ON zones_de_seient (esdeveniment_id);
CREATE INDEX idx_seients_zona ON seients (zona_id);
CREATE INDEX idx_seients_retingut ON seients (retingut_per_usuari_id);
CREATE INDEX idx_seients_estat ON seients (estat);
CREATE INDEX idx_comandes_usuari ON comandes (usuari_id);
CREATE INDEX idx_comandes_creat ON comandes (creat_el);
CREATE INDEX idx_tiquets_comanda ON tiquets (comanda_id);
