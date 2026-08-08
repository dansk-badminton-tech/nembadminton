import Vue from 'vue'
import {clearAuthToken} from '../../auth'
import {ApolloClientInstance} from '../../graphql'

export const currentUser = Vue.observable({
    userId: null,
    userName: null,
    userEmail: null,
    userAvatar: null,
    clubhouse: null
})

/* Partial update: only keys present in the payload are written, so callers can
   patch a single field (e.g. setUser({ name })). */
export function setUser(payload) {
    if (payload.id) {
        currentUser.userId = parseInt(payload.id)
    }
    if (payload.name) {
        currentUser.userName = payload.name
    }
    if (payload.email) {
        currentUser.userEmail = payload.email
    }
    if (payload.avatar) {
        currentUser.userAvatar = payload.avatar
    }
    if (payload.clubhouse) {
        currentUser.clubhouse = payload.clubhouse
    }
}

export function logout() {
    currentUser.userId = null
    currentUser.userName = null
    currentUser.userEmail = null
    currentUser.userAvatar = null
    currentUser.clubhouse = null

    clearAuthToken()
    ApolloClientInstance.cache.reset()
}
