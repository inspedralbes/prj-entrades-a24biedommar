<script setup>
import { useEventsStore } from '~/stores/events';
import { useAuthStore } from '~/stores/auth';

const props = defineProps({
    event: {
        type: Object,
        required: true
    }
});

const eventsStore = useEventsStore();
const authStore = useAuthStore();
const isLoading = ref(false);

function toggleFavorite() {
    if (eventsStore.isFavorite(props.event.id)) {
        eventsStore.removeFavorite(props.event.id);
    } else {
        eventsStore.addFavorite(props.event);
    }
}

async function goToEvent() {
    const eventId = props.event.id;
    return navigateTo(`/event/${eventId}`);
}

const formattedDate = computed(() => {
    let dateStr = '';
    if (props.event.dates && props.event.dates.start && props.event.dates.start.localDate) {
        dateStr = props.event.dates.start.localDate;
    }
    if (!dateStr) {
        return '';
    }
    const date = new Date(dateStr);
    return date.toLocaleDateString('ca-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).toUpperCase();
});

const imageUrl = computed(() => {
    const imgs = props.event.images;
    if (imgs && imgs[0] && imgs[0].url) {
        return imgs[0].url;
    }
    return 'https://via.placeholder.com/600x400?text=No+Image';
});

const liniaVenue = computed(() => {
    const emb = props.event._embedded;
    if (!emb || !emb.venues || !emb.venues[0]) {
        return '';
    }
    const v = emb.venues[0];
    const parts = [];
    if (v.name) {
        parts.push(v.name);
    }
    if (v.city && v.city.name) {
        parts.push(v.city.name);
    }
    let out = '';
    for (let i = 0; i < parts.length; i++) {
        if (i > 0) {
            out += ' • ';
        }
        out += parts[i];
    }
    return out;
});
</script>

<template>
    <div class="group relative overflow-hidden bg-black border-b border-zinc-900 pb-10 transition-all hover:border-[#FF0055]">
        <div class="aspect-[16/9] overflow-hidden bg-zinc-900 mb-6">
            <img 
                :src="imageUrl" 
                :alt="event.name"
                class="h-full w-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500 scale-100 group-hover:scale-105"
            />
        </div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
            <div class="flex-1">
                <span class="text-[#00F0FF] font-black text-sm uppercase tracking-widest mb-2 block">
                    {{ formattedDate }}
                </span>
                <h2 class="text-3xl md:text-4xl font-black uppercase leading-none tracking-tighter text-white">
                    {{ event.name }}
                </h2>
                <p class="text-zinc-500 font-bold mt-2 uppercase text-sm">
                    {{ liniaVenue }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                <button 
                    @click="toggleFavorite"
                    class="px-6 py-3 font-black uppercase text-sm transition-all border-2"
                    :class="eventsStore.isFavorite(event.id) 
                        ? 'bg-white text-black border-white' 
                        : 'bg-transparent text-white border-white hover:bg-white hover:text-black'"
                >
                    {{ eventsStore.isFavorite(event.id) ? "M'interessa ✓" : "M'interessa" }}
                </button>

                <button 
                    @click="goToEvent"
                    class="bg-[#FF0055] text-black px-10 py-3 font-black uppercase text-sm transition-all hover:scale-105 active:scale-95 shadow-[0_0_20px_rgba(255,0,85,0.4)]"
                >
                    Comprar
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>