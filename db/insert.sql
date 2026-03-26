-- =============================================================================
-- INSERCIONS DE DADES (DML)
-- Llavors inicials per a desenvolupament — TR3 (venda d’entrades)
-- Requisit: executar després de db/init.sql
-- =============================================================================

-- -----------------------------------------------------------------------------
-- usuaris
-- Contrasenya: hash de prova (substituir per hash real des de l’aplicació).
-- -----------------------------------------------------------------------------
INSERT INTO usuaris (nom, correu_electronic, contrasenya, rol, creat_el)
VALUES
    ('Administrador Principal', 'admin@tr3.daw', '$2y$10$xtBJ.oY8SZUXqoJcVZrkoumk5cMecuXAuzTOYLEvpMOrOec3o4eKm', 'admin', NOW() - INTERVAL '30 days'),
    ('Laia Ferrer', 'laia.ferrer@exemple.cat', '$2y$10$xtBJ.oY8SZUXqoJcVZrkoumk5cMecuXAuzTOYLEvpMOrOec3o4eKm', 'client', NOW() - INTERVAL '10 days'),
    ('Marc Puig', 'marc.puig@exemple.cat', '$2y$10$xtBJ.oY8SZUXqoJcVZrkoumk5cMecuXAuzTOYLEvpMOrOec3o4eKm', 'client', NOW() - INTERVAL '5 days');

-- -----------------------------------------------------------------------------
-- esdeveniments
-- -----------------------------------------------------------------------------
INSERT INTO esdeveniments (
    titol,
    descripcio,
    data_esdeveniment,
    nom_recinte,
    url_imatge,
    llindar_n,
    latitud,
    longitud,
    actiu
)
VALUES
    (
        'Concert TR3 — Nit DICE',
        'Experiència immersiva amb artistes convidats i so en temps real.',
        '2026-06-15 21:00:00+02',
        'Palau Sant Jordi',
        'https://cdn.exemple.cat/imatges/concert-tr3-dice.jpg',
        10,
        41.3851,
        2.1734,
        TRUE
    ),
    (
        'Festival TicketMaster TR3',
        'Cap de setmana amb més d’una desena d’actuacions i zones de preu diferenciades.',
        '2026-07-20 18:00:00+02',
        'Parc del Fòrum',
        'https://cdn.exemple.cat/imatges/festival-tr3.jpg',
        100,
        41.3648,
        2.1557,
        TRUE
    );

-- -----------------------------------------------------------------------------
-- zones_de_seient
-- -----------------------------------------------------------------------------
INSERT INTO zones_de_seient (esdeveniment_id, nom_zona, preu_base, codi_color)
VALUES
    (1, 'VIP — Fila frontal', 100.00, '#C9A227'),
    (1, 'Pista General', 50.00, '#1E3A5F'),
    (2, 'Graderia Nord', 45.00, '#2D6A4F');

-- -----------------------------------------------------------------------------
-- seients
-- Esdeveniment 1: zona 1 (10 seients), zona 2 (10 seients). Esdeveniment 2: zona 3 (6 seients).
-- Mostra: venuts (1–2), reservat actiu (3), caducitat passada amb reserva inconsistent a netejar (4), resta disponibles.
-- -----------------------------------------------------------------------------
INSERT INTO seients (zona_id, etiqueta_fila, numero_seient, estat, retingut_per_usuari_id, caducitat_reserva)
VALUES
    (1, 'A', 1, 'venut', NULL, NULL),
    (1, 'A', 2, 'venut', NULL, NULL),
    (1, 'A', 3, 'reservat', 2, NOW() + INTERVAL '15 minutes'),
    (1, 'A', 4, 'disponible', NULL, NULL),
    (1, 'A', 5, 'disponible', NULL, NULL),
    (1, 'B', 1, 'disponible', NULL, NULL),
    (1, 'B', 2, 'disponible', NULL, NULL),
    (1, 'B', 3, 'disponible', NULL, NULL),
    (1, 'B', 4, 'disponible', NULL, NULL),
    (1, 'B', 5, 'disponible', NULL, NULL);

INSERT INTO seients (zona_id, etiqueta_fila, numero_seient, estat, retingut_per_usuari_id, caducitat_reserva)
VALUES
    (2, 'P', 1, 'disponible', NULL, NULL),
    (2, 'P', 2, 'disponible', NULL, NULL),
    (2, 'P', 3, 'disponible', NULL, NULL),
    (2, 'P', 4, 'disponible', NULL, NULL),
    (2, 'P', 5, 'disponible', NULL, NULL),
    (2, 'P', 6, 'disponible', NULL, NULL),
    (2, 'P', 7, 'disponible', NULL, NULL),
    (2, 'P', 8, 'disponible', NULL, NULL),
    (2, 'P', 9, 'disponible', NULL, NULL),
    (2, 'P', 10, 'disponible', NULL, NULL);

INSERT INTO seients (zona_id, etiqueta_fila, numero_seient, estat, retingut_per_usuari_id, caducitat_reserva)
VALUES
    (3, 'G', 1, 'disponible', NULL, NULL),
    (3, 'G', 2, 'disponible', NULL, NULL),
    (3, 'G', 3, 'disponible', NULL, NULL),
    (3, 'G', 4, 'disponible', NULL, NULL),
    (3, 'G', 5, 'disponible', NULL, NULL),
    (3, 'G', 6, 'disponible', NULL, NULL);

-- -----------------------------------------------------------------------------
-- comandes
-- Una comanda completada (Laia): dos seients VIP; una comanda pendent (Marc) sense tiquets encara.
-- -----------------------------------------------------------------------------
INSERT INTO comandes (usuari_id, import_total, estat, id_intencio_pagament, creat_el)
VALUES
    (2, 200.00, 'completada', 'pi_prova_completada_001', NOW() - INTERVAL '2 days'),
    (3, 90.00, 'pendent', 'pi_prova_pendent_002', NOW() - INTERVAL '10 minutes');

-- -----------------------------------------------------------------------------
-- tiquets
-- Vinculats als seients 1 i 2 (venuts) i a la primera comanda.
-- -----------------------------------------------------------------------------
INSERT INTO tiquets (comanda_id, seient_id, hash_qr, comprat_el)
VALUES
    (1, 1, 'sha256:prova_hash_unic_entrada_seient_01', NOW() - INTERVAL '2 days'),
    (1, 2, 'sha256:prova_hash_unic_entrada_seient_02', NOW() - INTERVAL '2 days');
