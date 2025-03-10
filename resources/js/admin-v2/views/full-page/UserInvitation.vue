<template>
    <card-component
        title="Tilslut klub"
        icon="lock"
    >
        <template v-slot:default>
            <div>
                <b-message
                    v-if="!me"
                    title="Du skal oprette en bruger først"
                    type="is-info"
                    aria-close-label="Close message">
                    Du skal oprette dig som bruger først, før du kan accepter invitation.
                </b-message>
                <h2 class="subtitle">Invitation</h2>
                <div class="content">
                    <p>
                        Invitation til: {{ invitation?.clubhouse?.name }}
                    </p>
                    <p>
                        Role: {{ invitation?.role }}
                    </p>
                    <p>
                        Status: {{ invitation?.status }}
                    </p>
                    <p>
                        Udløber: {{ invitation?.expiresAt }}
                    </p>
                    <p>
                        Accepted: {{ invitation?.acceptedAt }}
                    </p>
                    <p>
                        Inviteret af: {{ invitation?.inviter?.name }} ({{ invitation?.inviter?.email }})
                    </p>
                </div>
            </div>
        </template>
        <template v-slot:footer>
            <footer class="card-footer">
                <a v-if="!isLoggedIn" class="card-footer-item" @click="goToSignUp">Opret bruger</a>
                <a v-if="isLoggedIn && !isInvitationPending" class="card-footer-item" @click="goToDashboard">Videre</a>
                <a v-if="isLoggedIn && isInvitationPending" class="card-footer-item" @click="acceptInvitation">Accepter</a>
                <a v-if="isLoggedIn && isInvitationPending" class="card-footer-item" @click="declineInvitation">Afvis</a>
            </footer>
        </template>
    </card-component>
</template>

<script>
import gql from "graphql-tag"
import CardComponent from "../../components/CardComponent.vue";
import {roles} from "@/helpers";
import ME from "../../../queries/me.gql";

export default {
    name: "UserInvitation",
    components: {CardComponent},
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
    computed: {
        isLoggedIn(){
            return this.me !== undefined
        },
        isInvitationPending(){
            return this.invitation?.status.toLowerCase() === 'pending'
        }
    },
    apollo: {
        me: {
            query: ME,
        },
        invitation: {
            query: gql`
                query invitation($token: ID!){
                    invitation(token: $token){
                        id
                        role
                        status
                        expiresAt
                        acceptedAt
                        inviter {
                            name
                            email
                        }
                        clubhouse {
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
                this.email = data.invitation.inviteeEmail
                this.role = this.resolveLabel(data.invitation.role)
            }
        }
    },
    methods: {
        goToSignUp(){
            this.$router.push({name: 'sign-up', query: {invitationToken: this.token}})
        },
        goToDashboard(){
            this.$router.push({name: 'home', params: {clubhouseId: this.invitation.clubhouse.id}})
        },
        resolveLabel(role){
            role = roles.find(r => r.value.toLowerCase() === role.toLowerCase())
            return role.label
        },
        declineInvitation() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation declineInvitation($token: ID!){
                            declineInvitation(token: $token){
                                id
                                role
                                status
                                expiresAt
                                acceptedAt
                            }
                        }
                    `,
                    variables: {
                        token: this.token
                    }
                })
                .then(res => {
                    this.$root.$emit('loggedIn')
                    this.$apollo.queries.invitation.refresh()
                })
                .catch(err => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 6000,
                            type: 'is-danger',
                            message: `Kunne ikke afvise invitationen.`
                        })

                })
        },
        acceptInvitation() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation acceptInvitation($token: ID!){
                            acceptInvitation(token: $token){
                                id
                                role
                                status
                                expiresAt
                                acceptedAt
                            }
                        }
                    `,
                    variables: {
                        token: this.token
                    },
                })
                .then(res => {
                    this.$router.push({name: 'home', params: {clubhouseId: this.invitation.clubhouse.id}})
                })
                .catch(err => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 6000,
                            type: 'is-danger',
                            message: `Kunne ikke accepter invitationen.`
                        })

                })
        }
    }
}
</script>

<style scoped>

</style>
