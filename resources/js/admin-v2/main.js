/* Styles */
import '@/scss/main.scss'

/* Core */
import Vue from 'vue'
import Buefy from 'buefy'

/* Router & Store */
import router from './router'
import store from './store'

/* Vue. Main component */
import App from './App.vue'
import apolloProvider from "../graphql";
import VueApollo from "vue-apollo";

import { Fragment } from 'vue-frag'
import VueClipboard from "vue-clipboard2";

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
Vue.use(VueApollo)
Vue.use(VueClipboard)
Vue.component('Fragment', Fragment)
Vue.component('kustomer', require('./components/Kustomer/Kustomer.vue'));

new Vue({
  router,
  store,
    apolloProvider,
  render: h => h(App)
}).$mount('#app')
