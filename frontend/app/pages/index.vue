<script setup>
import { useEventsStore } from '~/stores/events';
import { useAuthStore } from '~/stores/auth';

const eventsStore = useEventsStore();
const authStore = useAuthStore();

/**
 * Inicialització:
 * A. Demanar geolocalització a l'usuari.
 * B. Carregar esdeveniments inicials.
 */
onMounted(async () => {
    // Intentar obtenir geolocalització
    if (process.client && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                eventsStore.setLocation(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                console.warn('Geolocalització denegada o no disponible:', error);
                eventsStore.fetchEvents(); // Carregar sense location
            }
        );
    } else {
        eventsStore.fetchEvents();
    }
});

/**
 * Canvi manual del radi de cerca.
 */
function updateRadius(event) {
    eventsStore.setRadius(parseInt(event.target.value));
}
</script>

<template>
    <div class="min-h-screen bg-black text-white p-6 md:p-12">
        <!-- Header DICE Style -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 mb-20">
            <div>
                <h1 class="text-6xl md:text-8xl font-black uppercase tracking-tighter leading-none">
                    Cartellera
                </h1>
                <p class="text-[#00F0FF] font-bold mt-4 uppercase tracking-widest text-sm">
                    Explora els millors esdeveniments prop de tu
                </p>
            </div>

            <!-- Filtre de proximitat -->
            <div class="bg-zinc-900/50 p-6 border border-zinc-800 w-full md:w-80">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs font-black uppercase tracking-widest text-zinc-400">Radi de cerca</span>
                    <span class="text-[#FF0055] font-black">{{ eventsStore.radius }} KM</span>
                </div>
                <input 
                    type="range" 
                    min="1" 
                    max="500" 
                    :value="eventsStore.radius" 
                    @change="updateRadius"
                    class="w-full h-1 bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-[#FF0055]"
                />
                <div class="flex justify-between mt-2 text-[10px] font-bold text-zinc-600 uppercase">
                    <span>1 km</span>
                    <span>500 km</span>
                </div>
            </div>
        </header>

        <!-- Llistat d'esdeveniments -->
        <main>
            <!-- Estat de càrrega -->
            <div v-if="eventsStore.loading" class="flex flex-col items-center justify-center py-20">
                <div class="w-12 h-12 border-4 border-[#FF0055] border-t-transparent rounded-full animate-spin"></div>
                <p class="mt-4 font-black uppercase text-xs tracking-widest animate-pulse">Buscant esdeveniments...</p>
            </div>

            <!-- Error -->
            <div v-else-if="eventsStore.error" class="text-center py-20 border border-dashed border-zinc-800">
                <p class="text-zinc-500 font-black uppercase">{{ eventsStore.error }}</p>
                <button @click="eventsStore.fetchEvents" class="mt-4 text-[#FF0055] font-black uppercase text-sm underline">
                    Tornar-ho a intentar
                </button>
            </div>

            <!-- Grid d'esdeveniments -->
            <div v-else-if="eventsStore.events.length > 0" class="grid grid-cols-1 gap-12">
                <EventCard 
                    v-for="event in eventsStore.events" 
                    :key="event.id" 
                    :event="event" 
                />
            </div>

            <!-- No hi ha resultats -->
            <div v-else class="text-center py-20 border border-dashed border-zinc-800">
                <p class="text-zinc-500 font-black uppercase italic">No s'han trobat esdeveniments en aquest radi.</p>
                <p class="text-zinc-700 text-xs mt-2">Prova d'augmentar el radi de cerca o canviar la teva ubicació.</p>
            </div>
        </main>

        <!-- Footer / Nav temporal -->
        <footer class="mt-40 pt-10 border-t border-zinc-900 flex flex-col md:flex-row justify-between items-center gap-6 pb-10">
            <p class="text-zinc-700 font-bold text-[10px] uppercase">© 2026 TR3 TicketMaster • DICE Inspired UI</p>
            <div class="flex flex-wrap gap-6 md:gap-8 justify-center items-center">
                <NuxtLink
                    v-if="authStore.estat.estaAutenticat"
                    to="/tickets"
                    class="text-[#00F0FF] font-black uppercase text-xs hover:text-white"
                >
                    Les meves entrades
                </NuxtLink>
                <NuxtLink v-if="!authStore.estat.estaAutenticat" to="/auth/login" class="text-white font-black uppercase text-xs hover:text-[#FF0055]">Login</NuxtLink>
                <button v-else type="button" class="text-white font-black uppercase text-xs hover:text-[#FF0055]" @click="authStore.logout">Logout (<span v-if="authStore.estat.usuari && authStore.estat.usuari.nom">{{ authStore.estat.usuari.nom }}</span><span v-else>—</span>)</button>
            </div>
        </footer>
    </div>
</template>

<style>
/* Estils globals per a range inputs si calgués */
input[type='range']::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    background: #FF0055;
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(255, 0, 85, 0.5);
}
</style>
