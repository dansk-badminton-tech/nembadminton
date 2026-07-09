<template>
    <card-component title="Færdiggør din profil" icon="account-circle">
        <div class="content">
            <p>Du skal vælge din badminton spiller for at fortsætte.</p>
            <p class="is-size-7 has-text-grey-light">
                Kan du ikke finde dig selv? Kontakt din klub admin for at blive importeret.
            </p>
        </div>
        <hr>
        <member-search-autocomplete
            :clubhouse-id="me?.clubhouse?.id"
            :initial-player-id="me?.player_id"
            @select="onMemberSelect"
            @clear="onMemberClear"
        />
        <hr>
        <b-field>
            <b-button type="is-info" :loading="isLoading" :disabled="!playerId" @click="submit">Gem og fortsæt</b-button>
        </b-field>
    </card-component>
</template>

<script>
import CardComponent from "@/components/CardComponent.vue";
import MemberSearchAutocomplete from "@/components/MemberSearchAutocomplete.vue";
import ME from "../../../queries/me.gql";
import gql from "graphql-tag";
import {extractErrors} from "@/helpers.js";

export default {
    name: "CompletePlayerProfile",
    components: {CardComponent, MemberSearchAutocomplete},
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
            const regex = /^[0-9]{6}-[0-9]{2}$/
            if (!regex.test(this.playerId)) {
                this.$buefy.snackbar.open({
                    duration: 4000,
                    type: 'is-danger',
                    message: 'BadmintonID skal være i formatet yymmdd-xx (f.eks. 010203-01)'
                })
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
                this.$router.push({name: 'home', params: {clubhouseId: this.me.clubhouse.id}})
            }).catch(({graphQLErrors}) => {
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
}
</script>
