require('./bootstrap')

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress'
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import VueTippy from 'vue-tippy'


const appName = window.document.getElementsByTagName('title')[0]?.innerText ||
  'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => require(`./Pages/${name}.vue`),
  setup ({ el, app, props, plugin }) {

    const vapp = createApp({ render: () => h(app, props) })
    vapp.config.globalProperties.$date = dayjs

    const toastOptions = {

    }

    vapp.use(plugin).use(Toast, toastOptions)
      .mixin({ methods: { route } })
      .mount(el)

      vapp.use(VueTippy )
  },
})

InertiaProgress.init({ color: '#11b981' })
