import { defineStore } from 'pinia';
import { useAuthStore } from './auth';

/**
 * Entrades comprades (comandes Ticketmaster demo) i accions de compra.
 */
export const useTicketsStore = defineStore('tickets', () => {
    const carregant = ref(false);
    const error = ref(null);
    /** @type {import('vue').Ref<Array<Record<string, unknown>>>} */
    const esdeveniments = ref([]);

    async function fetchMevesEntrades() {
        const authStore = useAuthStore();
        if (!authStore.estat.token) {
            esdeveniments.value = [];
            return;
        }
        carregant.value = true;
        error.value = null;
        try {
            const res = await $fetch('/api/meves-entrades', {
                headers: {
                    Authorization: `Bearer ${authStore.estat.token}`,
                },
            });
            esdeveniments.value = res.esdeveniments || [];
        } catch (err) {
            console.error(err);
            let msg = 'No s’han pogut carregar les entrades.';
            if (err && err.data && err.data.missatge) {
                msg = err.data.missatge;
            }
            error.value = msg;
            esdeveniments.value = [];
        } finally {
            carregant.value = false;
        }
    }

    /**
     * @param {string} tmEventId
     * @param {number} quantitat
     */
    async function comprar(tmEventId, quantitat) {
        const authStore = useAuthStore();
        if (!authStore.estat.token) {
            throw new Error('Cal iniciar sessió.');
        }
        return await $fetch('/api/comandes', {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${authStore.estat.token}`,
            },
            body: {
                tm_event_id: tmEventId,
                quantitat,
            },
        });
    }

    return {
        carregant,
        error,
        esdeveniments,
        fetchMevesEntrades,
        comprar,
    };
});
