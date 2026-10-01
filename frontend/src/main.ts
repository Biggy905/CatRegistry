import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import VueSelect from '@ttbooking/vue-select'
import 'bootstrap/dist/css/bootstrap.min.css'
import '@vueform/slider/themes/default.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import '@ttbooking/vue-select/style.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')
