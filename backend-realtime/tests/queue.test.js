//================================ TESTS / PROVES ==================

const { QueueService, DEFAULT_THRESHOLD_N } = require('./src/services/QueueService');

// Test helper per netejar l'estat entre tests
function netejarEstat() {
    // Netejar totes les cues
    QueueService.queues.forEach((cua, eventId) => {
        QueueService.netejarCua(eventId);
    });
}

describe('QueueService - Gestió de Cua', () => {
    beforeEach(() => {
        netejarEstat();
    });

    describe('afegirUsuari', () => {
        test('Afegir primer usuari a la cua', () => {
            const resultat = QueueService.afegirUsuari('socket1', 'user1', 'event1');
            
            expect(resultat.success).toBe(true);
            expect(resultat.position).toBe(1);
            expect(resultat.message).toBe('Entrat a la cua');
        });

        test('Afegir segon usuari a la cua', () => {
            QueueService.afegirUsuari('socket1', 'user1', 'event1');
            const resultat = QueueService.afegirUsuari('socket2', 'user2', 'event1');
            
            expect(resultat.success).toBe(true);
            expect(resultat.position).toBe(2);
        });

        test('Usuari duplicat rebutjat', () => {
            QueueService.afegirUsuari('socket1', 'user1', 'event1');
            const resultat = QueueService.afegirUsuari('socket2', 'user1', 'event1');
            
            expect(resultat.success).toBe(false);
            expect(resultat.message).toBe('Ja estàs a la cua');
        });

        test('Cua plena -> waitlist', () => {
            // Threshold per defecte és 100,posem un de petit per testejar
            QueueService.actualitzarThresholdN('event2', 2);
            
            QueueService.afegirUsuari('socket1', 'user1', 'event2');
            QueueService.afegirUsuari('socket2', 'user2', 'event2');
            
            // Ara la cua està plena (2 usuaris, threshold 2)
            const resultat = QueueService.afegirUsuari('socket3', 'user3', 'event2');
            
            expect(resultat.success).toBe(false);
            expect(resultat.message).toBe('Cua plena');
            expect(resultat.waitlistPosition).toBe(1);
        });
    });

    describe('eliminarUsuari - Posicionament Decreixent', () => {
        test('Eliminar usuari actualitza posicions decreixent', () => {
            // Afegir 3 usuaris
            QueueService.afegirUsuari('socket1', 'user1', 'event1');
            QueueService.afegirUsuari('socket2', 'user2', 'event1');
            QueueService.afegirUsuari('socket3', 'user3', 'event1');
            
            // Eliminar el primer (user1)
            const eliminat = QueueService.eliminarUsuari('socket1', 'event1');
            
            expect(eliminat.userId).toBe('user1');
            
            // Comprovar que user2 ara és posició 1 i user3 és posició 2
            const pos2 = QueueService.obtenirPosicio('socket2', 'event1');
            const pos3 = QueueService.obtenirPosicio('socket3', 'event1');
            
            expect(pos2.position).toBe(1);
            expect(pos3.position).toBe(2);
        });

        test('Eliminar usuari del mig', () => {
            QueueService.afegirUsuari('socket1', 'user1', 'event1');
            QueueService.afegirUsuari('socket2', 'user2', 'event1');
            QueueService.afegirUsuari('socket3', 'user3', 'event1');
            
            // Eliminar el del mig
            QueueService.eliminarUsuari('socket2', 'event1');
            
            const pos1 = QueueService.obtenirPosicio('socket1', 'event1');
            const pos3 = QueueService.obtenirPosicio('socket3', 'event1');
            
            expect(pos1.position).toBe(1);
            expect(pos3.position).toBe(2);
        });
    });

    describe('Waitlist -> Cua Activa', () => {
        test('Usuari de waitlist promociona quan allibera espai', () => {
            // Cua amb threshold 1
            QueueService.actualitzarThresholdN('event-wl', 1);
            
            // Afegir usuari 1 (omple la cua)
            QueueService.afegirUsuari('socket1', 'user1', 'event-wl');
            
            // Afegir usuari 2 a waitlist
            QueueService.afegirUsuari('socket2', 'user2', 'event-wl');
            
            // Eliminar usuari 1 (allibera espai)
            QueueService.eliminarUsuari('socket1', 'event-wl');
            
            // Comprovar que usuari 2 és ara a la cua activa
            const pos2 = QueueService.obtenirPosicio('socket2', 'event-wl');
            
            expect(pos2.position).toBe(1);
            expect(pos2.inWaitlist).toBeUndefined();
        });
    });

    describe('Turn Token', () => {
        test('Generar turn token per a usuari primer', () => {
            QueueService.afegirUsuari('socket1', 'user1', 'event1');
            
            const turnData = QueueService.verificarTurn('socket1', 'event1');
            
            expect(turnData).not.toBeNull();
            expect(turnData.turnToken).toBeDefined();
            expect(turnData.expiresAt).toBeDefined();
            
            // Validar el token
            const decoded = QueueService.validarTurnToken(turnData.turnToken);
            expect(decoded.user_id).toBe('user1');
            expect(decoded.event_id).toBe('event1');
            expect(decoded.token_type).toBe('turn');
            expect(decoded.turn_token).toBe(true);
        });

        test('No generar turn token per a usuari no primer', () => {
            QueueService.afegirUsuari('socket1', 'user1', 'event1');
            QueueService.afegirUsuari('socket2', 'user2', 'event1');
            
            const turnData = QueueService.verificarTurn('socket2', 'event1');
            
            expect(turnData).toBeNull();
        });

        test('Turn token invàlid rebutjat', () => {
            const resultat = QueueService.validarTurnToken('token-invalid');
            
            expect(resultat).toBeNull();
        });
    });

    describe('Threshold N', () => {
        test('Threshold N per defecte', () => {
            const threshold = QueueService.obtenirThresholdN('event-desconegut');
            expect(threshold).toBe(DEFAULT_THRESHOLD_N);
        });

        test('Actualitzar threshold N', () => {
            QueueService.actualitzarThresholdN('event1', 50);
            
            const threshold = QueueService.obtenirThresholdN('event1');
            expect(threshold).toBe(50);
        });

        test('Cua plena amb threshold 100 vs threshold 5', () => {
            // Threshold 100 - gairebé mai es plena
            QueueService.actualitzarThresholdN('event-gran', 100);
            for (let i = 1; i <= 100; i++) {
                const resultat = QueueService.afegirUsuari(`socket${i}`, `user${i}`, 'event-gran');
                if (i < 100) {
                    expect(resultat.success).toBe(true);
                }
            }
            
            // Threshold 5 - es plena ràpid
            QueueService.actualitzarThresholdN('event-petit', 5);
            QueueService.afegirUsuari('s1', 'u1', 'event-petit');
            QueueService.afegirUsuari('s2', 'u2', 'event-petit');
            QueueService.afegirUsuari('s3', 'u3', 'event-petit');
            QueueService.afegirUsuari('s4', 'u4', 'event-petit');
            QueueService.afegirUsuari('s5', 'u5', 'event-petit');
            
            const resultatPle = QueueService.afegirUsuari('s6', 'u6', 'event-petit');
            expect(resultatPle.success).toBe(false);
            expect(resultatPle.message).toBe('Cua plena');
        });
    });

    describe('Estadístiques', () => {
        test('Estadístiques buides', () => {
            const stats = QueueService.obtenirEstadistiques('event-buit');
            
            expect(stats.activeUsers).toBe(0);
            expect(stats.waitlistSize).toBe(0);
            expect(stats.thresholdN).toBe(DEFAULT_THRESHOLD_N);
        });

        test('Estadístiques amb usuaris', () => {
            QueueService.actualitzarThresholdN('event-stats', 10);
            QueueService.afegirUsuari('socket1', 'user1', 'event-stats');
            QueueService.afegirUsuari('socket2', 'user2', 'event-stats');
            QueueService.afegirUsuari('socket3', 'user3', 'event-stats');
            
            const stats = QueueService.obtenirEstadistiques('event-stats');
            
            expect(stats.activeUsers).toBe(3);
            expect(stats.thresholdN).toBe(10);
            expect(stats.capacity).toBe(7);
        });
    });
});

describe('authMiddleware - Validació de Token', () => {
    const jwt = require('jsonwebtoken');
    const JWT_SECRET = process.env.JWT_SECRET || 'ticketmaster-secret-key';
    const { validarToken, validarTokenHandshake } = require('./src/middleware/authMiddleware');
    
    test('Token vàlid', () => {
        const token = jwt.sign({ 
            user_id: 'user123', 
            email: 'test@example.com',
            role: 'client' 
        }, JWT_SECRET);
        
        const resultat = validarToken(token);
        
        expect(resultat).not.toBeNull();
        expect(resultat.userId).toBe('user123');
    });

    test('Token invàlid', () => {
        const resultat = validarToken('token-invalid');
        
        expect(resultat).toBeNull();
    });

    test('Turn token rebutjat per connexió', () => {
        const token = jwt.sign({ 
            user_id: 'user123', 
            token_type: 'turn',
            turn_token: true
        }, JWT_SECRET);
        
        const resultat = validarToken(token);
        
        expect(resultat).toBeNull();
    });
});