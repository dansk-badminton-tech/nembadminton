import apolloProvider from "./graphql";
require('./bootstrap');
import App from "./views/App";
import Vue from 'vue'
import router from "./router";
import VueI18n from 'vue-i18n'
import da from "./i18n/da";

const i18n = new VueI18n({
                             locale: 'da',
                             messages: {
                                 da
                             }
                         })

new Vue({
            components: {App},
            apolloProvider,
            i18n,
            router
        }).$mount("#app");
