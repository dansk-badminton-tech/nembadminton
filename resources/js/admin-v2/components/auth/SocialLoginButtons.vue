<template>
    <div v-if="googleEnabled" class="social-login-buttons">
        <b-field>
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
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import gql from 'graphql-tag'
import { setAuthToken } from '../../../auth'
import ME from '../../../queries/me.gql'
import { extractErrors } from '@/helpers'

const GOOGLE_SDK_URL = 'https://accounts.google.com/gsi/client'

// Module-level promise cache so the SDK loads at most once per page load,
// even if the component is mounted on multiple pages back-to-back.
let googleSdkPromise = null

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
            loadingProvider: null, // null | 'google'
            googleClient: null,
        }
    },
    computed: {
        googleEnabled() {
            return !!import.meta.env.VITE_GOOGLE_CLIENT_ID
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
                    this.routeAfterLogin(data.me.clubhouse.id)
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
        routeAfterLogin(clubhouseId) {
            if (this.invitationToken) {
                this.$router.push({ name: 'invitation', params: { token: this.invitationToken } })
                return
            }
            if (this.mode === 'signup') {
                this.$router.push({ name: 'sign-up-finish' })
                return
            }
            this.$router.push({ name: 'home', params: {clubhouseId: clubhouseId} })
        },
    },
})
</script>

<style scoped>
.social-login-buttons {
    margin-top: 0.5rem;
}
</style>
