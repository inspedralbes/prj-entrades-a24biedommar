//================================ NAMESPACES / IMPORTS ============
const Redis = require('redis');
const { QueueService } = require('./services/QueueService');

//================================ VARIABLES / CONSTANTS ============

const REDIS_HOST = process.env.REDIS_HOST || 'localhost';
const REDIS_PORT = process.env.REDIS_PORT || 6379;

//================================ FUNCIONS / LÒGICA ================

/**
 * Servidor de Subscripció a Redis per a esdeveniments de Laravel
 * A. Esconnecta a Laravel a través de Redis Pub/Sub
 * B. Escolta canvis d'estat de la cua (threshold-updated, etc.)
 * C. Actualitza l'estat local de la cua
 */

/**
 * Inicialitza el client de subscripció a Redis
 * @returns {object} Client de Redis conectat
 */
async function inicialitzarSubscriber() {
    const subscriber = Redis.createClient({
        socket: {
            host: REDIS_HOST,
            port: REDIS_PORT
        }
    });

    subscriber.on('error', (err) => {
        console.error('❌ Error de connexió Redis:', err.message);
    });

    subscriber.on('connect', () => {
        console.log('✅ Connexió a Redis establerta');
    });

    await subscriber.connect();
    return subscriber;
}

/**
 * Configura les subscripcions als canals de cua
 * @param {object} subscriber - Client de Redis
 * @param {object} io - Instància de Socket.IO per a broadcasts
 */
async function configurarSubscripcions(subscriber, io) {
    // A. Subscriure al canal global de cues (per a tots els events)
    // Format: queue:{eventId} per a events específics
    // Format: queue:updates per a actualitzacions globals
    
    await subscriber.subscribe('queue:updates', (message) => {
        try {
            const data = JSON.parse(message);
            console.log('📨 Missatge rebut a queue:updates:', data);
            
            // B. Processar diferents tipus d'events
            switch (data.event_type) {
                case 'threshold-updated':
                    if (data.event_id && data.threshold_n) {
                        QueueService.actualitzarThresholdN(data.event_id, data.threshold_n);
                        
                        // C. Notificar a tots els usuaris d'aquell event
                        io.to(`queue:${data.event_id}`).emit('queue:threshold-updated', {
                            eventId: data.event_id,
                            thresholdN: data.threshold_n
                        });
                        
                        console.log(`📊 Threshold actualitzat per event ${data.event_id}: ${data.threshold_n}`);
                    }
                    break;
                    
                case 'event-started':
                    if (data.event_id) {
                        // Inicialitzar cua per a l'event
                        QueueService.inicialitzarCua(data.event_id);
                        console.log(`🎫 Event ${data.event_id} iniciat, cua preparada`);
                    }
                    break;
                    
                case 'event-ended':
                    if (data.event_id) {
                        // Netejar cua de l'event
                        QueueService.netejarCua(data.event_id);
                        console.log(`🏁 Event ${data.event_id} acabat, cua netejada`);
                    }
                    break;
                    
                default:
                    console.log('⚠️ Tipus d\'event desconegut:', data.event_type);
            }
        } catch (error) {
            console.error('❌ Error processant missatge de Redis:', error);
        }
    });

    console.log('📡 Subscrit al canal queue:updates');
}

/**
 * Funció per subscriure a un canal específic d'un event
 * @param {object} subscriber - Client de Redis
 * @param {string} eventId - ID de l'event
 * @param {object} io - Instància de Socket.IO
 */
async function subscriureEvent(subscriber, eventId, io) {
    const canal = `queue:${eventId}`;
    
    await subscriber.subscribe(canal, (message) => {
        try {
            const data = JSON.parse(message);
            console.log(`📨 Missatge rebut a ${canal}:`, data);
            
            // Processar events específics de l'event
            switch (data.event_type) {
                case 'user-joined':
                    // Actualitzar cua des de Laravel (si és necessari)
                    break;
                case 'user-left':
                    // Actualitzar cua des de Laravel
                    break;
                case 'threshold-updated':
                    if (data.threshold_n) {
                        QueueService.actualitzarThresholdN(eventId, data.threshold_n);
                        io.to(`queue:${eventId}`).emit('queue:threshold-updated', {
                            eventId,
                            thresholdN: data.threshold_n
                        });
                    }
                    break;
            }
        } catch (error) {
            console.error(`❌ Error processant missatge de ${canal}:`, error);
        }
    });
    
    console.log(`📡 Subscrit al canal ${canal}`);
}

/**
 * Configura la resposta a comandos administratius des de Redis
 * @param {object} subscriber - Client de Redis
 * @param {object} io - Instància de Socket.IO
 */
async function configurarAdminCommands(subscriber, io) {
    await subscriber.subscribe('admin:commands', (message) => {
        try {
            const data = JSON.parse(message);
            console.log('📨 Comanda administrativa rebuda:', data);
            
            switch (data.command) {
                case 'panic':
                    // Aturar totes les interaccions
                    io.emit('panic:mode', { 
                        active: true, 
                        message: data.message || 'Sistema aturat per administrador'
                    });
                    console.log('🛑 Mode pànic activat');
                    break;
                    
                case 'resume':
                    // Reprendre interaccions
                    io.emit('panic:mode', { 
                        active: false, 
                        message: 'Sistema reprès' 
                    });
                    console.log('▶️ Sistema reprès');
                    break;
                    
                case 'clear-queue':
                    if (data.event_id) {
                        QueueService.netejarCua(data.event_id);
                        io.to(`queue:${data.event_id}`).emit('queue:cleared', {
                            eventId: data.event_id
                        });
                        console.log(`🗑️ Cua netejada per event ${data.event_id}`);
                    }
                    break;
            }
        } catch (error) {
            console.error('❌ Error processant comanda administrativa:', error);
        }
    });
    
    console.log('📡 Subscrit al canal admin:commands');
}

//================================ EXPORTS ==========================

module.exports = {
    inicialitzarSubscriber,
    configurarSubscripcions,
    subscriureEvent,
    configurarAdminCommands
};