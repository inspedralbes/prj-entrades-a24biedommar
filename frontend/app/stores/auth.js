import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', () => {
    // A. Estat inicial de la Store
    const estat = {
        token: null,
        usuari: null,
        estaAutenticat: false,
        carregant: false,
        error: null,
    };

    /**
     * Inicia sessio amb correu electronic i contrasenya.
     * A. Valida les credencials amb el backend Laravel.
     * B. desa el token a localStorage.
     * C. Actualitza l'estat de la Store.
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
            estat.error = err.data?.missatge || 'Error en el login';
            throw err;
        } finally {
            estat.carregant = false;
        }
    }

    /**
     * Registra un nou usuari al sistema.
     * A. Envia les dades al endpoint de registre.
     * B. Retorna la resposta del servidor.
     */
    async function register(nom, correu, contrasenya) {
        estat.carregant = true;
        estat.error = null;

        try {
            const resposta = await $fetch('/api/register', {
                method: 'POST',
                body: {
                    nom: nom,
                    correu_electronic: correu,
                    contrasenya: contrasenya,
                },
            });

            return resposta;
        } catch (err) {
            estat.error = err.data?.missatge || 'Error en el registre';
            throw err;
        } finally {
            estat.carregant = false;
        }
    }

    /**
     * Tanca la sessio de l'usuari actual.
     * A. Revoca el token al backend.
     * B. Neteja l'estat local.
     */
    async function logout() {
        if (!estat.token) return;

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

            if (process.client) {
                localStorage.removeItem('auth_token');
            }
        }
    }

    /**
     * Obté les dades de l'usuari autenticat des del backend.
     * A. Crida l'endpoint /api/usuari.
     * B. Actualitza les dades de l'usuari a l'estat.
     */
    async function fetchUser() {
        if (!estat.token) return;

        try {
            const resposta = await $fetch('/api/usuari', {
                headers: {
                    Authorization: `Bearer ${estat.token}`,
                },
            });

            estat.usuari = resposta;
        } catch (err) {
            await logout();
        }
    }

    /**
     * Inicialitza l'estat d'autenticacio des de localStorage.
     * A. Recupera el token guardat.
     * B. Valida el token carregant les dades de l'usuari.
     */
    async function initAuth() {
        if (process.client) {
            const tokenGuardat = localStorage.getItem('auth_token');
            if (tokenGuardat) {
                estat.token = tokenGuardat;
                estat.estaAutenticat = true;
                await fetchUser();
            }
        }
    }

    return {
        estat,
        login,
        register,
        logout,
        fetchUser,
        initAuth,
    };
});