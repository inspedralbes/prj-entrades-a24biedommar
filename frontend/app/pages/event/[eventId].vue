<script setup>
/**
 * Detall d'esdeveniment estil DICE + compra interna (demo sense passarel·la).
 */
const route = useRoute();
const eventId = computed(() => String(route.params.eventId || ''));

const pending = ref(true);
const errorMsg = ref(null);
/** @type {import('vue').Ref<Record<string, unknown> | null>} */
const esdeveniment = ref(null);

const modalCompraObert = ref(false);
const compraExitosa = ref(false);

async function carregar() {
    pending.value = true;
    errorMsg.value = null;
    esdeveniment.value = null;
    compraExitosa.value = false;

    const id = eventId.value;
    if (!id) {
        errorMsg.value = 'ID d\'esdeveniment no vàlid.';
        pending.value = false;
        return;
    }

    try {
        const res = await $fetch(`/api/events/${encodeURIComponent(id)}`);
        let ev = res;
        if (res.esdeveniment) {
            ev = res.esdeveniment;
        }
        esdeveniment.value = ev;
    } catch (err) {
        let status = null;
        if (err && err.statusCode != null) {
            status = err.statusCode;
        } else if (err && err.status != null) {
            status = err.status;
        } else if (err && err.response && err.response.status != null) {
            status = err.response.status;
        }
        if (status === 404) {
            errorMsg.value = 'Aquest esdeveniment no s\'ha trobat o ja no està disponible.';
        } else {
            errorMsg.value = 'No s\'ha pogut carregar l\'esdeveniment. Torna-ho a intentar.';
        }
    } finally {
        pending.value = false;
    }
}

watch(eventId, () => {
    carregar();
}, { immediate: true });

const imatgeUrl = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    if (ev.imatge_gran) {
        return String(ev.imatge_gran);
    }
    if (ev.imatge) {
        return String(ev.imatge);
    }
    return '';
});

const titol = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    if (ev.nom) {
        return String(ev.nom);
    }
    if (ev.name) {
        return String(ev.name);
    }
    return 'Esdeveniment';
});

const dataHora = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    let d = null;
    if (ev.data) {
        d = ev.data;
    } else if (ev.dates && ev.dates.start && ev.dates.start.localDate) {
        d = ev.dates.start.localDate;
    }
    let t = null;
    if (ev.hora) {
        t = ev.hora;
    } else if (ev.dates && ev.dates.start && ev.dates.start.localTime) {
        t = ev.dates.start.localTime;
    }
    if (!d) {
        return '';
    }
    let timePart = '';
    if (t) {
        timePart = ' · ' + String(t);
    }
    try {
        const date = new Date(String(d));
        return date.toLocaleDateString('ca-ES', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }) + timePart;
    } catch {
        return String(d) + timePart;
    }
});

const lloc = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    const u = ev.ubicacio;
    if (u && (u.recinte || u.ciutat)) {
        const parts = [];
        if (u.recinte) {
            parts.push(String(u.recinte));
        }
        if (u.ciutat) {
            parts.push(String(u.ciutat));
        }
        let out = '';
        for (let i = 0; i < parts.length; i++) {
            if (i > 0) {
                out += ' · ';
            }
            out += parts[i];
        }
        return out;
    }
    const v = ev._embedded && ev._embedded.venues ? ev._embedded.venues[0] : null;
    if (v) {
        const parts2 = [];
        if (v.name) {
            parts2.push(String(v.name));
        }
        if (v.city && v.city.name) {
            parts2.push(String(v.city.name));
        }
        let out2 = '';
        for (let j = 0; j < parts2.length; j++) {
            if (j > 0) {
                out2 += ' · ';
            }
            out2 += parts2[j];
        }
        return out2;
    }
    if (ev.venue && ev.venue.nom) {
        return String(ev.venue.nom);
    }
    return '';
});

const preuText = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    const p = ev.preu;
    if (p && (p.min != null || p.max != null)) {
        let cur = 'EUR';
        if (p.moneda) {
            cur = String(p.moneda);
        }
        if (p.min != null && p.max != null && p.min !== p.max) {
            return String(p.min) + ' – ' + String(p.max) + ' ' + cur;
        }
        let val = '';
        if (p.min != null) {
            val = String(p.min);
        } else if (p.max != null) {
            val = String(p.max);
        }
        return val + ' ' + cur;
    }
    const pcs = ev.preus_complets;
    if (!Array.isArray(pcs) || pcs.length === 0) {
        return '';
    }
    const parts = [];
    for (let i = 0; i < pcs.length; i++) {
        const x = pcs[i];
        let s = '';
        if (x.formatted) {
            s = String(x.formatted);
        } else {
            const a = [];
            if (x.min != null) {
                a.push(String(x.min));
            }
            if (x.max != null) {
                a.push(String(x.max));
            }
            let inner = '';
            for (let k = 0; k < a.length; k++) {
                if (k > 0) {
                    inner += '–';
                }
                inner += a[k];
            }
            s = inner;
        }
        if (s) {
            parts.push(s);
        }
    }
    let out = '';
    for (let j = 0; j < parts.length; j++) {
        if (j > 0) {
            out += ' · ';
        }
        out += parts[j];
    }
    return out;
});

