/* Styles */
import '@/scss/main.scss'

/* Core */
import Vue from 'vue'
import Buefy from 'buefy'

/* Router & Store */
import router from './router'
import store from './store'

/* Vue. Main component */
import Skeleton from './Skeleton.vue'
import apolloProvider from "../graphql";
import VueApollo from "vue-apollo";

import {Fragment} from 'vue-frag'
import VueClipboard from "vue-clipboard2";

/* Sentry */
import * as Sentry from "@sentry/vue";

/* Default title tag */
const defaultDocumentTitle = 'Nembadminton'

/* Sentry Configuration */
Sentry.init({
    Vue,
    dsn: import.meta.env.VITE_SENTRY_DSN_PUBLIC,
    environment: import.meta.env.VITE_SENTRY_ENVIRONMENT || 'development',
    sampleRate: parseFloat(import.meta.env.VITE_SENTRY_SAMPLE_RATE) || 1.0,
});

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

Vue.config.productionTip = false

Vue.use(Buefy)
Vue.use(VueApollo)
Vue.use(VueClipboard)
Vue.component('Fragment', Fragment)

new Vue({
            router,
            store,
            apolloProvider,
            render: h => h(Skeleton)
        })
    .$mount('#app')
