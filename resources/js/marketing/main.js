/* Styles */
import './scss/main.scss'
import Vue from "vue";
import router from "../admin-v2/router";
import App from "./App.vue";


new Vue({
            router,
            render: h => h(App)
        }).$mount('#app')
