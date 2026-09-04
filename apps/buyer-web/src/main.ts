import { createPinia } from 'pinia'
import { VueQueryPlugin } from '@tanstack/vue-query'
import { createApp } from 'vue'
import App from './App.vue'
import { router } from './router'
import { queryClient } from './lib/query'
import './assets/main.css'

createApp(App).use(createPinia()).use(VueQueryPlugin, { queryClient }).use(router).mount('#app')
