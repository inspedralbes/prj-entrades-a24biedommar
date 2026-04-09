<script setup>
const authStore = useAuthStore();

const correu = ref('');
const contrasenya = ref('');
const error = ref('');
const loading = ref(false);

const router = useRouter();
const route = useRoute();
const cookieReturnToPrefill = useCookie('return_to', { path: '/', maxAge: 60 * 30 });

/** Query ?return_to= des del modal de compra → cookie per al redirect post-login. */
onMounted(() => {
    const q = route.query.return_to;
    if (typeof q === 'string' && q.startsWith('/')) {
        cookieReturnToPrefill.value = q;
    }
});

/**
 * Després del login: return_to segur del backend o /.
 */
async function handleLogin() {
    error.value = '';
    loading.value = true;

    if (!correu.value || !contrasenya.value) {
        error.value = 'Tots els camps son obligatoris';
        loading.value = false;
        return;
    }

    const cookieReturnTo = useCookie('return_to', { path: '/' });

    try {
        const returnTo = cookieReturnTo.value || '/';
        await authStore.login(correu.value, contrasenya.value, returnTo);

        const desti = cookieReturnTo.value || '/';
        cookieReturnTo.value = null;

        await router.push(desti);
    } catch (err) {
        let msg = 'Credencials incorrectes';
        if (err && err.data && err.data.missatge) {
            msg = err.data.missatge;
        }
        error.value = msg;
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-black flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <h1 class="text-4xl font-extrabold text-white text-center mb-8 uppercase tracking-wider">
                Entrar
            </h1>

            <form @submit.prevent="handleLogin" class="space-y-6">
                <div>
                    <label class="block text-white text-sm font-bold mb-2 uppercase">
                        Correu electronic
                    </label>
                    <input
                        v-model="correu"
                        type="email"
                        class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#FF0055] transition-colors"
                        placeholder="el.teu@email.com"
                        required
                    />
                </div>

                <div>
                    <label class="block text-white text-sm font-bold mb-2 uppercase">
                        Contrasenya
                    </label>
                    <input
                        v-model="contrasenya"
                        type="password"
                        class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#FF0055] transition-colors"
                        placeholder="********"
                        required
                    />
                </div>

                <div v-if="error" class="text-red-500 text-sm font-bold text-center">
                    {{ error }}
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full py-4 bg-[#FF0055] text-black font-black uppercase text-xl rounded-full hover:opacity-90 transition-opacity disabled:opacity-50"
                >
                    <span v-if="loading">Carregant...</span>
                    <span v-else>Iniciar Sessio</span>
                </button>

                <div class="text-center">
                    <NuxtLink to="/auth/register" class="text-[#00F0FF] font-bold hover:underline">
                        Crear un compte
                    </NuxtLink>
                </div>
            </form>
        </div>
    </div>
</template>
