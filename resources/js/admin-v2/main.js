/* Styles */
import '@/scss/main.scss'

/* Core */
import { createApp, h } from 'vue'
import Buefy from 'buefy'

/* Router & Store */
import router from './router'
import store from './store'

/* Vue. Main component */
import Skeleton from './Skeleton.vue'
import apolloProvider from "../graphql";

import VueClipboard from "vue-clipboard2";

/* Sentry */
//import * as Sentry from "@sentry/vue";

/* Default title tag */
const defaultDocumentTitle = 'Nembadminton'

/* Sentry Configuration */
// Sentry.init({
//     Vue,
//     dsn: import.meta.env.VITE_SENTRY_DSN_PUBLIC,
//     environment: import.meta.env.VITE_SENTRY_ENVIRONMENT || 'development',
//     sampleRate: parseFloat(import.meta.env.VITE_SENTRY_SAMPLE_RATE) || 1.0,
// });

/* Collapse mobile aside menu on route change & set document title from route meta */
router.afterEach(to => {
    store.commit('asideMobileStateToggle', false)
    store.dispatch('asideDesktopOnlyToggle', false)

    if (to.meta && to.meta.title) {
        document.title = `${to.meta.title} — ${defaultDocumentTitle}`
    } else {
        document.title = defaultDocumentTitle
    }
})


const app = createApp({render: () => h(Skeleton),})
    .use(router)
    .use(store)
    .use(apolloProvider)
    .use(Buefy)
    .use(VueClipboard)
    .mount("#app")
