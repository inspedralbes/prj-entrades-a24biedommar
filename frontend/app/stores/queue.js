//================================ NAMESPACES / IMPORTS ============
import { defineStore } from 'pinia';

//================================ ESTAT / VARIABLES ===============

/**
 * Store per gestionar l'estat de la cua virtual (The Gatekeeper).
 * A. Manté la posició de l'usuari a la cua.
 * B. Gestiona la connexió WebSocket.
 * C. Controla l'estat del turn token.
 */
export const useQueueStore = defineStore('queue', () => {
    // Estat reactiu
    const eventId = ref(null);
    const position = ref(null);
    const queueSize = ref(0);
    const thresholdN = ref(100);
    const isFirst = ref(false);
    const isInWaitlist = ref(false);
    const waitlistPosition = ref(null);
    const turnToken = ref(null);
    const turnExpiresAt = ref(null);
    const isConnected = ref(false);
    const isReconnecting = ref(false);
    const error = ref(null);
    const eventName = ref('');
    const eventTime = ref('');
    const isPanicMode = ref(false);
    const panicMessage = ref('');

    // Socket (s'inicialitzarà des del composable)
    let socket = null;

    //================================ ACCIONS / MÈTODES =============

    /**
     * Inicialitza la connexió a la cua per a un event.
     * A. Rep l'ID de l'event i el token d'autenticació.
     * B. Estableix la connexió Socket.IO.
     */
    function inicialitzarCua(idEvent, tokenAutenticacio, nomEvent = '', horaEvent = '') {
        if (socket) {
            socket.removeAllListeners();
            socket.disconnect();
            socket = null;
        }

        eventId.value = idEvent;
        eventName.value = nomEvent;
        eventTime.value = horaEvent;
        
        // Import dinàmic per evitar circular dependency
        import('socket.io-client').then(({ io }) => {
            const envUrl = typeof import.meta !== 'undefined' && import.meta.env
                ? import.meta.env.NUXT_PUBLIC_SOCKET_URL
                : '';
            const GATEKEEPER_URL = envUrl || 'http://localhost:3001';

            socket = io(GATEKEEPER_URL, {
                auth: { token: tokenAutenticacio },
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: 10,
                reconnectionDelay: 1000,
            });

            // Handler: connectat
            socket.on('connect', () => {
                isConnected.value = true;
                isReconnecting.value = false;
                error.value = null;
                console.log('✅ Connectat a The Gatekeeper');

                // Unir-se a la cua de l'event
                socket.emit('queue:join', { eventId: idEvent });
            });

            // Handler: connect_error
            socket.on('connect_error', (err) => {
                error.value = err.message;
                isConnected.value = false;
                console.error('❌ Error de connexió:', err.message);
            });

            // Handler: disconnect
            socket.on('disconnect', (reason) => {
                isConnected.value = false;
                console.log('🔌 Desconnectat:', reason);
            });

            // Handler: reconnecting
            socket.on('reconnecting', () => {
                isReconnecting.value = true;
                console.log('🔄 Reconnectant...');
            });

            // Handler: connected
            socket.on('connected', (data) => {
                console.log('🔌 Socket ID:', data.socketId);
            });

            // Handler: queue:joined
            socket.on('queue:joined', (data) => {
                position.value = data.position;
                queueSize.value = data.queueSize;
                isInWaitlist.value = false;
                console.log(`📝 Unit a cua en posició ${data.position}`);
            });

            // Handler: queue:waitlist
            socket.on('queue:waitlist', (data) => {
                isInWaitlist.value = true;
                waitlistPosition.value = data.waitlistPosition;
                position.value = null;
                console.log(`📝 Afegit a waitlist en posició ${data.waitlistPosition}`);
            });

            // Handler: queue:position-update
            socket.on('queue:position-update', (data) => {
                position.value = data.position;
                queueSize.value = data.queueSize;
                isFirst.value = data.isFirst;
            });

            // Handler: queue:update
            socket.on('queue:update', (data) => {
                queueSize.value = data.queueSize || data.activeUsers;
                thresholdN.value = data.thresholdN;
            });

            // Handler: queue:turn-granted
            socket.on('queue:turn-granted', (data) => {
                turnToken.value = data.turnToken;
                turnExpiresAt.value = data.expiresAt;
                isFirst.value = true;
                position.value = 1;
                console.log('🎫 Turn token obtingut!');
            });

            // Handler: queue:turn-denied
            socket.on('queue:turn-denied', () => {
                isFirst.value = false;
            });

            // Handler: queue:left
            socket.on('queue:left', () => {
                position.value = null;
                isInWaitlist.value = false;
                waitlistPosition.value = null;
                turnToken.value = null;
                console.log('🚪 Has sortit de la cua');
            });

            // Handler: queue:error
            socket.on('queue:error', (data) => {
                error.value = data.message;
                console.error('❌ Error de cua:', data.message);
            });

            // Handler: queue:threshold-updated
            socket.on('queue:threshold-updated', (data) => {
                thresholdN.value = data.thresholdN;
            });

            // Handler: panic:mode
            socket.on('panic:mode', (data) => {
                isPanicMode.value = data.active;
                panicMessage.value = data.message;
                if (data.active) {
                    console.log('🛑 Mode pànic activat:', data.message);
                } else {
                    console.log('▶️ Mode pànic desactivat');
                }
            });

            // Handler: queue:status-response
            socket.on('queue:status-response', (data) => {
                position.value = data.position;
                queueSize.value = data.activeUsers;
                thresholdN.value = data.thresholdN;
                isFirst.value = data.isFirst;
            });
        });
    }

    /**
     * Demana actualització de l'estat de la cua.
     */
    function actualitzarEstat() {
        if (socket && eventId.value) {
            socket.emit('queue:status', { eventId: eventId.value });
        }
    }

    /**
     * Demana el turn token (per accedir al mapa).
     */
    function demanarTurn() {
        if (socket && eventId.value) {
            socket.emit('queue:get-turn', { eventId: eventId.value });
        }
    }

    /**
     * Surt de la cua.
     */
    function sortirCua() {
        if (socket && eventId.value) {
            socket.emit('queue:leave', { eventId: eventId.value });
        }
    }

    /**
     * Desconnecta el socket.
     */
    function desconnectar() {
        if (socket) {
            socket.disconnect();
            socket = null;
        }
        isConnected.value = false;
        position.value = null;
        eventId.value = null;
    }

    /**
     * Reseteja l'estat de la cua.
     */
    function reset() {
        position.value = null;
        queueSize.value = 0;
        thresholdN.value = 100;
        isFirst.value = false;
        isInWaitlist.value = false;
        waitlistPosition.value = null;
        turnToken.value = null;
        turnExpiresAt.value = null;
        isConnected.value = false;
        isReconnecting.value = false;
        error.value = null;
        isPanicMode.value = false;
        panicMessage.value = '';
    }

    //================================ COMPUTED ====================

    /**
     * Calcula el temps d'espera estimat (en minuts).
     * Estimació: 2 minuts per persona davant.
     */
    const tempsEsperaEstimats = computed(() => {
        if (isInWaitlist.value && waitlistPosition.value) {
            return (position.value || 0) * 2 + waitlistPosition.value * 2;
        }
        return (position.value || 0) * 2;
    });

    /**
     * Determina si l'usuari pot accedir al mapa.
     */
    const potAccedirMapa = computed(() => {
        return turnToken.value !== null && isFirst.value;
    });

    /**
     * Retorna l'estat de la cua per mostrar.
     */
    const estatCua = computed(() => {
        if (isPanicMode.value) {
            return { tipus: 'panic', missatge: panicMessage.value };
        }
        if (isInWaitlist.value) {
            return { tipus: 'waitlist', posicio: waitlistPosition.value };
        }
        if (isFirst.value) {
            return { tipus: 'ready' };
        }
        return { tipus: 'waiting', posicio: position.value };
    });

    //================================ EXPORT ======================

    return {
        // Estat
        eventId,
        position,
        queueSize,
        thresholdN,
        isFirst,
        isInWaitlist,
        waitlistPosition,
        turnToken,
        turnExpiresAt,
        isConnected,
        isReconnecting,
        error,
        eventName,
        eventTime,
        isPanicMode,
        panicMessage,
        
        // Computed
        tempsEsperaEstimats,
        potAccedirMapa,
        estatCua,
        
        // Accions
        inicialitzarCua,
        actualitzarEstat,
        demanarTurn,
        sortirCua,
        desconnectar,
        reset
    };
});