//================================ NAMESPACES / IMPORTS ============

const express = require('express');
const { createServer } = require('node:http');
const { Server } = require('socket.io');
const Redis = require('redis');

//================================ VARIABLES / CONSTANTS ============

const app = express();
const server = createServer(app);
const io = new Server(server, {
    cors: {
        origin: '*',
    }
});

const PORT = process.env.PORT || 3001;
const REDIS_HOST = process.env.REDIS_HOST || 'localhost';

//================================ FUNCIONS / LÒGICA ================

function inicialitzarRedis() {
    // A. Crear client de Redis per subscriure's als canals de Laravel
    const subscriber = Redis.createClient({
        socket: {
            host: REDIS_HOST,
            port: 6379
        }
    });

    subscriber.on('error', (err) => {
        console.error('Error de connexió Redis:', err);
    });

    return subscriber;
}

function configurarSocketIO() {
    // A. Configurar esdeveniments de connexió
    io.on('connection', (socket) => {
        console.log('Usuari connectat:', socket.id);

        // B. Escoltar esdeveniments del client
        socket.on('unirse-evento', (eventId) => {
            socket.join(`evento-${eventId}`);
            console.log(`Usuari ${socket.id} unit a l'esdeveniment ${eventId}`);
        });

        socket.on('disconnect', () => {
            console.log('Usuari desconnectat:', socket.id);
        });
    });
}

function iniciarServidor() {
    // A. Iniciar el servidor al port especificat
    server.listen(PORT, () => {
        console.log(`Servidor Real-time iniciat al port ${PORT} ✅`);
    });
}

//================================ EXPORTS ==========================

// B. Iniciar l'aplicació
(async () => {
    const redisClient = inicialitzarRedis();
    await redisClient.connect();
    
    configurarSocketIO();
    iniciarServidor();
})();
