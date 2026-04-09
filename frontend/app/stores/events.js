import { defineStore } from 'pinia';
import { useAuthStore } from './auth';

/**
 * Store de Pinia per gestionar els esdeveniments, el filtrat per proximitat
 * i el sistema de favorits ("M'interessa").
 */
export const useEventsStore = defineStore('events', () => {
    const authStore = useAuthStore();

    // ================================ ESTAT (REFS) ============
    const events = ref([]);
    const favorites = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const userLocation = ref({ lat: null, lon: null });
    const radius = ref(50); // Radi per defecte en km

    // ================================ ACCIONS ============

    /**
     * Obté el llistat d'esdeveniments des del backend (Laravel).
     * A. Inclou paràmetres de geolocalització si estan disponibles.
     * B. Gestiona la resposta i els errors.
     */
    async function fetchEvents() {
        loading.value = true;
        error.value = null;

        try {
            const query = {
                radius: radius.value,
            };

            if (userLocation.value.lat && userLocation.value.lon) {
                query.lat = userLocation.value.lat;
                query.lon = userLocation.value.lon;
            }

            // Crida a l'API de Laravel (EventController@index retorna { esdeveniments } o array)
            const data = await $fetch('/api/events', { query });
            if (Array.isArray(data)) {
                events.value = data;
            } else if (data.esdeveniments) {
                events.value = data.esdeveniments;
            } else if (data.events) {
                events.value = data.events;
            } else {
                events.value = [];
            }

            // Si l'usuari està autenticat, carreguem els seus favorits
            if (authStore.estat.estaAutenticat) {
                await fetchFavorites();
            }
        } catch (err) {
            console.error('Error carregant esdeveniments:', err);
            error.value = 'No s’han pogut carregar els esdeveniments.';
        } finally {
            loading.value = false;
        }
    }

    /**
     * Obté la llista de favorits de l'usuari des de PostgreSQL.
     */
    async function fetchFavorites() {
        if (!authStore.estat.token) return;

        try {
            const data = await $fetch('/api/favorites', {
                headers: {
                    Authorization: `Bearer ${authStore.estat.token}`,
                },
            });
            favorites.value = data;
        } catch (err) {
            console.error('Error carregant favorits:', err);
        }
    }

    /**
     * Afegir un event a favorits amb mutació optimista.
     */
    async function addFavorite(event) {
        if (!authStore.estat.estaAutenticat) {
            return navigateTo('/auth/login');
        }

        // 1. SNAPSHOT & MUTACIÓ OPTIMISTA
        const backup = [...favorites.value];
        let eventData = null;
        if (event.dates && event.dates.start && event.dates.start.localDate) {
            eventData = event.dates.start.localDate;
        }
        let eventImatge = null;
        if (event.images && event.images[0] && event.images[0].url) {
            eventImatge = event.images[0].url;
        }
        favorites.value.push({
            event_id: event.id,
            event_nom: event.name,
            event_data: eventData,
            event_imatge: eventImatge,
        });

        try {
            // 2. SINCRONITZACIÓ AMB BACKEND
            await $fetch('/api/favorites', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${authStore.estat.token}`,
                },
                body: {
                    event_id: event.id,
                    event_nom: event.name,
                    event_data: eventData,
                    event_imatge: eventImatge,
                },
            });
        } catch (err) {
            // 3. ROLLBACK SI HI HA ERROR
            favorites.value = backup;
            console.error('Error afegint favorit:', err);
        }
    }

    /**
     * Eliminar un event de favorits amb mutació optimista.
     */
    async function removeFavorite(eventId) {
        if (!authStore.estat.estaAutenticat) return;

        // 1. SNAPSHOT & MUTACIÓ OPTIMISTA
        const backup = [...favorites.value];
        favorites.value = favorites.value.filter(f => f.event_id !== eventId);

        try {
            // 2. SINCRONITZACIÓ AMB BACKEND
            await $fetch(`/api/favorites/${eventId}`, {
                method: 'DELETE',
                headers: {
                    Authorization: `Bearer ${authStore.estat.token}`,
                },
            });
        } catch (err) {
            // 3. ROLLBACK SI HI HA ERROR
            favorites.value = backup;
            console.error('Error eliminant favorit:', err);
        }
    }

    /**
     * Comprova si un event concret és a la llista de favorits.
     */
    function isFavorite(eventId) {
        return favorites.value.some(f => f.event_id === eventId);
    }

    /**
     * Actualitza el radi de cerca i torna a carregar els events.
     */
    function setRadius(newRadius) {
        radius.value = newRadius;
        fetchEvents();
    }

    /**
     * Estableix la ubicació de l'usuari i torna a carregar els events.
     */
    function setLocation(lat, lon) {
        userLocation.value = { lat, lon };
        fetchEvents();
    }

    return {
        events,
        favorites,
        loading,
        error,
        userLocation,
        radius,
        fetchEvents,
        addFavorite,
        removeFavorite,
        isFavorite,
        setRadius,
        setLocation,
    };
});
