<script setup>
const authStore = useAuthStore();

const nom = ref('');
const correu = ref('');
const contrasenya = ref('');
const confirmPassword = ref('');
const error = ref('');
const success = ref('');
const loading = ref(false);

const router = useRouter();

async function handleRegister() {
    error.value = '';
    success.value = '';
    loading.value = true;

    if (!nom.value || !correu.value || !contrasenya.value || !confirmPassword.value) {
        error.value = 'Tots els camps son obligatoris';
        loading.value = false;
        return;
    }

    if (contrasenya.value !== confirmPassword.value) {
        error.value = 'Les contrasenyes no coincideixen';
        loading.value = false;
        return;
    }

    if (contrasenya.value.length < 8) {
        error.value = 'La contrasenya ha de tenir minim 8 caracters';
        loading.value = false;
        return;
    }

    try {
        await authStore.register(nom.value, correu.value, contrasenya.value, confirmPassword.value);
        success.value = 'Compte creat correctament. Pots iniciar sessio.';
        setTimeout(() => {
            router.push('/auth/login');
        }, 2000);
    } catch (err) {
        let msg = 'Error en el registre';
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
                Registre
            </h1>

            <form @submit.prevent="handleRegister" class="space-y-6">
                <div>
                    <label class="block text-white text-sm font-bold mb-2 uppercase">
                        Nom
                    </label>
                    <input
                        v-model="nom"
                        type="text"
                        class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#FF0055] transition-colors"
                        placeholder="El teu nom"
                        required
                    />
                </div>

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
                        placeholder="Minim 8 caracters"
                        required
                    />
                </div>

                <div>
                    <label class="block text-white text-sm font-bold mb-2 uppercase">
                        Confirmar contrasenya
                    </label>
                    <input
                        v-model="confirmPassword"
                        type="password"
                        class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 text-white focus:outline-none focus:border-[#FF0055] transition-colors"
                        placeholder="Repeteix la contrasenya"
                        required
                    />
                </div>

                <div v-if="error" class="text-red-500 text-sm font-bold text-center">
                    {{ error }}
                </div>

                <div v-if="success" class="text-[#00F0FF] text-sm font-bold text-center">
                    {{ success }}
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full py-4 bg-[#FF0055] text-black font-black uppercase text-xl rounded-full hover:opacity-90 transition-opacity disabled:opacity-50"
                >
                    <span v-if="loading">Carregant...</span>
                    <span v-else>Crear Compte</span>
                </button>

                <div class="text-center">
                    <NuxtLink to="/auth/login" class="text-[#00F0FF] font-bold hover:underline">
                        Ja tens compte? Iniciar sessio
                    </NuxtLink>
                </div>
            </form>
        </div>
    </div>
</template>