<template>
    <card-component
        title="Tilslut klub"
        icon="lock"
    >
    <div class="mb-2">
        <form @submit="create">
            <b-field label="Navn">
                <b-input v-model="name" icon="account" placeholder="Dit navn" required></b-input>
            </b-field>
            <b-field label="Adgangskode">
                <b-input v-model="password" icon="lock" placeholder="********" type="password"></b-input>
            </b-field>
            <b-field label="Gentag adgangskode">
                <b-input v-model="password_confirmation" icon="lock" placeholder="********" type="password"></b-input>
            </b-field>
            <b-field label="Email">
                <b-input v-model="email" icon="email" disabled type="email"></b-input>
            </b-field>
            <b-field label="Role">
                <b-input v-model="role" icon="account-group" disabled placeholder="Din role"></b-input>
            </b-field>
            <label class="label">Inviteret til</label>
            <article class="message is-warning-passive">
                <div class="message-body">
                    <p>
                        Klubhus: {{invitation.clubhouse.name}}
                    </p>
                    <p>
                        Inviteret af: {{invitation.inviter.name}}
                    </p>
                </div>
            </article>
            <b-field grouped>
                <b-button class="control" :loading="loading" type="submit">Opret</b-button>
            </b-field>
        </form>
    </div>
    </card-component>
</template>

<script>
import gql from "graphql-tag"
import CardComponent from "../../components/CardComponent.vue";
import BadmintonPlayerClubs from "@/components/badminton-player/BadmintonPlayerClubs.vue";
import {setAuthToken} from "../../../auth";
import {extractErrors, roles} from "@/helpers";

export default {
    name: "CreateUserInvitation",
    components: {CardComponent, BadmintonPlayerClubs},
    props: {
        token: String
    },
    data() {
        return {
            name: null,
            email: null,
            role: null,
            password: null,
            password_confirmation: null,
            loading: false,
            accepted: false
        }
    },
    apollo: {
        invitation: {
            query: gql`
                query invitation($token: ID!){
                    invitation(token: $token){
                        id
                        inviteeEmail
                        role
                        status
                        clubhouse {
                            id
                            name
                        }
                        inviter {
                            id
                            name
                        }
                    }
                }
            `,
            variables(){
                return {
                    token: this.token
                }
            },
            result({data}){
                console.log(data.invitation)
                this.email = data.invitation.inviteeEmail
                this.role = this.resolveLabel(data.invitation.role)
            }
        }
    },
    methods: {
        resolveLabel(role){
            role = roles.find(r => r.value.toLowerCase() === role.toLowerCase())
            return role.label
        },
        create() {
            this.loading = true;
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation acceptInvitation($token: ID!){
                          acceptInvitation(token: $input){
                            status
                            tokens{
                                access_token
                            }
                          }
                        }
                    `,
                    variables: {
                        input: {
                            name: this.name,
                            email: this.email,
                            organization_id: this.clubId,
                            player_id: this.playerId,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        }
                    }
                })
                .then(({data}) => {
                    setAuthToken(data.register.tokens.access_token)
                    this.$root.$emit('loggedIn')
                    this.$router.push({name: 'onboarding'})
                })
                .catch(({graphQLErrors}) => {
                    let errors = extractErrors(graphQLErrors)
                    this.$buefy.snackbar.open({
                            duration: 6000,
                            type: 'is-danger',
                            message: `Kunne ikke oprette bruger. <br />` + errors.join('<br />')
                        })
                })
                .finally(() => {
                    this.loading = false;
                })
        }
    }
}
</script>

<style scoped>

</style>
