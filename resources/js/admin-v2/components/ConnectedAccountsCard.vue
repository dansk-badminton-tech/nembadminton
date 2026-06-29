<template>
    <card-component
        v-if="googleEnabled"
        title="Tilknyttede konti"
        icon="link-variant"
    >
        <div class="is-flex is-justify-content-space-between is-align-items-center">
            <span class="icon-text">
                <b-icon icon="google" />
                <span class="ml-2">Google</span>
            </span>
            <span v-if="googleLinked" class="has-text-success" dusk="google-linked-badge">
                <b-icon icon="check-circle" /> Tilknyttet
            </span>
            <connect-google-button
                v-else
                :current-user-email="userEmail || ''"
                @linked="handleLinked"
            />
        </div>
    </card-component>
</template>

<script>
import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import CardComponent from '@/components/CardComponent.vue'
import ConnectGoogleButton from '@/components/auth/ConnectGoogleButton.vue'
import ME from '../../queries/me.gql'

export default defineComponent({
    name: 'ConnectedAccountsCard',
    components: { CardComponent, ConnectGoogleButton },
    data() {
        return {
            me: null,
        }
    },
    apollo: {
        me: {
            query: ME,
        },
    },
    computed: {
        ...mapState(['userEmail']),
        googleEnabled() {
            return !!import.meta.env.VITE_GOOGLE_CLIENT_ID
        },
        googleLinked() {
            if (!this.me || !Array.isArray(this.me.socialProviders)) return false
            return this.me.socialProviders.some((p) => p.provider === 'google')
        },
    },
    methods: {
        handleLinked() {
            this.$apollo.queries.me.refetch().then(() => {
                this.$buefy.snackbar.open({
                    duration: 4000,
                    type: 'is-success',
                    message: 'Google-konto tilknyttet.',
                })
            })
        },
    },
})
</script>
