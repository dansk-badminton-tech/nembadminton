/* Styles */
import '@/scss/main.scss'

/* Core */
import {createApp} from 'vue'
import Buefy from 'buefy'

/* Router & Store */
import router from './router'
import {toggleAsideMobile, toggleAsideDesktopOnly} from './store/layout'

/* Vue. Main component */
import Skeleton from './Skeleton.vue'
import apolloProvider from "../graphql";

import * as Sentry from "@sentry/vue";

/* Default title tag */
const defaultDocumentTitle = 'Nembadminton'

/* Collapse mobile aside menu on route change & set document title from route meta */
router.afterEach(to => {
    toggleAsideMobile(false)
    toggleAsideDesktopOnly(false)

    if (to.meta && to.meta.title) {
        document.title = `${to.meta.title} — ${defaultDocumentTitle}`
    } else {
        document.title = defaultDocumentTitle
    }
})

const app = createApp(Skeleton)

Sentry.init({
    app,
    dsn: import.meta.env.VITE_SENTRY_DSN_PUBLIC,
    environment: import.meta.env.VITE_SENTRY_ENVIRONMENT || 'development',
    sampleRate: parseFloat(import.meta.env.VITE_SENTRY_SAMPLE_RATE) || 1.0,
});

app.use(router)
app.use(apolloProvider)
app.use(Buefy)

app.mount('#app')
