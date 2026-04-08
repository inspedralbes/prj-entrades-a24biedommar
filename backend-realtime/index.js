//================================ NAMESPACES / IMPORTS ============
require('dotenv').config();

const express = require('express');
const { createServer } = require('node:http');
const { Server } = require('socket.io');
const Redis = require('redis');

const { inicialitzarHandlersCua } = require('./src/sockets/gatekeeperHandlers');
const { 
    inicialitzarSubscriber, 
    configurarSubscripcions,
    configurarAdminCommands 
} = require('./src/services/redisSubscriber');

//================================ VARIABLES / CONSTANTS ============

const app = express();
const server = createServer(app);

const io = new Server(server, {
    cors: {
        origin: process.env.CORS_ORIGIN || '*',
        methods: ['GET', 'POST'],
        credentials: true
    }
});

const PORT = process.env.PORT || 3001;
const REDIS_HOST = process.env.REDIS_HOST || 'localhost';
const REDIS_PORT = process.env.REDIS_PORT || 6379;

//================================ MIDDLEWARE =======================

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'ok', service: 'gatekeeper' });
});

app.get('/', (req, res) => {
    res.json({ 
        service: 'The Gatekeeper - TicketMaster',
        version: '1.0.0',
        status: 'running'
    });
});

//================================ FUNCIONS / LÒGICA ================

/**
 * Inicialitza el servidor de temps real
 * A. Configura Socket.IO
 * B. Inicialitza el subscriber de Redis
 * C. Inicia el servidor HTTP
 */
async function iniciarServidor() {
    try {
        // A. Inicialitzar handlers de Socket.IO per a la cua
        inicialitzarHandlersCua(io);
        console.log('✅ Handlers de cua inicialitzats');

        // B. Inicialitzar Redis subscriber
        let redisSubscriber = null;
        
        try {
            redisSubscriber = await inicialitzarSubscriber();
            
            // C. Configurar subscripcions
            await configurarSubscripcions(redisSubscriber, io);
            await configurarAdminCommands(redisSubscriber, io);
            
            console.log('✅ Redis subscriber configurat');
        } catch (redisError) {
            console.warn('⚠️ Redis no disponible, continuant sense subscripcions:', redisError.message);
        }

        // D. Iniciar servidor
        server.listen(PORT, () => {
            console.log(`🛡️ The Gatekeeper iniciat al port ${PORT} ✅`);
            console.log(`   WebSocket: ws://localhost:${PORT}`);
            console.log(`   Health: http://localhost:${PORT}/health`);
        });

    } catch (error) {
        console.error('❌ Error iniciant el servidor:', error);
        process.exit(1);
    }
}

//================================ INICI ===========================

iniciarServidor();

// Handle shutdown graceful
process.on('SIGTERM', () => {
    console.log('📡 SIGTERM rebut, tancant servidor...');
    server.close(() => {
        console.log('✅ Servidor tancat');
        process.exit(0);
    });
});