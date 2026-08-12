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

app.mount('#app')
