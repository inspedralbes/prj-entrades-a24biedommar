<script setup>
const authStore = useAuthStore();

const correu = ref('');
const contrasenya = ref('');
const error = ref('');
const loading = ref(false);

const router = useRouter();

async function handleLogin() {
    error.value = '';
    loading.value = true;

    if (!correu.value || !contrasenya.value) {
        error.value = 'Tots els camps son obligatoris';
        loading.value = false;
        return;
    }

    try {
        const returnTo = useCookie('return_to').value || '/';
        await authStore.login(correu.value, contrasenya.value, returnTo);
        
        const redirectUrl = useCookie('return_to').value || '/';
        useCookie('return_to').value = null;
        
        await router.push(redirectUrl);
    } catch (err) {
        error.value = err.data?.missatge || 'Credencials incorrectes';
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
                    <NuxtLink to="/register" class="text-[#00F0FF] font-bold hover:underline">
                        Crear un compte
                    </NuxtLink>
                </div>
            </form>
        </div>
    </div>
</template>