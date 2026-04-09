<script setup>
import { useAuthStore } from '~/stores/auth';
import { useTicketsStore } from '~/stores/tickets';

const props = defineProps({
    obert: { type: Boolean, default: false },
    eventId: { type: String, required: true },
    esdeveniment: { type: Object, default: null },
    potComprar: { type: Boolean, default: false },
    preuText: { type: String, default: '' },
    /** Ex.: "Des de 25 EUR per entrada" (mateix text que el footer). */
    preuPerEntrada: { type: String, default: '' },
});

const emit = defineEmits(['tancar', 'compraExitosa']);

const authStore = useAuthStore();
const ticketsStore = useTicketsStore();
const route = useRoute();

const quantitat = ref(1);
const comprant = ref(false);
const errorLocal = ref(null);

watch(
    () => props.obert,
    (v) => {
        if (v) {
            quantitat.value = 1;
            errorLocal.value = null;
        }
    }
);

async function confirmarCompra() {
    errorLocal.value = null;
    if (!props.potComprar) {
        errorLocal.value = 'Aquest esdeveniment no està disponible per a la venda.';
        return;
    }
    if (!authStore.estat.token) {
        return;
    }
    comprant.value = true;
    try {
        await ticketsStore.comprar(props.eventId, quantitat.value);
        emit('compraExitosa');
        emit('tancar');
    } catch (err) {
        let primer = '';
        const d = err && err.data ? err.data : null;
        if (d) {
            if (d.missatge) {
                primer = d.missatge;
            } else if (d.message) {
                primer = d.message;
            }
        }
        if (!primer && d && d.errors && typeof d.errors === 'object') {
            const claus = Object.keys(d.errors);
            for (let i = 0; i < claus.length; i++) {
                const arr = d.errors[claus[i]];
                if (Array.isArray(arr) && arr.length > 0) {
                    primer = arr[0];
                    break;
                }
            }
        }
        if (!primer && err && err.message) {
            primer = err.message;
        }
        if (!primer) {
            primer = 'Error en la compra.';
        }
        errorLocal.value = primer;
    } finally {
        comprant.value = false;
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="obert"
            class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            @click.self="emit('tancar')"
        >
            <div
                class="w-full max-w-lg bg-zinc-950 border border-zinc-800 rounded-t-2xl sm:rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="p-6 border-b border-zinc-800 flex justify-between items-start gap-4">
                    <h2 class="text-xl font-black uppercase tracking-tight text-white">
                        Comprar entrades
                    </h2>
                    <button
                        type="button"
                        class="text-zinc-500 hover:text-white text-2xl leading-none"
                        aria-label="Tancar"
                        @click="emit('tancar')"
                    >
                        ×
                    </button>
                </div>

                <div v-if="esdeveniment" class="p-6 space-y-4 text-zinc-300">
                    <p class="font-black uppercase text-[#00F0FF] text-xs tracking-widest">
                        {{ esdeveniment.nom }}
                    </p>
                    <p v-if="preuPerEntrada" class="text-white font-black text-lg">
                        {{ preuPerEntrada }}
                    </p>
                    <p
                        v-else-if="preuText"
                        class="text-white font-black text-lg"
                    >
                        {{ preuText }}
                    </p>
                    <p
                        v-else
                        class="text-zinc-500 font-bold text-sm"
                    >
                        Preu no publicat a Ticketmaster
                    </p>
                    <p v-if="preuText && preuPerEntrada" class="text-zinc-500 text-xs">
                        Rang orientatiu: {{ preuText }}
                    </p>
                    <p class="text-xs text-zinc-500 leading-relaxed">
                        Import orientatiu per entrada en aquesta demo (sense passarel·la real).
                    </p>

                    <div v-if="!potComprar" class="rounded-lg border border-amber-900/50 bg-amber-950/40 px-4 py-3 text-amber-200 text-sm font-bold">
                        Les entrades s’han esgotat o no estan disponibles per a la venda.
                    </div>

                    <template v-else>
                        <div v-if="!authStore.estat.estaAutenticat" class="rounded-lg border border-zinc-700 p-4 space-y-3">
                            <p class="text-sm text-zinc-400">
                                Has d’iniciar sessió per comprar.
                            </p>
                            <NuxtLink
                                :to="'/auth/login?return_to=' + encodeURIComponent(route.fullPath)"
                                class="block w-full text-center bg-[#FF0055] text-black py-3 font-black uppercase text-sm"
                            >
                                Iniciar sessió
                            </NuxtLink>
                        </div>

                        <div v-else class="space-y-4">
                            <label class="block">
                                <span class="text-xs font-black uppercase tracking-widest text-zinc-500">Quantitat (màx. 6)</span>
                                <select
                                    v-model.number="quantitat"
                                    class="mt-2 w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-3 text-white font-bold"
                                >
                                    <option :value="1">1 entrada</option>
                                    <option :value="2">2 entrades</option>
                                    <option :value="3">3 entrades</option>
                                    <option :value="4">4 entrades</option>
                                    <option :value="5">5 entrades</option>
                                    <option :value="6">6 entrades</option>
                                </select>
                            </label>

                            <p v-if="errorLocal" class="text-red-400 text-sm font-bold">
                                {{ errorLocal }}
                            </p>

                            <button
                                type="button"
                                class="w-full bg-[#FF0055] text-black py-4 font-black uppercase text-sm disabled:opacity-50"
                                :disabled="comprant"
                                @click="confirmarCompra"
                            >
                                <span v-if="comprant">Processant…</span>
                                <span v-else>Confirmar compra</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </Teleport>
</template>
