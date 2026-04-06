import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Default from './layout/wrapper/index.vue'

// import meforma toaster
import Toaster from "@meforma/vue-toaster"

const app = createApp(App)

// đăng ký toaster
app.use(Toaster, {
  position: 'top-right',
  duration: 3000,
  closeOnClick: true,
})

app.use(router)
app.component("default-layout", Default)

app.mount("#app")
