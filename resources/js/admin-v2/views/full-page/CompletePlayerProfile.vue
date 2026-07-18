<template>
    <card-component title="Færdiggør din profil" icon="account-circle">
        <template v-slot:default>
            <div v-if="$apollo.loading" class="has-text-centered py-5">
                <b-icon icon="loading" custom-size="is-large" />
                <p class="has-text-grey-light mt-3">Henter din profil...</p>
            </div>
            <div v-else-if="!me" class="has-text-centered py-5">
                <p class="has-text-grey-light">Kunne ikke hente din profil. Prøv at genindlæse siden.</p>
            </div>
            <div v-else>
                <b-message type="is-info" :closable="false">
                    <p class="mb-1"><strong>Hej {{ me.name }}!</strong></p>
                    Du skal vælge din badminton spiller for at fortsætte. Søg efter dit navn og vælg dig selv.
                </b-message>

                <form @submit.prevent="submit">
                    <member-search-autocomplete
                        :clubhouse-id="me?.clubhouse?.id"
                        :initial-player-id="me?.player_id"
                        @select="onMemberSelect"
                        @clear="onMemberClear"
                    />

                    <hr>

                    <b-field>
                        <b-button
                            native-type="submit"
                            type="is-info"
                            size="is-medium"
                            expanded
                            :loading="isLoading"
                            :disabled="!playerId"
                            icon-left="check-circle"
                        >
                            Gem og fortsæt
                        </b-button>
                    </b-field>
                </form>

                <p class="is-size-7 has-text-grey-light has-text-centered mt-4">
                    Kan du ikke finde dig selv? Kontakt din klub admin for at blive importeret.
                </p>
            </div>
        </template>
    </card-component>
</template>

<script>
import { defineComponent } from 'vue'
import CardComponent from "@/components/CardComponent.vue"
import MemberSearchAutocomplete from "@/components/MemberSearchAutocomplete.vue"
import ME from "../../../queries/me.gql"
import gql from "graphql-tag"
import { extractErrors } from "@/helpers.js"

export default defineComponent({
    name: "CompletePlayerProfile",
    components: { CardComponent, MemberSearchAutocomplete },
    data() {
        return {
            isLoading: false,
            playerId: null
        }
    },
    apollo: {
        me: {
            query: ME
        }
    },
    methods: {
        onMemberSelect(refId) {
            this.playerId = refId
        },
        onMemberClear() {
            this.playerId = null
        },
        submit() {
            if (!this.playerId) {
                return
            }
            this.isLoading = true
            this.$apollo.mutate({
                mutation: gql`
                    mutation updateMe($input: UpdateMe!) {
                        updateMe(input: $input) {
                            id
                            player_id
                        }
                    }
                `,
                variables: {
                    input: {
                        name: this.me.name,
                        email: this.me.email,
                        player_id: this.playerId
                    }
                }
            }).then(() => {
                this.$buefy.snackbar.open({
                    message: 'Din profil er nu opdateret',
                    type: 'is-success',
                    duration: 2000
                })
                this.$root.$emit('loggedIn')
                this.$router.push({ name: 'home', params: { clubhouseId: this.me.clubhouse.id } })
            }).catch(({ graphQLErrors }) => {
                let errors = extractErrors(graphQLErrors)
                this.$buefy.snackbar.open({
                    message: "Kunne ikke opdatere din profil. " + errors.join(', '),
                    type: 'is-danger',
                    duration: 5000
                })
            }).finally(() => {
                this.isLoading = false
            })
        }
    }
})
</script>