/** Backend exposa pot_comprar / esgotat (TicketmasterService). */
const potComprar = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return false;
    }
    if (ev.esgotat === true) {
        return false;
    }
    return ev.pot_comprar === true;
});

const mapaSeientsUrl = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    const m = ev.mapa_seients;
    if (typeof m === 'string') {
        return m;
    }
    return '';
});

/** Categoria principal (TM segment o classificacions). */
const categoriaEtiqueta = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    if (ev.categoria) {
        return String(ev.categoria);
    }
    const cls = ev.classificacions;
    if (Array.isArray(cls) && cls.length > 0 && cls[0].segment) {
        return String(cls[0].segment);
    }
    return '';
});

/** Adreça completa des de venue (backend). */
const adrecaVenue = computed(() => {
    const ev = esdeveniment.value;
    if (!ev || !ev.venue) {
        return '';
    }
    const v = ev.venue;
    const linies = [];
    if (v.adreca) {
        linies.push(String(v.adreca));
    }
    let linia2 = '';
    if (v.codi_postal) {
        linia2 += String(v.codi_postal) + ' ';
    }
    if (v.ciutat) {
        linia2 += String(v.ciutat);
    }
    if (linia2.trim()) {
        linies.push(linia2.trim());
    }
    if (v.pais) {
        linies.push(String(v.pais));
    }
    let out = '';
    for (let i = 0; i < linies.length; i++) {
        if (i > 0) {
            out += '\n';
        }
        out += linies[i];
    }
    return out;
});

/** Artistes / atraccions amb nom (per a la secció Line-up). */
const artistesAmbNom = computed(() => {
    const ev = esdeveniment.value;
    if (!ev || !Array.isArray(ev.artistes)) {
        return [];
    }
    const out = [];
    for (let i = 0; i < ev.artistes.length; i++) {
        const a = ev.artistes[i];
        if (a && a.nom) {
            out.push(a);
        }
    }
    return out;
});

/** Organitzadors / promotors (API TM). */
const organitzadorsLlista = computed(() => {
    const ev = esdeveniment.value;
    if (!ev || !Array.isArray(ev.organitzadors)) {
        return [];
    }
    const out = [];
    for (let j = 0; j < ev.organitzadors.length; j++) {
        const o = ev.organitzadors[j];
        if (o && o.nom) {
            out.push(String(o.nom));
        }
    }
    return out;
});

/**
 * Text curt per al footer i el modal: preu per entrada (orientatiu).
 */
const preuPerEntradaFooter = computed(() => {
    const ev = esdeveniment.value;
    if (!ev) {
        return '';
    }
    const p = ev.preu;
    let mon = 'EUR';
    if (p && p.moneda) {
        mon = String(p.moneda);
    }
    if (p && p.min != null && p.max != null && p.min === p.max) {
        return String(p.min) + ' ' + mon + ' per entrada';
    }
    if (p && p.min != null) {
        return 'Des de ' + String(p.min) + ' ' + mon + ' per entrada';
    }
    const pcs = ev.preus_complets;
    if (Array.isArray(pcs) && pcs.length > 0) {
        const primer = pcs[0];
        if (primer && primer.min != null) {
            let m = 'EUR';
            if (primer.moneda) {
                m = String(primer.moneda);
            }
            return 'Des de ' + String(primer.min) + ' ' + m + ' per entrada';
        }
    }
    return '';
});

function obrirModal() {
    modalCompraObert.value = true;
}

function tancarModal() {
    modalCompraObert.value = false;
}

function onCompraExitosa() {
    compraExitosa.value = true;
}
</script>

