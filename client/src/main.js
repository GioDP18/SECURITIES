import './assets/main.css'
import { createApp } from 'vue'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura'
import App from './App.vue'
import router from './router'
import { usePublicKey } from './composables/usePublicKey'

const app = createApp(App)

// Initialize public key
const { fetchPublicKey } = usePublicKey()
fetchPublicKey()

app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
})

app.use(router)
app.mount('#app')
