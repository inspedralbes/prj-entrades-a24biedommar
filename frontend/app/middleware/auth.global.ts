import { useAuthStore } from '~/stores/auth';

const publicRoutes = ['/', '/login', '/register', '/events'];
const protectedRoutes = ['/checkout', '/profile', '/tickets', '/waiting-room'];

export default defineNuxtRouteMiddleware(async (to, from) => {
    const authStore = useAuthStore();
    const router = useRouter();

    // A. Inicialitzar estat d'autenticacio si no esta fet
    if (process.client && !authStore.state.isInitialized) {
        await authStore.initAuth();
    }

    const isAuthenticated = authStore.state.isAuthenticated;
    const isPublicRoute = publicRoutes.includes(to.path);
    const isProtectedRoute = protectedRoutes.includes(to.path);
    const isAuthRoute = to.path === '/login' || to.path === '/register';

    // B. Usuari autenticat intenta accedir a rutes auth -> redirigir a Landing
    if (isAuthenticated && isAuthRoute) {
        return router.push('/');
    }

    // C. Usuari NO autenticat intenta accedir a ruta protegida -> redirigir a login
    if (!isAuthenticated && isProtectedRoute) {
        // Guardar URL actual per retornar desprès del login
        const returnTo = to.fullPath;
        const returnToCookie = useCookie('return_to', { maxAge: 60 * 5 });
        returnToCookie.value = returnTo;
        
        return router.push('/login');
    }

    // D. Usuari autenticat intenta accedir a ruta protegida -> permetre
    if (isAuthenticated && isProtectedRoute) {
        return;
    }

    // E. Rutes públiques -> permetre
    return;
});