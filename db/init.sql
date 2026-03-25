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
