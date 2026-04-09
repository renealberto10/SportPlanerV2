import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)

// Wait for the router to resolve the initial route before mounting.
// Without this, App.vue renders before route.meta is available,
// causing the dashboard shell to flash on public pages (landing, login).
router.isReady().then(() => app.mount('#app'))
