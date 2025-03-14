import Vue from 'vue'
import Vuex from 'vuex'
import {clearAuthToken} from "../../auth";
import {ApolloClientInstance} from "../../graphql";

Vue.use(Vuex)

const store = new Vuex.Store(
    {
        state: {
            /* User */
            userId: null,
            userName: null,
            userEmail: null,
            userAvatar: null,
            clubhouse: null,

            /* NavBar */
            isNavBarVisible: true,

            /* FooterBar */
            isFooterBarVisible: true,

            /* Aside */
            isAsideVisible: true,
            isAsideMobileExpanded: false,

            /* Sample data (commonly used) */
            clients: []
        },
        mutations: {
            /* A fit-them-all commit */
            basic(state, payload) {
                state[payload.key] = payload.value
            },

            /* User */
            user(state, payload) {
                if (payload.id) {
                    state.userId = parseInt(payload.id)
                }
                if (payload.name) {
                    state.userName = payload.name
                }
                if (payload.email) {
                    state.userEmail = payload.email
                }
                if (payload.avatar) {
                    state.userAvatar = payload.avatar
                }
                if(payload.clubhouse) {
                    state.clubhouse = payload.clubhouse
                }
            },

            logout(state, payload) {
                state.userName = null
                state.userEmail = null
                state.userAvatar = null
                clearAuthToken()
                ApolloClientInstance.cache.reset()
            },

            /* Aside Mobile */
            asideMobileStateToggle(state, payload = null) {
                const htmlClassName = 'has-aside-mobile-expanded'

                let isShow

                if (payload !== null) {
                    isShow = payload
                } else {
                    isShow = !state.isAsideMobileExpanded
                }

                if (isShow) {
                    document.documentElement.classList.add(htmlClassName)
                } else {
                    document.documentElement.classList.remove(htmlClassName)
                }

                state.isAsideMobileExpanded = isShow
            },

            /* Full Page mode */
            fullPage(state, payload) {
                state.isNavBarVisible = !payload
                state.isAsideVisible = !payload
                state.isFooterBarVisible = !payload
            }
        },
        actions: {
            asideDesktopOnlyToggle(store, payload = null) {
                let method

                switch (payload) {
                    case true:
                        method = 'add'
                        break
                    case false:
                        method = 'remove'
                        break
                    default:
                        method = 'toggle'
                }
                document.documentElement.classList[method]('has-aside-desktop-only-visible')
            },
            toggleFullPage({commit}, payload) {
                commit('fullPage', payload)

                document.documentElement.classList[!payload
                                                   ? 'add'
                                                   : 'remove']('has-aside-left', 'has-navbar-fixed-top')
            }
        }
    })

export default store

export function useStore() {
    return store
}
