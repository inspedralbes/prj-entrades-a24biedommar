//================================ NAMESPACES / IMPORTS ============
const { QueueService } = require('../services/QueueService');
const { validarTokenHandshake, validarToken } = require('../middleware/authMiddleware');

//================================ VARIABLES / CONSTANTS ============

//================================ SOCKET HANDLERS / LÒGICA ================

/**
 * Configura els handlers de Socket.IO per a The Gatekeeper
 * A. Gestió de connexió i desconnexió
 * B. Unir-se a una cua d'event
 * C. Actualitzacions de posició
 * D. Gestió de turn tokens
 */

/**
 * Inicialitza els handlers de socket per a la cua virtual
 * @param {object} io - Instància de Socket.IO
 */
function inicialitzarHandlersCua(io) {
    // A. Handler de connexió
    io.on('connection', (socket) => {
        console.log(`🔌 Usuari connectat: ${socket.id}`);
        
        // B. Validar token d'autenticació
        const auth = validarTokenHandshake(socket);
        
        if (!auth.valid) {
            console.log(`❌ Connexió rebutjada per a ${socket.id}: ${auth.error}`);
            socket.emit('error', { message: auth.error });
            socket.disconnect();
            return;
        }

        // C. desar dades de l'usuari al socket
        socket.data.userId = auth.userId;
        socket.data.email = auth.email;
        socket.data.role = auth.role;
        socket.data.events = []; // Events on l'usuari està apuntat

        console.log(`✅ Usuari ${auth.userId} connectat a Gatekeeper`);
        
        // D. Enviar confirmació de connexió
        socket.emit('connected', { 
            userId: auth.userId,
            socketId: socket.id 
        });

        // E. Handler: Unir-se a la cua d'un event
        socket.on('queue:join', (data) => {
            const { eventId } = data;
            
            if (!eventId) {
                socket.emit('queue:error', { message: 'eventId és obligatori' });
                return;
            }

            // F. Comprovar si l'usuari ja està a la cua d'aquest event
            if (socket.data.events.includes(eventId)) {
                socket.emit('queue:error', { message: 'Ja estàs a la cua d\'aquest event' });
                return;
            }

            // G. Afegir usuari a la cua
            const resultat = QueueService.afegirUsuari(socket.id, auth.userId, eventId);
            
            if (resultat.success) {
                // H. Guardar event a la llista de l'usuari
                socket.data.events.push(eventId);
                
                // I. Unir socket a la sala de l'event
                socket.join(`queue:${eventId}`);
                
                // J. Notificar a l'usuari
                socket.emit('queue:joined', {
                    eventId,
                    position: resultat.position,
                    queueSize: resultat.queueSize,
                    message: resultat.message
                });

                // K. Broadcast a la cua (per si d'altres volen saber la mida)
                io.to(`queue:${eventId}`).emit('queue:update', {
                    eventId,
                    queueSize: resultat.queueSize,
                    thresholdN: QueueService.obtenirThresholdN(eventId)
                });

                console.log(`📝 Usuari ${auth.userId} unit a cua ${eventId} en posició ${resultat.position}`);
                
                // L. Comprovar si és el primer i notificar
                if (resultat.position === 1) {
                    const turnData = QueueService.verificarTurn(socket.id, eventId);
                    if (turnData) {
                        socket.emit('queue:turn-granted', {
                            turnToken: turnData.turnToken,
                            expiresAt: turnData.expiresAt,
                            message: 'És el teu torn! Pots accedir al mapa de seients.'
                        });
                    }
                }
            } else {
                // M. Cua plena - afegir a waitlist
                if (resultat.waitlistPosition) {
                    socket.emit('queue:waitlist', {
                        eventId,
                        waitlistPosition: resultat.waitlistPosition,
                        message: resultat.message
                    });
                } else {
                    socket.emit('queue:error', { message: resultat.message });
                }
            }
        });

        // N. Handler: Obtenir estat de la cua
        socket.on('queue:status', (data) => {
            const { eventId } = data;
            
            if (!eventId) {
                socket.emit('queue:error', { message: 'eventId és obligatori' });
                return;
            }

            const posicio = QueueService.obtenirPosicio(socket.id, eventId);
            const estadistiques = QueueService.obtenirEstadistiques(eventId);

            socket.emit('queue:status-response', {
                ...posicio,
                ...estadistiques
            });
        });

        // O. Handler: Sortir de la cua
        socket.on('queue:leave', (data) => {
            const { eventId } = data;
            
            if (!eventId) {
                socket.emit('queue:error', { message: 'eventId és obligatori' });
                return;
            }

            // P. Eliminar usuari de la cua
            const eliminated = QueueService.eliminarUsuari(socket.id, eventId);
            
            if (eliminated) {
                // Q. Treure de la llista d'esdeveniments de l'usuari
                socket.data.events = socket.data.events.filter(e => e !== eventId);
                
                // R. Sortir de la sala
                socket.leave(`queue:${eventId}`);
                
                // S. Notificar a l'usuari
                socket.emit('queue:left', {
                    eventId,
                    message: 'Has sortit de la cua'
                });

                // T. Notificar a la cua que algú ha sortit
                io.to(`queue:${eventId}`).emit('queue:update', {
                    eventId,
                    action: 'user-left',
                    ...QueueService.obtenirEstadistiques(eventId)
                });

                console.log(`🚪 Usuari ${auth.userId} ha sortit de la cua ${eventId}`);
            } else {
                socket.emit('queue:error', { message: 'No estaves a la cua d\'aquest event' });
            }
        });

        // U. Handler: Demanar turn (per accedir al mapa)
        socket.on('queue:get-turn', (data) => {
            const { eventId } = data;
            
            if (!eventId) {
                socket.emit('queue:error', { message: 'eventId és obligatori' });
                return;
            }

            const turnData = QueueService.verificarTurn(socket.id, eventId);
            
            if (turnData) {
                socket.emit('queue:turn-granted', {
                    turnToken: turnData.turnToken,
                    expiresAt: turnData.expiresAt,
                    message: 'És el teu torn! Pots accedir al mapa de seients.'
                });
            } else {
                socket.emit('queue:turn-denied', {
                    message: 'Encara no és el teu torn'
                });
            }
        });

        // V. Handler: Desconnectar
        socket.on('disconnect', () => {
            console.log(`🔌 Usuari desconnectat: ${socket.id} (${auth.userId})`);
            
            // W. Eliminar de totes les cues on estava
            if (socket.data.events && socket.data.events.length > 0) {
                socket.data.events.forEach(eventId => {
                    const eliminated = QueueService.eliminarUsuari(socket.id, eventId);
                    
                    if (eliminated) {
                        // Notificar a la cua
                        io.to(`queue:${eventId}`).emit('queue:update', {
                            eventId,
                            action: 'user-disconnected',
                            ...QueueService.obtenirEstadistiques(eventId)
                        });
                    }
                });
            }
        });
    });
}

//================================ EXPORTS ==========================

module.exports = { inicialitzarHandlersCua };