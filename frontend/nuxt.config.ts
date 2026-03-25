//================================ CONFIGURACIÓ NUXT 4 ============

export default defineNuxtConfig({
    compatibilityDate: '2025-07-15',
    devtools: { enabled: true },
    
    // Estructura de directoris (Nuxt 4 Standard)
    srcDir: 'app/',
    dir: {
        pages: 'app/pages',
        layouts: 'app/layouts',
        middleware: 'app/middleware',
        plugins: 'app/plugins',
    },

    // Mòduls
    modules: [
        '@pinia/nuxt',
    ],

    // Configuració CSS (Estètica DICE)
    css: [
        '~/assets/css/main.css',
    ],

    // Variables d'entorn pública
    runtimeConfig: {
        public: {
            apiUrl: process.env.NUXT_PUBLIC_API_URL || 'http://localhost:8000',
            socketUrl: process.env.NUXT_PUBLIC_SOCKET_URL || 'http://localhost:3001',
        }
    },

    // Configuració de renderitzat
    routeRules: {
        '/': { prerender: true },
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
