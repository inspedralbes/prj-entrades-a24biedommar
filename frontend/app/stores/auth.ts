import { defineStore } from 'pinia';

interface User {
    id: number;
    nom: string;
    correu_electronic: string;
    rol: string;
}

interface AuthState {
    token: string | null;
    user: User | null;
    isAuthenticated: boolean;
    loading: boolean;
    error: string | null;
}

export const useAuthStore = defineStore('auth', () => {
    // A. Estat inicial
    const state = reactive<AuthState>({
        token: null,
        user: null,
        isAuthenticated: false,
        loading: false,
        error: null,
    });

    // B. Actions
    async function login(correu: string, contrasenya: string, returnTo: string = '/') {
        state.loading = true;
        state.error = null;

        try {
            const response = await $fetch('/api/login', {
                method: 'POST',
                body: {
                    correu_electronic: correu,
                    contrasenya: contrasenya,
                    return_to: returnTo,
                },
            });

            state.token = response.token;
            state.user = response.usuari;
            state.isAuthenticated = true;

            if (process.client) {
                localStorage.setItem('auth_token', response.token);
            }

            return response;
        } catch (err: any) {
            state.error = err.data?.missatge || 'Error en el login';
            throw err;
        } finally {
            state.loading = false;
        }
    }

    async function register(nom: string, correu: string, contrasenya: string) {
        state.loading = true;
        state.error = null;

        try {
            const response = await $fetch('/api/register', {
                method: 'POST',
                body: {
                    nom: nom,
                    correu_electronic: correu,
                    contrasenya: contrasenya,
                },
            });

            return response;
        } catch (err: any) {
            state.error = err.data?.missatge || 'Error en el registre';
            throw err;
        } finally {
            state.loading = false;
        }
    }

    async function logout() {
        if (!state.token) return;

        try {
            await $fetch('/api/logout', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${state.token}`,
                },
            });
        } catch (err) {
            console.error('Logout error:', err);
        } finally {
            state.token = null;
            state.user = null;
            state.isAuthenticated = false;

            if (process.client) {
                localStorage.removeItem('auth_token');
            }
        }
    }

    async function fetchUser() {
        if (!state.token) return;

        try {
            const response = await $fetch('/api/usuari', {
                headers: {
                    Authorization: `Bearer ${state.token}`,
                },
            });

            state.user = response;
        } catch (err) {
            await logout();
        }
    }

    async function initAuth() {
        if (process.client) {
            const savedToken = localStorage.getItem('auth_token');
            if (savedToken) {
                state.token = savedToken;
                state.isAuthenticated = true;
                await fetchUser();
            }
        }
    }

    return {
        state,
        login,
        register,
        logout,
        fetchUser,
        initAuth,
    };
});