// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    compatibilityDate: '2025-07-15',
    css: ['~/assets/styles/main.scss'],
    devtools: { enabled: true },
    modules: ['@nuxt/eslint'],
    runtimeConfig: {
        public: {
            apiBaseUrl: 'http://localhost:8000/api',
        },
    },
    app: {
        head: {
            title: 'ToDoTest',
            link: [
                { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
                {
                    rel: 'preconnect',
                    href: 'https://fonts.gstatic.com',
                    crossorigin: '',
                },
                {
                    rel: 'stylesheet',
                    href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap',
                },
            ],
        },
    },
    typescript: {
        strict: true,
        typeCheck: true,
    },
})
