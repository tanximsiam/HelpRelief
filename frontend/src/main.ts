// import './assets/main.css'
import './assets/tailwind.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { useAuth } from '@/stores/auth'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')

const auth = useAuth();
auth.fetchUser().catch(() => auth.logout());
