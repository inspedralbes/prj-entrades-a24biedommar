<script setup>
/**
 * Arrel de l'aplicació Nuxt.
 * Cal un bloc <script> perquè Vite/PostCSS no intentin processar el SFC com a CSS pur.
 */
</script>

<template>
    <div class="min-h-screen bg-black text-white">
        <NuxtRouteAnnouncer />
        <!-- Sense NuxtLayout + layouts/default.vue: evita bug PostCSS/Tailwind amb SFC només template -->
        <NuxtPage />
    </div>
</template>

<style scoped>
/* Evita mòduls d'estil virtuals buits que confonen PostCSS amb el contingut del template */
</style>
