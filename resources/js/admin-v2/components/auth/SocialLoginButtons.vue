<template>
    <div v-if="googleEnabled || facebookEnabled" class="social-login-buttons">
        <p class="has-text-centered has-text-grey is-size-7 my-3">eller</p>
        <b-field v-if="googleEnabled">
            <b-button
                dusk="social-login-google"
                type="is-light"
                expanded
                size="is-medium"
                :loading="loadingProvider === 'google'"
                :disabled="!googleReady || loadingProvider !== null"
                @click="loginWithGoogle"
            >
                <b-icon icon="google" size="is-small" class="mr-2"></b-icon>
                {{ buttonLabel('Google') }}
            </b-button>
        </b-field>
        <b-field v-if="facebookEnabled">
            <b-button
                dusk="social-login-facebook"
                type="is-info"
                expanded
                size="is-medium"
                :loading="loadingProvider === 'facebook'"
                :disabled="!facebookReady || loadingProvider !== null"
                @click="loginWithFacebook"
            >
                <b-icon icon="facebook" size="is-small" class="mr-2"></b-icon>
                {{ buttonLabel('Facebook') }}
            </b-button>
        </b-field>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import gql from 'graphql-tag'
import { setAuthToken } from '../../../auth'
import ME from '../../../queries/me.gql'
import { extractErrors } from '@/helpers'

const GOOGLE_SDK_URL = 'https://accounts.google.com/gsi/client'
const FACEBOOK_SDK_URL = 'https://connect.facebook.net/en_US/sdk.js'
const FACEBOOK_GRAPH_VERSION = 'v18.0'

// Module-level promise caches so SDKs load at most once per page load,
// even if the component is mounted on multiple pages back-to-back.
let googleSdkPromise = null
let facebookSdkPromise = null

function loadGoogleSdk() {
    if (googleSdkPromise) return googleSdkPromise
    googleSdkPromise = new Promise((resolve, reject) => {
        if (window.google && window.google.accounts && window.google.accounts.oauth2) {
            resolve()
            return
        }
        const s = document.createElement('script')
        s.src = GOOGLE_SDK_URL
        s.async = true
        s.defer = true
        s.onload = () => resolve()
        s.onerror = () => reject(new Error('Google SDK failed to load'))
        document.head.appendChild(s)
    })
    return googleSdkPromise
}

function loadFacebookSdk(appId) {
    if (facebookSdkPromise) return facebookSdkPromise
    facebookSdkPromise = new Promise((resolve, reject) => {
        if (window.FB) {
            resolve()
            return
        }
        window.fbAsyncInit = function () {
            window.FB.init({
                appId: appId,
                cookie: false,
                xfbml: false,
                version: FACEBOOK_GRAPH_VERSION,
            })
            resolve()
        }
        const s = document.createElement('script')
        s.src = FACEBOOK_SDK_URL
        s.async = true
        s.defer = true
        s.crossOrigin = 'anonymous'
        s.onerror = () => reject(new Error('Facebook SDK failed to load'))
        document.head.appendChild(s)
    })
    return facebookSdkPromise
}

