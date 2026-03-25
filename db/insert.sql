-- 🎫 TR3 TicketMaster Dummy Data

-- Users (Simple password)
INSERT INTO users (name, email, password, role) VALUES 
('Admin Account', 'admin@master.da3', 'pbkdf2:sha256:600000$p6G6...', 'admin'),
('John Doe Client', 'john@sample.com', 'pbkdf2:sha256:600000$p6G6...', 'client'),
('Jane Smith Client', 'jane@sample.com', 'pbkdf2:sha256:600000$p6G6...', 'client');

-- Events
INSERT INTO events (title, threshold_n, latitude, longitude) VALUES 
('Concert TR3 - DICE Experience', 10, 41.3851, 2.1734),
('Ticketmaster Festival', 100, 41.3648, 2.1557);

-- Zones for Event 1
INSERT INTO seat_zones (event_id, zone_name, base_price) VALUES 
(1, 'Vip Row', 100.00),
(1, 'General Standing', 50.00);

-- Seats for VIP Zone (Event 1, Zone 1) - 10 seats
INSERT INTO seats (zone_id, row_label, seat_number, status) VALUES 
(1, 'A', 1, 'available'), (1, 'A', 2, 'available'), (1, 'A', 3, 'available'), (1, 'A', 4, 'available'), (1, 'A', 5, 'available'),
(1, 'B', 1, 'available'), (1, 'B', 2, 'available'), (1, 'B', 3, 'available'), (1, 'B', 4, 'available'), (1, 'B', 5, 'available');

-- Seats for General Zone (Event 1, Zone 2) - 10 seats
INSERT INTO seats (zone_id, row_label, seat_number, status) VALUES 
(2, 'P', 1, 'available'), (2, 'P', 2, 'available'), (2, 'P', 3, 'available'), (2, 'P', 4, 'available'), (2, 'P', 5, 'available'),
(2, 'P', 6, 'available'), (2, 'P', 7, 'available'), (2, 'P', 8, 'available'), (2, 'P', 9, 'available'), (2, 'P', 10, 'available');
