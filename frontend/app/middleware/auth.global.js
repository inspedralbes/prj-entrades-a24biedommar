import { useAuthStore } from '~/stores/auth';

/**
 * Rutes publics que no requereixen autenticacio.
 */
const rutesPubliques = ['/', '/login', '/register', '/events'];

/**
 * Rutes protegides que requereixen autenticacio.
 */
const rutesProtegides = ['/checkout', '/profile', '/tickets', '/waiting-room'];

/**
 * Middleware global d'autenticacio per protegir rutes.
 * A. Inicialitza l'estat d'autenticacio si no esta fet.
 * B. Redirigeix usuaris autenticats que intenten accedir a /login o /register.
 * C. Redirigeix usuaris no autenticats que intenten accedir a rutes protegides.
 * D. Guarda la URL actual per retornar desprès del login.
 */
export default defineNuxtRouteMiddleware(async (to, from) => {
    const authStore = useAuthStore();
    const router = useRouter();

    // A. Inicialitzar estat d'autenticacio si esta al client
    if (process.client && !authStore.estaAutenticatInicialitzat) {
        await authStore.initAuth();
    }

    const estaAutenticat = authStore.estaAutenticat;
    const esRutaPublica = rutesPubliques.includes(to.path);
    const esRutaProtegida = rutesProtegides.includes(to.path);
    const esRutaAuth = to.path === '/login' || to.path === '/register';

    // B. Usuari autenticat intenta accedir a rutes d'autenticacio -> Landing
    if (estaAutenticat && esRutaAuth) {
        return router.push('/');
    }

    // C. Usuari no autenticat intenta accedir a ruta protegida -> Login
    if (!estaAutenticat && esRutaProtegida) {
        const returnTo = to.fullPath;
        const cookieReturnTo = useCookie('return_to', { maxAge: 60 * 5 });
        cookieReturnTo.value = returnTo;

        return router.push('/login');
    }

    // D. Usuari autenticat accedeix a ruta protegida -> Permetre
    if (estaAutenticat && esRutaProtegida) {
        return;
    }

    // E. Rutes publiques -> Permetre
    return;
});