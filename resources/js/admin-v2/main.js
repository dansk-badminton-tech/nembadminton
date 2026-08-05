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

import {Fragment} from 'vue-frag'

/* Default title tag */
const defaultDocumentTitle = 'Nembadminton'

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
Vue.use(apolloProvider)
// @vue/compat's `new Vue()` mount only carries Vue.prototype globals into the
// mounted app (it replaces app.config.globalProperties). @vue/apollo-option's
// `launch()` reads `this.$apolloProvider`, so expose it the Vue 2 way to ensure
// `apollo:` smart queries are registered.
Vue.prototype.$apolloProvider = apolloProvider
Vue.component('Fragment', Fragment)

new Vue({
            router,
            store,
            apolloProvider,
            render: h => h(Skeleton)
        })
    .$mount('#app')
