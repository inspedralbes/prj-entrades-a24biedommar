import { useAuthStore } from '~/stores/auth';

/**
 * Rutes públiques (sense autenticació obligatòria).
 */
const rutesPubliquesPrefixes = ['/', '/auth/login', '/auth/register', '/events'];

/**
 * Prefixos de rutes protegides.
 */
const prefixosProtegits = ['/waiting-room', '/checkout', '/profile', '/tickets', '/seients'];

/**
 * Comprova si la ruta és pública.
 */
function esRutaPublica(path) {
    if (path === '/') {
        return true;
    }
    if (path === '/event' || path.startsWith('/event/')) {
        return true;
    }
    return rutesPubliquesPrefixes.some((p) => {
        if (p === '/') {
            return false;
        }
        return path === p || path.startsWith(`${p}/`);
    });
}

/**
 * Comprova si cal autenticació per accedir a la ruta.
 */
function esRutaProtegida(path) {
    return prefixosProtegits.some((prefix) => path === prefix || path.startsWith(`${prefix}/`));
}

/**
 * Middleware global d'autenticacio per protegir rutes.
 * A. Inicialitza l'estat d'autenticacio al client.
 * B. Redirigeix usuaris autenticats que intenten accedir a login/register cap a la landing.
 * C. Guarda return_to i redirigeix a login si la ruta és protegida.
 */
export default defineNuxtRouteMiddleware(async (to) => {
    const authStore = useAuthStore();

    if (process.client) {
        await authStore.initAuth();
    }

    const estaAutenticat = authStore.estat.estaAutenticat;
    const esAuth = to.path === '/auth/login' || to.path === '/auth/register';
    const protegida = esRutaProtegida(to.path);
    const publica = esRutaPublica(to.path);

    if (estaAutenticat && esAuth) {
        return navigateTo('/');
    }

    if (!estaAutenticat && protegida) {
        const cookieReturnTo = useCookie('return_to', { maxAge: 60 * 30, path: '/' });
        cookieReturnTo.value = to.fullPath;
        return navigateTo('/auth/login');
    }

    if (!estaAutenticat && !publica && !protegida) {
        return;
    }

    return;
});
