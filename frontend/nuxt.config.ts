//================================ CONFIGURACIÓ NUXT 4 ============

const proxyApi = process.env.NUXT_API_PROXY_TARGET || 'http://127.0.0.1:8000';

export default defineNuxtConfig({
    compatibilityDate: '2025-07-15',
    /**
     * Desactivat: amb certes peticions fantasma (extensions del navegador, rutes absolutes
     * tipus /components/ui/...), vite-plugin-inspect provoca ENOENT en interceptar la càrrega.
     * Pots tornar a activar-lo si cal el panell d’inspecció de Vite.
     */
    devtools: { enabled: false },

    hooks: {
        'vite:extendConfig'(config) {
            const plugins = config.plugins;
            if (!Array.isArray(plugins)) {
                return;
            }
            config.plugins = plugins.filter((p) => {
                if (p && typeof p === 'object' && 'name' in p) {
                    return (p as { name?: string }).name !== 'vite-plugin-inspect';
                }
                return true;
            });
        },
    },

    // Estructura de directoris (Nuxt 4 Standard)
    srcDir: 'app/',
    dir: {
        pages: 'pages',
        middleware: 'middleware',
        plugins: 'plugins',
    },

    // Mòduls
    modules: [
        '@pinia/nuxt',
        '@nuxtjs/tailwindcss',
    ],

    // Configuració CSS (Estètica DICE)
    css: [
        '~/assets/css/main.css',
    ],

    vite: {
        server: {
            proxy: {
                '/api': {
                    target: proxyApi,
                    changeOrigin: true,
                },
            },
        },
    },

    // Variables d'entorn pública
    runtimeConfig: {
        public: {
            apiUrl: process.env.NUXT_PUBLIC_API_URL || 'http://localhost:8000',
            socketUrl: process.env.NUXT_PUBLIC_SOCKET_URL || 'http://localhost:3001',
            gatekeeperUrl: process.env.NUXT_PUBLIC_GATEKEEPER_URL || 'http://localhost:3001',
        }
    },

    // Configuració de renderitzat
    routeRules: {
        '/': { ssr: false },
        '/mapa/**': { ssr: false },
        '/cuenta/**': { ssr: false },
    },

    // Cabçaleres de seguretat
    app: {
        head: {
            title: 'TR3 TicketMaster',
            meta: [
                { charset: 'utf-8' },
                { name: 'viewport', content: 'width=device-width, initial-scale=1' },
                { name: 'description', content: 'Plataforma de reserves d\'entrades' }
            ],
            link: [
                { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap' }
            ]
        }
    }
})
