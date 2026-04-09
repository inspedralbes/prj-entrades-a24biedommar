import { defineStore } from 'pinia';
import { reactive, ref, computed } from 'vue';

/**
 * Store d'autenticació d'usuaris.
 */
export const useAuthStore = defineStore('auth', () => {
    const estat = reactive({
        token: null,
        usuari: null,
        estaAutenticat: false,
        carregant: false,
        error: null,
    });

    /** Evita múltiples initAuth al client. */
    const sessioInicialitzada = ref(false);

    /** Token de torn de la cua virtual (sincronitzat amb queueStore quan escau). */
    const cuaTurnToken = ref(null);

    /** Posició a la cua (per mostrar o lògica post-login). */
    const cuaPosicio = ref(null);

    const token = computed(() => estat.token);

    /**
     * Actualitza turn token i posició des de la cua (Pinia integració S1.14).
     */
    function establirEstatCua(turnToken, posicio) {
        cuaTurnToken.value = turnToken;
        cuaPosicio.value = posicio;
        if (process.client && turnToken) {
            localStorage.setItem('turn_token', turnToken);
        }
    }

    /**
     * Neteja l'estat de cua al store.
     */
    function netejarEstatCua() {
        cuaTurnToken.value = null;
        cuaPosicio.value = null;
        if (process.client) {
            localStorage.removeItem('turn_token');
        }
    }

    /**
     * Comprova si hi ha un turn_token desat (per exemple després del login).
     */
    function teTurnTokenDesat() {
        if (cuaTurnToken.value) {
            return true;
        }
        if (process.client && localStorage.getItem('turn_token')) {
            return true;
        }
        return false;
    }

    /**
     * Inicia sessio amb correu electronic i contrasenya.
     */
    async function login(correu, contrasenya, returnTo = '/') {
        estat.carregant = true;
        estat.error = null;

        try {
            const resposta = await $fetch('/api/login', {
                method: 'POST',
                body: {
                    correu_electronic: correu,
                    contrasenya: contrasenya,
                    return_to: returnTo,
                },
            });

            estat.token = resposta.token;
            estat.usuari = resposta.usuari;
            estat.estaAutenticat = true;

            if (process.client) {
                localStorage.setItem('auth_token', resposta.token);
            }

            return resposta;
        } catch (err) {
            let msg = 'Error en el login';
            if (err && err.data && err.data.missatge) {
                msg = err.data.missatge;
            }
            estat.error = msg;
            throw err;
        } finally {
            estat.carregant = false;
        }
    }

    /**
     * Registra un nou usuari al sistema.
     */
    async function register(nom, correu, contrasenya, contrasenyaConfirmacio) {
        estat.carregant = true;
        estat.error = null;

        try {
            const resposta = await $fetch('/api/register', {
                method: 'POST',
                body: {
                    nom: nom,
                    correu_electronic: correu,
                    contrasenya: contrasenya,
                    contrasenya_confirmation: contrasenyaConfirmacio,
                },
            });

            return resposta;
        } catch (err) {
            let msg = 'Error en el registre';
            if (err && err.data && err.data.missatge) {
                msg = err.data.missatge;
            }
            estat.error = msg;
            throw err;
        } finally {
            estat.carregant = false;
        }
    }

    /**
     * Tanca la sessio de l'usuari actual.
     */
    async function logout() {
        if (!estat.token) {
            return;
        }

        try {
            await $fetch('/api/logout', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${estat.token}`,
                },
            });
        } catch (err) {
            console.error('Error en logout:', err);
        } finally {
            estat.token = null;
            estat.usuari = null;
            estat.estaAutenticat = false;
            netejarEstatCua();

            if (process.client) {
                localStorage.removeItem('auth_token');
            }
        }
    }

    /**
     * Obté les dades de l'usuari autenticat des del backend.
     */
    async function fetchUser() {
        if (!estat.token) {
            return;
        }

        try {
            const resposta = await $fetch('/api/usuari', {
                headers: {
                    Authorization: `Bearer ${estat.token}`,
                },
            });

            const u = resposta.data !== undefined ? resposta.data : resposta;
            estat.usuari = u;
        } catch (err) {
            await logout();
        }
    }

    /**
     * Inicialitza l'estat d'autenticacio des de localStorage.
     */
    async function initAuth() {
        if (!process.client) {
            return;
        }

        if (sessioInicialitzada.value) {
            return;
        }

        sessioInicialitzada.value = true;

        const tokenGuardat = localStorage.getItem('auth_token');
        const turnGuardat = localStorage.getItem('turn_token');

        if (turnGuardat) {
            cuaTurnToken.value = turnGuardat;
        }

        if (tokenGuardat) {
            estat.token = tokenGuardat;
            estat.estaAutenticat = true;
            await fetchUser();
        }
    }

    return {
        estat,
        token,
        cuaTurnToken,
        cuaPosicio,
        sessioInicialitzada,
        establirEstatCua,
        netejarEstatCua,
        teTurnTokenDesat,
        login,
        register,
        logout,
        fetchUser,
        initAuth,
    };
});
