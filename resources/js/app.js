import './bootstrap'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import dayjs from 'dayjs'
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import VueTippy from 'vue-tippy'

const appName = window.document.getElementsByTagName('title')[0]?.innerText ||
  'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,

  resolve: (name) => resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue'),
  ),

  progress: {
    color: '#11b981',
  },

  setup ({ el, App, props, plugin }) {

    const vapp = createApp({ render: () => h(App, props) })
    vapp.config.globalProperties.$date = dayjs

    const toastOptions = {}

    vapp.use(plugin)
        .use(Toast, toastOptions)
        .use(VueTippy)
        .mixin({ methods: { route } })
        .mount(el)
  },
})