export default defineComponent({
    name: 'SocialLoginButtons',
    props: {
        mode: {
            type: String,
            default: 'login',
            validator: (value) => ['login', 'signup'].includes(value),
        },
        invitationToken: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            googleReady: false,
            facebookReady: false,
            loadingProvider: null, // null | 'google' | 'facebook'
            googleClient: null,
        }
    },
    computed: {
        googleEnabled() {
            return !!import.meta.env.VITE_GOOGLE_CLIENT_ID
        },
        facebookEnabled() {
            return !!import.meta.env.VITE_FACEBOOK_APP_ID
        },
    },
    mounted() {
        if (this.googleEnabled) {
            loadGoogleSdk()
                .then(() => {
                    this.googleClient = window.google.accounts.oauth2.initTokenClient({
                        client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
                        scope: 'openid profile email',
                        callback: (response) => this.handleGoogleResponse(response),
                        error_callback: (error) => {
                            this.loadingProvider = null
                            if (error && error.type && error.type !== 'popup_closed') {
                                this.$buefy.snackbar.open({
                                    duration: 6000,
                                    type: 'is-danger',
                                    message: 'Google login mislykkedes. Prøv igen.',
                                })
                            }
                        },
                    })
                    this.googleReady = true
                })
                .catch(() => this.showLoadError())
        }
        if (this.facebookEnabled) {
            loadFacebookSdk(import.meta.env.VITE_FACEBOOK_APP_ID)
                .then(() => {
                    this.facebookReady = true
                })
                .catch(() => this.showLoadError())
        }
    },
    methods: {
        buttonLabel(provider) {
            return this.mode === 'signup' ? `Opret med ${provider}` : `Log ind med ${provider}`
        },
        showLoadError() {
            this.$buefy.snackbar.open({
                duration: 5000,
                type: 'is-danger',
                message: 'Kunne ikke indlæse social login. Prøv igen.',
            })
        },
        loginWithGoogle() {
            if (!this.googleReady || !this.googleClient) return
            this.loadingProvider = 'google'
            // requestAccessToken triggers the popup; the callback set in mounted() handles the response.
            this.googleClient.requestAccessToken({ prompt: '' })
        },
        handleGoogleResponse(response) {
            if (!response || !response.access_token) {
                this.loadingProvider = null
                return
            }
            this.callSocialLogin('google', response.access_token)
        },
        loginWithFacebook() {
            if (!this.facebookReady || !window.FB) return
            this.loadingProvider = 'facebook'
            window.FB.login(
                (response) => {
                    if (!response || response.status !== 'connected') {
                        this.loadingProvider = null
                        return
                    }
                    const grantedScopes = (response.authResponse.grantedScopes || '').split(',')
                    if (!grantedScopes.includes('email')) {
                        this.loadingProvider = null
                        this.$buefy.snackbar.open({
                            duration: 6000,
                            type: 'is-danger',
                            message: 'Facebook gav ikke adgang til din email. Prøv igen og tillad email.',
                        })
                        return
                    }
                    this.callSocialLogin('facebook', response.authResponse.accessToken)
                },
                { scope: 'email', return_scopes: true },
            )
        },
        callSocialLogin(provider, token) {
            this.$apollo
                .mutate({
                    mutation: gql`
                        mutation ($input: SocialLoginInput!) {
                            socialLogin(input: $input) {
                                access_token
                                token_type
                                expires_in
                            }
                        }
                    `,
                    variables: { input: { provider, token } },
                })
                .then(({ data }) => {
                    setAuthToken(data.socialLogin.access_token)
                    return this.$apollo.query({ query: ME, fetchPolicy: 'network-only' })
                })
                .then(({ data }) => {
                    this.$store.commit('user', {
                        id: data.me.id,
                        name: data.me.name,
                        email: data.me.email,
                        avatar: 'https://api.dicebear.com/6.x/fun-emoji/svg',
                        clubhouse: data.me.clubhouse,
                    })
                    this.routeAfterLogin()
                })
                .catch(({ graphQLErrors }) => {
                    const errors = extractErrors(graphQLErrors)
                    const detail = errors.length > 0 ? '<br />' + errors.join('<br />') : ''
                    this.$buefy.snackbar.open({
                        duration: 6000,
                        type: 'is-danger',
                        message: 'Login mislykkedes. Prøv igen.' + detail,
                    })
                })
                .finally(() => {
                    this.loadingProvider = null
                })
        },
        routeAfterLogin() {
            if (this.invitationToken) {
                this.$router.push({ name: 'invitation', params: { token: this.invitationToken } })
                return
            }
            if (this.mode === 'signup') {
                this.$router.push({ name: 'sign-up-finish' })
                return
            }
            this.$router.push({ name: 'home-redirect' })
        },
    },
})
</script>

<style scoped>
.social-login-buttons {
    margin-top: 0.5rem;
}
</style>
