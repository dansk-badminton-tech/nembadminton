<template>
    <b-button
        v-if="googleEnabled"
        dusk="connect-google-button"
        type="is-light"
        size="is-small"
        :loading="loading"
        :disabled="!googleReady || loading"
        @click="onClick"
    >
        <b-icon icon="google" size="is-small" class="mr-2"></b-icon>
        Tilknyt Google
    </b-button>
</template>

<script>
import { defineComponent } from 'vue'
import gql from 'graphql-tag'
import { setAuthToken } from '../../../auth'
import { extractErrors } from '@/helpers'

const GOOGLE_SDK_URL = 'https://accounts.google.com/gsi/client'

// Module-level promise cache so the SDK loads at most once per page load,
// even if the component is mounted and unmounted multiple times.
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
    name: 'ConnectGoogleButton',
    props: {
        currentUserEmail: {
            type: String,
            required: false,
            default: '',
        },
    },
    emits: ['linked'],
    data() {
        return {
            googleReady: false,
            googleClient: null,
            loading: false,
        }
    },
    computed: {
        googleEnabled() {
            return !!import.meta.env.VITE_GOOGLE_CLIENT_ID
        },
    },
    mounted() {
        if (!this.googleEnabled) return
        loadGoogleSdk()
            .then(() => {
                this.googleClient = window.google.accounts.oauth2.initTokenClient({
                    client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
                    scope: 'openid profile email',
                    callback: (response) => this.handleTokenResponse(response),
                    error_callback: (error) => {
                        this.loading = false
                        if (error && error.type && error.type !== 'popup_closed') {
                            this.showError('Google login mislykkedes. Prøv igen.')
                        }
                    },
                })
                this.googleReady = true
            })
            .catch(() => this.showError('Kunne ikke indlæse Google. Prøv igen.'))
    },
    methods: {
        onClick() {
            if (!this.googleReady || !this.googleClient) return
            this.loading = true
            this.googleClient.requestAccessToken({ prompt: '' })
        },
        showError(message) {
            this.$buefy.snackbar.open({
                duration: 6000,
                type: 'is-danger',
                message,
            })
        },
        handleTokenResponse(response) {
            if (!response || !response.access_token) {
                this.loading = false
                return
            }
            this.verifyAndLink(response.access_token)
        },
        verifyAndLink(token) {
            // Pre-check: ask Google for the email associated with this token.
            // If it doesn't match the current user, abort BEFORE calling the
            // backend mutation so the vendor trait can't write a social_providers
            // row tied to a different user.
            fetch('https://oauth2.googleapis.com/tokeninfo?access_token=' + encodeURIComponent(token))
                .then((res) => {
                    if (!res.ok) {
                        throw new Error('tokeninfo http ' + res.status)
                    }
                    return res.json()
                })
                .then((info) => {
                    if (info.error_description) {
                        this.loading = false
                        this.showError('Google-token blev afvist. Prøv igen.')
                        return
                    }
                    const tokenEmail = (info.email || '').toLowerCase()
                    const currentEmail = (this.currentUserEmail || '').toLowerCase()
                    if (tokenEmail !== currentEmail) {
                        this.loading = false
                        this.showError(
                            "Den valgte Google-konto har en anden email end din nuværende konto. Vælg en konto med email '" +
                                this.currentUserEmail +
                                "'.",
                        )
                        return
                    }
                    this.callSocialLogin(token)
                })
                .catch(() => {
                    this.loading = false
                    this.showError('Kunne ikke verificere Google-konto. Prøv igen.')
                })
        },
        callSocialLogin(token) {
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
                    variables: { input: { provider: 'google', token } },
                })
                .then(({ data }) => {
                    setAuthToken(data.socialLogin.access_token)
                    this.$emit('linked')
                })
                .catch(({ graphQLErrors }) => {
                    const errors = extractErrors(graphQLErrors)
                    const detail = errors.length > 0 ? '<br />' + errors.join('<br />') : ''
                    this.showError('Kunne ikke tilknytte Google-konto. Prøv igen.' + detail)
                })
                .finally(() => {
                    this.loading = false
                })
        },
    },
})
</script>
