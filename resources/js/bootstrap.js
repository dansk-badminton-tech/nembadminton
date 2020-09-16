import Vue from 'vue'
import VueRouter from 'vue-router'
import Buefy from 'buefy'
import 'buefy/dist/buefy.css'
import {Plugin} from 'vue-fragment'
import VueI18n from 'vue-i18n'
import './vee-validate';
import VueApollo from 'vue-apollo'

Vue.use(VueApollo)
Vue.use(VueI18n)
Vue.use(Plugin)
Vue.use(Buefy)
Vue.use(VueRouter)