<template>
    <div class="min-h-screen bg-black text-white pb-32 md:pb-28">
        <div class="p-6 md:p-10 max-w-6xl mx-auto">
            <NuxtLink
                to="/"
                class="inline-flex items-center gap-2 text-[#00F0FF] font-black uppercase text-xs tracking-widest hover:text-white transition-colors mb-8"
            >
                ← Tornar al cartell
            </NuxtLink>

            <div v-if="pending" class="flex flex-col items-center justify-center py-24">
                <div class="w-12 h-12 border-4 border-[#FF0055] border-t-transparent rounded-full animate-spin" />
                <p class="mt-4 font-black uppercase text-xs tracking-widest text-zinc-500">
                    Carregant esdeveniment…
                </p>
            </div>

            <div
                v-else-if="errorMsg"
                class="border border-dashed border-zinc-700 rounded-lg p-12 text-center"
            >
                <p class="text-zinc-400 font-bold mb-6">{{ errorMsg }}</p>
                <NuxtLink
                    to="/"
                    class="inline-block bg-[#FF0055] text-black px-8 py-3 font-black uppercase text-sm"
                >
                    Tornar a l'inici
                </NuxtLink>
            </div>

            <template v-else-if="esdeveniment">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                    <!-- Columna visual -->
                    <div class="lg:col-span-5 space-y-6">
                        <div class="aspect-square max-w-md mx-auto lg:mx-0 overflow-hidden bg-zinc-900 rounded-xl border border-zinc-800 shadow-[0_0_40px_rgba(0,240,255,0.08)]">
                            <img
                                v-if="imatgeUrl"
                                :src="imatgeUrl"
                                :alt="titol"
                                class="h-full w-full object-cover"
                            >
                            <div
                                v-else
                                class="h-full min-h-[240px] flex items-center justify-center text-zinc-600 font-black uppercase text-sm"
                            >
                                Sense imatge
                            </div>
                        </div>
                        <div v-if="mapaSeientsUrl" class="rounded-xl border border-zinc-800 overflow-hidden bg-zinc-950">
                            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 px-4 pt-3">
                                Mapa de seients (informació Ticketmaster)
                            </p>
                            <a
                                :href="mapaSeientsUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="block p-2"
                            >
                                <img
                                    :src="mapaSeientsUrl"
                                    alt="Seatmap"
                                    class="w-full h-auto opacity-90 hover:opacity-100 transition"
                                >
                            </a>
                        </div>
                    </div>

                    <!-- Columna text (DICE-like: tot dins l'app) -->
                    <div class="lg:col-span-7 space-y-5">
                        <header class="space-y-3">
                            <p class="text-[#00F0FF] font-black text-xs uppercase tracking-widest">
                                {{ dataHora }}
                            </p>
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black uppercase tracking-tighter leading-tight">
                                {{ titol }}
                            </h1>
                            <div class="flex flex-wrap gap-2 items-center">
                                <span
                                    v-if="categoriaEtiqueta"
                                    class="inline-block px-3 py-1 rounded-full border border-zinc-700 text-[10px] font-black uppercase tracking-widest text-zinc-300"
                                >
                                    {{ categoriaEtiqueta }}
                                </span>
                                <span
                                    v-if="preuText"
                                    class="inline-block px-3 py-1 rounded-full bg-zinc-900 border border-[#FF0055]/40 text-xs font-black text-white"
                                >
                                    {{ preuText }}
                                </span>
                            </div>
                        </header>

                        <!-- Ubicació -->
                        <section class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-5 md:p-6 space-y-3">
                            <h2 class="text-white font-black uppercase text-xs tracking-[0.2em]">
                                Ubicació
                            </h2>
                            <p
                                v-if="lloc"
                                class="text-zinc-100 font-bold uppercase text-sm leading-relaxed"
                            >
                                {{ lloc }}
                            </p>
                            <p
                                v-if="adrecaVenue"
                                class="text-zinc-400 text-sm whitespace-pre-line leading-relaxed"
                            >
                                {{ adrecaVenue }}
                            </p>
                            <p
                                v-if="!lloc && !adrecaVenue"
                                class="text-zinc-500 text-sm"
                            >
                                Sense informació d’ubicació des de Ticketmaster per a aquest esdeveniment.
                            </p>
                        </section>

                        <!-- Line-up / artistes -->
                        <section class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-5 md:p-6 space-y-4">
                            <h2 class="text-white font-black uppercase text-xs tracking-[0.2em]">
                                Line-up
                            </h2>
                            <ul v-if="artistesAmbNom.length" class="space-y-3">
                                <li
                                    v-for="(art, idx) in artistesAmbNom"
                                    :key="idx"
                                    class="flex gap-4 items-center"
                                >
                                    <div
                                        v-if="art.imatge"
                                        class="w-14 h-14 shrink-0 rounded-lg overflow-hidden bg-zinc-800 border border-zinc-700"
                                    >
                                        <img :src="art.imatge" :alt="art.nom" class="w-full h-full object-cover">
                                    </div>
                                    <div
                                        v-else
                                        class="w-14 h-14 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center text-zinc-600 text-[10px] font-black uppercase"
                                    >
                                        TM
                                    </div>
                                    <span class="font-black uppercase text-white text-sm tracking-tight">{{ art.nom }}</span>
                                </li>
                            </ul>
                            <p v-else class="text-zinc-500 text-sm">
                                Sense artistes o atraccions llistades per a aquest esdeveniment.
                            </p>
                        </section>

                        <!-- Organització -->
                        <section class="rounded-2xl border border-zinc-800 bg-zinc-950/60 p-5 md:p-6 space-y-3">
                            <h2 class="text-white font-black uppercase text-xs tracking-[0.2em]">
                                Organització
                            </h2>
                            <ul v-if="organitzadorsLlista.length" class="space-y-2">
                                <li
                                    v-for="(nomOrg, io) in organitzadorsLlista"
                                    :key="io"
                                    class="text-zinc-300 font-bold text-sm"
                                >
                                    {{ nomOrg }}
                                </li>
                            </ul>
                            <p v-else class="text-zinc-500 text-sm">
                                Sense promotor o organitzador llistat a Ticketmaster.
                            </p>
                        </section>

                        <!-- Descripció i notes -->
                        <section class="rounded-2xl border border-zinc-800 bg-zinc-950/40 p-5 md:p-6 space-y-4">
                            <h2 class="text-white font-black uppercase text-xs tracking-[0.2em]">
                                Sobre l’esdeveniment
                            </h2>
                            <p
                                v-if="esdeveniment.descripcio"
                                class="text-zinc-300 text-sm leading-relaxed whitespace-pre-line"
                            >
                                {{ esdeveniment.descripcio }}
                            </p>
                            <p
                                v-if="esdeveniment.informacio"
                                class="text-zinc-500 text-sm leading-relaxed whitespace-pre-line"
                            >
                                {{ esdeveniment.informacio }}
                            </p>
                            <p
                                v-if="!esdeveniment.descripcio && !esdeveniment.informacio"
                                class="text-zinc-500 text-sm"
                            >
                                Ticketmaster no proporciona text descriptiu per a aquest esdeveniment.
                            </p>
                        </section>

                        <div
                            v-if="compraExitosa"
                            class="rounded-lg border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-emerald-200 text-sm font-bold"
                        >
                            Compra registrada. Pots veure les entrades a «Les meves entrades».
                        </div>
                    </div>
                </div>

                <!-- Barra fixa compra (DICE-like) -->
                <div
                    class="fixed bottom-0 left-0 right-0 z-50 border-t border-zinc-800 bg-zinc-950/95 backdrop-blur-md px-4 py-4 md:px-8"
                >
                    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p v-if="preuPerEntradaFooter" class="text-white font-black text-xl md:text-2xl tracking-tight">
                                {{ preuPerEntradaFooter }}
                            </p>
                            <p
                                v-else-if="preuText"
                                class="text-white font-black text-lg"
                            >
                                {{ preuText }}
                            </p>
                            <p
                                v-else
                                class="text-zinc-400 font-bold text-sm uppercase tracking-wide"
                            >
                                Preu no publicat a Ticketmaster
                            </p>
                            <p v-if="preuText && preuPerEntradaFooter" class="text-zinc-500 text-xs mt-1 max-w-xl">
                                Rang orientatiu: {{ preuText }}
                            </p>
                            <p class="text-zinc-500 text-xs mt-1 max-w-xl">
                                Preu orientatiu per a la demo (sense passarel·la real). La compra es fa dins aquesta app.
                            </p>
                            <p
                                v-if="!potComprar"
                                class="text-amber-400 text-sm font-black uppercase mt-2"
                            >
                                Les entrades s’han esgotat o no estan disponibles per a la venda.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                            <NuxtLink
                                to="/"
                                class="inline-flex justify-center items-center border border-zinc-600 text-zinc-300 px-6 py-3 font-black uppercase text-xs hover:border-white hover:text-white transition"
                            >
                                Altres esdeveniments
                            </NuxtLink>
                            <button
                                type="button"
                                class="inline-flex justify-center items-center bg-[#FF0055] text-black px-10 py-3 font-black uppercase text-sm transition hover:scale-[1.02] disabled:opacity-40 disabled:pointer-events-none shadow-[0_0_24px_rgba(255,0,85,0.35)]"
                                :disabled="!potComprar"
                                @click="obrirModal"
                            >
                                Comprar
                            </button>
                        </div>
                    </div>
                </div>

                <EventPurchaseModal
                    :obert="modalCompraObert"
                    :event-id="eventId"
                    :esdeveniment="esdeveniment"
                    :pot-comprar="potComprar"
                    :preu-text="preuText"
                    :preu-per-entrada="preuPerEntradaFooter"
                    @tancar="tancarModal"
                    @compra-exitosa="onCompraExitosa"
                />
            </template>
        </div>
    </div>
</template>
