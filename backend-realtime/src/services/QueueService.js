//================================ NAMESPACES / IMPORTS ============
const Redis = require('redis');
const jwt = require('jsonwebtoken');

//================================ VARIABLES / CONSTANTS ============

const JWT_SECRET = process.env.JWT_SECRET || 'ticketmaster-secret-key';
const TURN_TOKEN_TTL = 300; // 5 minuts en segons

// Estat de la cua per a cada event (in-memory)
const queues = new Map(); // eventId -> { users: Map<socketId, userData>, waitlist: [] }
// Threshold N per defecte si no es consulta a Laravel
const DEFAULT_THRESHOLD_N = 100;

//================================ SERVICIS / LÒGICA ================

/**
 * servei de Gestió de Cua (The Gatekeeper)
 * A. Manté l'estat de la cua per a cada event
 * B. Gestiona l'entrada, sortida i avanç de posicions
 * C. Genera turn tokens quan l'usuari arriba al davant
 */
const QueueService = {
    /**
     * Inicialitza l'estat de la cua per a un event si no existeix
     * @param {string} eventId - Identificador de l'event
     */
    inicialitzarCua(eventId) {
        if (!queues.has(eventId)) {
            queues.set(eventId, {
                users: new Map(), // socketId -> { userId, eventId, position, joinedAt }
                waitlist: [], // usuaris que esperen quan la cua està plena
                thresholdN: DEFAULT_THRESHOLD_N
            });
        }
        return queues.get(eventId);
    },

    /**
     * Obté el threshold N per a un event
     * @param {string} eventId - Identificador de l'event
     * @returns {number} Threshold N
     */
    obtenirThresholdN(eventId) {
        const cua = queues.get(eventId);
        return cua ? cua.thresholdN : DEFAULT_THRESHOLD_N;
    },

    /**
     * Actualitza el threshold N per a un event
     * @param {string} eventId - Identificador de l'event
     * @param {number} threshold - Nou valor del threshold
     */
    actualitzarThresholdN(eventId, threshold) {
        const cua = this.inicialitzarCua(eventId);
        cua.thresholdN = threshold;
    },

    /**
     * Afegeix un usuari a la cua d'un event
     * @param {string} socketId - ID del socket
     * @param {string} userId - ID de l'usuari
     * @param {string} eventId - ID de l'event
     * @returns {object} Resultat de l'operació
     */
    afegirUsuari(socketId, userId, eventId) {
        const cua = this.inicialitzarCua(eventId);
        
        // A. Comprovar si l'usuari ja és a la cua
        for (const [sid, usuari] of cua.users) {
            if (usuari.userId === userId) {
                return { 
                    success: false, 
                    message: 'Ja estàs a la cua', 
                    position: usuari.position 
                };
            }
        }

        // B. Comprovar si la cua està plena
        const nombreActius = cua.users.size;
        
        if (nombreActius >= cua.thresholdN) {
            // C. Afegir a la waitlist
            cua.waitlist.push({ socketId, userId, eventId, joinedAt: Date.now() });
            return {
                success: false,
                message: 'Cua plena',
                position: null,
                waitlistPosition: cua.waitlist.length,
                queueSize: nombreActius
            };
        }

        // D. Afegir a la cua activa
        const position = nombreActius + 1;
        cua.users.set(socketId, { 
            userId, 
            eventId, 
            position, 
            joinedAt: Date.now() 
        });

        return {
            success: true,
            position,
            message: 'Entrat a la cua',
            queueSize: nombreActius + 1
        };
    },

    /**
     * Elimina un usuari de la cua i actualitza posicions (decreixent)
     * @param {string} socketId - ID del socket
     * @param {string} eventId - ID de l'event
     * @returns {object} Dades de l'usuari eliminat o null
     */
    eliminarUsuari(socketId, eventId) {
        const cua = queues.get(eventId);
        if (!cua) return null;

        // A. Comprovar si l'usuari és a la cua activa
        const usuariEliminat = cua.users.get(socketId);
        
        if (usuariEliminat) {
            cua.users.delete(socketId);
            
            // B. Actualitzar posicions decreixent (tothom davant avança)
            let posicio = 1;
            for (const [sid, usuari] of cua.users) {
                usuari.position = posicio++;
            }

            // C. Moure usuari de waitlist a cua activa si n'hi ha
            if (cua.waitlist.length > 0 && cua.users.size < cua.thresholdN) {
                const seguent = cua.waitlist.shift();
                const novaPosicio = cua.users.size + 1;
                cua.users.set(seguent.socketId, {
                    userId: seguent.userId,
                    eventId: seguent.eventId,
                    position: novaPosicio,
                    joinedAt: seguent.joinedAt
                });
            }

            return usuariEliminat;
        }

        // D. Comprovar si és a la waitlist
        const indexWaitlist = cua.waitlist.findIndex(u => u.socketId === socketId);
        if (indexWaitlist !== -1) {
            cua.waitlist.splice(indexWaitlist, 1);
            return { userId: usuariEliminat?.userId, fromWaitlist: true };
        }

        return null;
    },

    /**
     * Obté la posició d'un usuari a la cua
     * @param {string} socketId - ID del socket
     * @param {string} eventId - ID de l'event
     * @returns {object|null} Posició de l'usuari
     */
    obtenirPosicio(socketId, eventId) {
        const cua = queues.get(eventId);
        if (!cua) return null;

        const usuari = cua.users.get(socketId);
        if (usuari) {
            return {
                position: usuari.position,
                queueSize: cua.users.size,
                thresholdN: cua.thresholdN,
                isFirst: usuari.position === 1
            };
        }

        // Comprovar waitlist
        const waitIndex = cua.waitlist.findIndex(u => u.socketId === socketId);
        if (waitIndex !== -1) {
            return {
                position: null,
                waitlistPosition: waitIndex + 1,
                waitlistSize: cua.waitlist.length,
                inWaitlist: true
            };
        }

        return null;
    },

    /**
     * Obté tots els usuaris actius d'una cua
     * @param {string} eventId - ID de l'event
     * @returns {array} Llista d'usuaris
     */
    obtenirUsuarisCua(eventId) {
        const cua = queues.get(eventId);
        if (!cua) return [];
        
        return Array.from(cua.users.values());
    },

    /**
     * Genera un turn token JWT per a l'usuari
     * @param {string} userId - ID de l'usuari
     * @param {string} eventId - ID de l'event
     * @returns {string} Token JWT
     */
    generarTurnToken(userId, eventId) {
        const payload = {
            user_id: userId,
            event_id: eventId,
            issued_at: Date.now(),
            expires_at: Date.now() + (TURN_TOKEN_TTL * 1000),
            token_type: 'turn',
            turn_token: true
        };

        return jwt.sign(payload, JWT_SECRET, { expiresIn: TURN_TOKEN_TTL });
    },

    /**
     * Valida un turn token
     * @param {string} token - Token JWT
     * @returns {object|null} Payload del token o null si invàlid
     */
    validarTurnToken(token) {
        try {
            const decoded = jwt.verify(token, JWT_SECRET);
            if (decoded.token_type === 'turn' && decoded.turn_token === true) {
                return decoded;
            }
            return null;
        } catch (error) {
            return null;
        }
    },

    /**
     * Comprova si un usuari és primer a la cua i genera turn token
     * @param {string} socketId - ID del socket
     * @param {string} eventId - ID de l'event
     * @returns {object|null} Turn token si és primer, null altrament
     */
    verificarTurn(socketId, eventId) {
        const posicio = this.obtenirPosicio(socketId, eventId);
        
        if (posicio && posicio.isFirst) {
            const cua = queues.get(eventId);
            const usuari = cua?.users.get(socketId);
            
            if (usuari) {
                const turnToken = this.generarTurnToken(usuari.userId, eventId);
                return {
                    turnToken,
                    expiresAt: Date.now() + (TURN_TOKEN_TTL * 1000)
                };
            }
        }
        
        return null;
    },

    /**
     * Neteja la cua d'un event
     * @param {string} eventId - ID de l'event
     */
    netejarCua(eventId) {
        if (queues.has(eventId)) {
            const cua = queues.get(eventId);
            cua.users.clear();
            cua.waitlist = [];
        }
    },

    /**
     * Obté estadístiques d'una cua
     * @param {string} eventId - ID de l'event
     * @returns {object} Estadístiques
     */
    obtenirEstadistiques(eventId) {
        const cua = queues.get(eventId);
        if (!cua) {
            return { 
                activeUsers: 0, 
                waitlistSize: 0, 
                thresholdN: DEFAULT_THRESHOLD_N,
                capacity: DEFAULT_THRESHOLD_N 
            };
        }

        return {
            activeUsers: cua.users.size,
            waitlistSize: cua.waitlist.length,
            thresholdN: cua.thresholdN,
            capacity: cua.thresholdN - cua.users.size
        };
    }
};

//================================ EXPORTS ==========================

module.exports = { 
    QueueService,
    queues,
    DEFAULT_THRESHOLD_N,
    JWT_SECRET,
    TURN_TOKEN_TTL
};