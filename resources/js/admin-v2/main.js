/* Styles */
import '@/scss/main.scss'

/* Core */
import {createApp, configureCompat} from 'vue'
import Buefy from 'buefy'

// Buefy 3 and our migrated components use the Vue 3 v-model contract
// (modelValue/update:modelValue). Disabling the runtime COMPONENT_V_MODEL
// compat stops @vue/compat's convertLegacyVModelProps from re-mapping those
// props back to Vue 2's value/input semantics (which would break Buefy 3
// form inputs, e.g. typing being overwritten on login).
configureCompat({COMPONENT_V_MODEL: false})

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

const app = createApp(Skeleton)

app.use(router)
app.use(store)
app.use(apolloProvider)
app.use(Buefy)
app.component('Fragment', Fragment)

app.mount('#app')
