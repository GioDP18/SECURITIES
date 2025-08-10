import './assets/main.css'
import { createApp } from 'vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura'
import App from './App.vue'
import router from './router'
import { usePublicKey } from './composables/usePublicKey'

async function bootstrap() {
    const app = createApp(App)

    // Initialize public key
    const { fetchPublicKey } = usePublicKey()
    await fetchPublicKey()

    app.use(PrimeVue, { theme: { preset: Aura } })
    app.use(router)
    app.mount('#app')
}

bootstrap()
