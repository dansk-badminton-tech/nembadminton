import Vue from 'vue'
import VueRouter from 'vue-router'
import Buefy from 'buefy'
import { Fragment } from 'vue-frag'
import VueI18n from 'vue-i18n'
import VueApollo from 'vue-apollo'
import 'buefy/dist/buefy.css'
import './vee-validate';
import './awesome-font'
import VueClipboard from 'vue-clipboard2'

Vue.use(VueApollo)
Vue.use(VueI18n)
Vue.use(Buefy)
Vue.use(VueRouter)
Vue.use(VueClipboard)
Vue.component('Fragment', Fragment)
