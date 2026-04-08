<template>
    <div class="waiting-room">
        <!-- Estat: Panico -->
        <div v-if="queueStore.isPanicMode" class="panic-overlay">
            <div class="panic-card">
                <div class="panic-icon">🛑</div>
                <h2>Sistema Aturat</h2>
                <p>{{ queueStore.panicMessage }}</p>
                <p class="subtitle">Si us plau, espera actualitzacions.</p>
            </div>
        </div>

        <!-- Estat: Reconectant -->
        <div v-else-if="queueStore.isReconnecting" class="reconnecting-overlay">
            <div class="reconnecting-card">
                <div class="spinner"></div>
                <h2>Reconnectant...</h2>
                <p>S'està intentant reconnectar a la cua.</p>
            </div>
        </div>

        <!-- Estat: Error de connexio -->
        <div v-else-if="queueStore.error && !queueStore.isConnected" class="error-overlay">
            <div class="error-card">
                <div class="error-icon">❌</div>
                <h2>Error de Connexió</h2>
                <p>{{ queueStore.error }}</p>
                <button @click="reconnectar" class="btn-retry">
                    Tornar a intentar
                </button>
            </div>
        </div>

        <!-- Estat: Cua Normal -->
        <div v-else class="queue-container">
            <!-- Header -->
            <div class="header">
                <h1 class="title">La Teva Cua Virtual</h1>
                <div v-if="queueStore.eventName" class="event-info">
                    <span class="event-name">{{ queueStore.eventName }}</span>
                    <span v-if="queueStore.eventTime" class="event-time">{{ queueStore.eventTime }}</span>
                </div>
            </div>

            <!-- Flip Card Animation -->
            <div class="flip-container">
                <div class="flip-card" :class="{ 'is-first': queueStore.isFirst, 'is-waiting': !queueStore.isFirst }">
                    <div class="flip-card-inner">
                        <!-- Davant: Posicio -->
                        <div class="flip-card-front">
                            <div class="position-display">
                                <span class="position-label">La teva posició</span>
                                <div class="position-number">
                                    <span class="digit">{{ queueStore.position || queueStore.waitlistPosition || '-' }}</span>
                                </div>
                                <span class="position-suffix">
                                    {{ queueStore.isInWaitlist ? 'a la llista d\'espera' : (queueStore.position === 1 ? '' : 'a la cua') }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Darrere: Et toca! -->
                        <div class="flip-card-back">
                            <div class="turn-granted">
                                <div class="turn-icon">🎫</div>
                                <h2>És el teu torn!</h2>
                                <p>Pots accedir al mapa de seients</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queue Info -->
            <div class="queue-info">
                <div class="info-item">
                    <span class="info-label">Persones a la cua</span>
                    <span class="info-value">{{ queueStore.queueSize }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Llindar màxim</span>
                    <span class="info-value">{{ queueStore.thresholdN }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Temps estimat</span>
                    <span class="info-value">~{{ queueStore.tempsEsperaEstimats }} min</span>
                </div>
            </div>

            <!-- Accions -->
            <div class="actions">
                <!-- Boto: Et toca! -->
                <button 
                    v-if="queueStore.isFirst" 
                    @click="accedirMapa" 
                    class="btn-primary btn-enter"
                >
                    <span class="btn-icon">🎫</span>
                    Entrar a l'Event
                </button>

                <!-- Boto: Demanar torn (si no l'has rebut) -->
                <button 
                    v-else-if="queueStore.turnToken && !queueStore.isFirst"
                    @click="demanarTurn" 
                    class="btn-secondary"
                >
                    Verificar Torn
                </button>

                <!-- Boto: Sortir de la cua -->
                <button 
                    v-if="!queueStore.isFirst" 
                    @click="confirmarSortida" 
                    class="btn-outline"
                >
                    Sortir de la Cua
                </button>
            </div>

            <!-- Estat de connexio -->
            <div class="connection-status">
                <span class="status-dot" :class="{ 'connected': queueStore.isConnected }"></span>
                <span class="status-text">
                    {{ queueStore.isConnected ? 'Connectat' : 'Desconnectat' }}
                </span>
            </div>
        </div>

        <!-- Modal de confirmacio de sortida -->
        <div v-if="showConfirmModal" class="modal-overlay" @click.self="showConfirmModal = false">
            <div class="modal">
                <h3>Sortir de la Cua?</h3>
                <p>Si surts de la cua, perdràs la teva posició i hauràs d'esperar de nou.</p>
                <div class="modal-actions">
                    <button @click="showConfirmModal = false" class="btn-cancel">Cancel·lar</button>
                    <button @click="sortirDeCua" class="btn-confirm">Sortir</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
//================================ SCRIPT SETUP ==================

const route = useRoute();
const router = useRouter();
const { useQueueStore } = await import('@/stores/queue');
const { useAuthStore } = await import('@/stores/auth');

const queueStore = useQueueStore();
const authStore = useAuthStore();

const eventId = route.params.eventId;
const showConfirmModal = ref(false);

//================================ LIFECYCLE ======================

onMounted(() => {
    // A. Comprovar que l'usuari està autenticat
    if (!authStore.token) {
        router.push('/auth/login?redirect=' + encodeURIComponent(route.fullPath));
        return;
    }

    // B. Obtenir dades de l'event (si és passat com a query)
    const nomEvent = route.query.nom || '';
    const horaEvent = route.query.hora || '';

    // C. Inicialitzar la cua
    queueStore.inicialitzarCua(eventId, authStore.token, nomEvent, horaEvent);
});

onUnmounted(() => {
    // Desconnectar en sortir de la pàgina
    queueStore.desconnectar();
});

//================================ METODES ========================

/**
 * Connectar de nou
 */
function reconnectar() {
    queueStore.reset();
    queueStore.inicialitzarCua(eventId, authStore.token, '', '');
}

/**
 * Accedir al mapa de seients
 */
function accedirMapa() {
    if (queueStore.turnToken) {
        // Guardar el turn token i redirigir al mapa
        localStorage.setItem('turn_token', queueStore.turnToken);
        router.push(`/seients/${eventId}`);
    }
}

/**
 * Demanar torn (verificar si és el teu torn)
 */
function demanarTurn() {
    queueStore.demanarTurn();
}

/**
 * Mostrar modal de confirmacio
 */
function confirmarSortida() {
    showConfirmModal.value = true;
}

/**
 * Sortir de la cua
 */
function sortirDeCua() {
    queueStore.sortirCua();
    showConfirmModal.value = false;
    // Redirigir a la pàgina principal
    setTimeout(() => {
        router.push('/');
    }, 1000);
}

//================================ ESTILS ========================

// Els estils DICE s'apliquen des de CSS global o Tailwind
</script>

<style scoped>
/*================================ ESTILS CSS ====================*/

/* Variables DICE */
:root {
    --dice-black: #0a0a0a;
    --dice-pink: #FF0055;
    --dice-blue: #00F0FF;
    --dice-white: #ffffff;
    --dice-gray: #2a2a2a;
}

/* Container principal */
.waiting-room {
    min-height: 100vh;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    font-family: 'Segoe UI', sans-serif;
    color: #ffffff;
}

/* Panic Mode */
.panic-overlay, .error-overlay, .reconnecting-overlay {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.panic-card, .error-card, .reconnecting-card {
    background: #1a1a2e;
    border: 2px solid #FF0055;
    border-radius: 1rem;
    padding: 3rem;
    text-align: center;
    box-shadow: 0 0 30px rgba(255, 0, 85, 0.3);
}

.panic-icon, .error-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.reconnecting-card .spinner {
    width: 50px;
    height: 50px;
    border: 3px solid #2a2a2a;
    border-top-color: #00F0FF;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 2rem;
}

.title {
    font-size: 2rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.event-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.event-name {
    font-size: 1.25rem;
    color: #00F0FF;
}

.event-time {
    font-size: 0.875rem;
    color: #888;
}

/* Flip Card */
.flip-container {
    perspective: 1000px;
    margin-bottom: 2rem;
}

.flip-card {
    width: 280px;
    height: 280px;
    position: relative;
}

.flip-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.6s;
    transform-style: preserve-3d;
}

.flip-card.is-first .flip-card-inner {
    transform: rotateY(180deg);
}

.flip-card-front, .flip-card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.flip-card-front {
    background: linear-gradient(145deg, #1a1a2e, #2a2a3e);
    border: 2px solid #2a2a2a;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.flip-card-back {
    background: linear-gradient(145deg, #FF0055, #cc0044);
    transform: rotateY(180deg);
    box-shadow: 0 10px 30px rgba(255, 0, 85, 0.4);
}

/* Position Display */
.position-display {
    text-align: center;
}

.position-label {
    font-size: 0.875rem;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.position-number {
    margin: 1rem 0;
}

.position-number .digit {
    font-size: 6rem;
    font-weight: 700;
    color: #00F0FF;
    text-shadow: 0 0 20px rgba(0, 240, 255, 0.5);
    display: block;
}

.position-suffix {
    font-size: 1rem;
    color: #666;
}

/* Turn Granted */
.turn-granted {
    text-align: center;
}

.turn-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.turn-granted h2 {
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
}

.turn-granted p {
    font-size: 1rem;
    opacity: 0.9;
}

/* Queue Info */
.queue-info {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-item {
    text-align: center;
}

.info-label {
    display: block;
    font-size: 0.75rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff;
}

/* Actions */
.actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
    margin-bottom: 2rem;
}

.btn-primary, .btn-secondary, .btn-outline {
    padding: 1rem 2rem;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #FF0055;
    border: none;
    color: #ffffff;
    box-shadow: 0 0 20px rgba(255, 0, 85, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 30px rgba(255, 0, 85, 0.6);
}

.btn-secondary {
    background: #00F0FF;
    border: none;
    color: #0a0a0a;
}

.btn-outline {
    background: transparent;
    border: 2px solid #2a2a2a;
    color: #888;
}

.btn-outline:hover {
    border-color: #FF0055;
    color: #FF0055;
}

.btn-icon {
    font-size: 1.25rem;
}

/* Connection Status */
.connection-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #666;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #666;
}

.status-dot.connected {
    background: #00FF00;
    box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.modal {
    background: #1a1a2e;
    border-radius: 1rem;
    padding: 2rem;
    max-width: 400px;
    text-align: center;
}

.modal h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #ffffff;
}

.modal p {
    color: #888;
    margin-bottom: 2rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn-cancel {
    padding: 0.75rem 1.5rem;
    background: transparent;
    border: 2px solid #2a2a2a;
    color: #888;
    border-radius: 0.5rem;
    cursor: pointer;
}

.btn-confirm {
    padding: 0.75rem 1.5rem;
    background: #FF0055;
    border: none;
    color: #ffffff;
    border-radius: 0.5rem;
    cursor: pointer;
}

/* Retry button */
.btn-retry {
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    background: #00F0FF;
    border: none;
    color: #0a0a0a;
    border-radius: 0.5rem;
    cursor: pointer;
    font-weight: 600;
}

.btn-retry:hover {
    opacity: 0.9;
}
</style>