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
//
// RENDER_FUNCTION: Buefy 3 components with a hand-written zero-arg render()
// (BTableColumn, BFieldBody, BCollapse, BNavbar, BSlotComponent, ...) get
// flagged _compatWrapped by @vue/compat, which swaps their $slots for a Vue 2
// proxy that *invokes* each slot on property read with no arguments. Buefy 3
// reads $slots the Vue 3 way, so b-table's `column.$slots.default` existence
// check would run a `v-slot="props"` body with props === undefined.
configureCompat({COMPONENT_V_MODEL: false, RENDER_FUNCTION: false})

/* Router & Store */
import router from './router'
import {toggleAsideMobile, toggleAsideDesktopOnly} from './store/layout'

/* Vue. Main component */
import Skeleton from './Skeleton.vue'
import apolloProvider from "../graphql";

import {Fragment} from 'vue-frag'

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

app.use(router)
app.use(apolloProvider)
app.use(Buefy)
app.component('Fragment', Fragment)

new Vue({
            router,
            apolloProvider,
            render: h => h(Skeleton)
        })
    .$mount('#app')
app.mount('#app')
