<script setup>
import { useTicketsStore } from '~/stores/tickets';

const ticketsStore = useTicketsStore();

onMounted(() => {
    ticketsStore.fetchMevesEntrades();
});

const expanded = ref(null);

function toggle(tmId) {
    if (expanded.value === tmId) {
        expanded.value = null;
    } else {
        expanded.value = tmId;
    }
}
</script>

<template>
    <div class="min-h-screen bg-black text-white p-6 md:p-12">
        <div class="max-w-3xl mx-auto">
            <NuxtLink
                to="/"
                class="inline-flex items-center gap-2 text-[#00F0FF] font-black uppercase text-xs tracking-widest hover:text-white mb-10"
            >
                ← Tornar al cartell
            </NuxtLink>

            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">
                Les meves entrades
            </h1>
            <p class="text-zinc-500 text-sm font-bold uppercase tracking-widest mb-10">
                Comandes Ticketmaster (demo)
            </p>

            <div v-if="ticketsStore.carregant" class="flex flex-col items-center py-16">
                <div class="w-10 h-10 border-4 border-[#FF0055] border-t-transparent rounded-full animate-spin" />
            </div>

            <p v-else-if="ticketsStore.error" class="text-red-400 font-bold">
                {{ ticketsStore.error }}
            </p>

            <div v-else-if="!ticketsStore.esdeveniments.length" class="border border-dashed border-zinc-700 rounded-lg p-12 text-center text-zinc-500 font-bold uppercase text-sm">
                Encara no tens cap entrada comprada.
            </div>

            <ul v-else class="space-y-4">
                <li
                    v-for="grup in ticketsStore.esdeveniments"
                    :key="grup.tm_event_id"
                    class="border border-zinc-800 rounded-xl overflow-hidden bg-zinc-950/80"
                >
                    <button
                        type="button"
                        class="w-full text-left px-5 py-4 flex justify-between items-center gap-4 hover:bg-zinc-900/80 transition"
                        @click="toggle(grup.tm_event_id)"
                    >
                        <div>
                            <p class="font-black uppercase text-lg text-white">
                                {{ grup.detall_event_json && grup.detall_event_json.nom ? grup.detall_event_json.nom : grup.tm_event_id }}
                            </p>
                            <p class="text-zinc-500 text-xs mt-1">
                                {{ grup.detall_event_json && grup.detall_event_json.data ? grup.detall_event_json.data : '' }} · {{ grup.detall_event_json && grup.detall_event_json.recinte ? grup.detall_event_json.recinte : '' }}
                            </p>
                        </div>
                        <span class="text-[#FF0055] font-black shrink-0">
                            {{ grup.entrades_total }} entr.
                        </span>
                    </button>

                    <div
                        v-show="expanded === grup.tm_event_id"
                        class="border-t border-zinc-800 px-5 py-4 space-y-6 bg-black/40"
                    >
                        <div
                            v-for="c in grup.comandes"
                            :key="c.id"
                            class="space-y-2"
                        >
                            <p class="text-xs font-black uppercase text-zinc-500">
                                Comanda #{{ c.id }} · {{ c.import_total }} € · {{ c.creat_el }}
                            </p>
                            <ul class="space-y-2 pl-0 list-none">
                                <li
                                    v-for="t in c.tiquets"
                                    :key="t.id"
                                    class="text-sm font-mono text-zinc-300 border border-zinc-800 rounded px-3 py-2 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2"
                                >
                                    <span>Tiquet #{{ t.id }}</span>
                                    <span class="text-zinc-500 text-xs break-all">{{ t.hash_qr }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
