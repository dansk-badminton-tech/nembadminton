<script>
import ME from "../../../queries/me.gql";
import CardComponent from "@/components/CardComponent.vue";
import gql from "graphql-tag";

export default {
    name: "SignUpFinish",
    components: {CardComponent},
    props: {
        error: String|null
    },
    data(){
        return {
            isLoading: false,
            email: null,
            name: null
        }
    },
    apollo: {
        me: {
            query: ME,
        },
    },
    computed: {
        hasClubhouse() {
            return this?.me?.clubhouse !== null
        },
        showMissingClubhouseMessage(){
            return this.error === 'missingClubhouse'
        }
    },
    methods: {
        toDashboard(){
            this.$router.push({name: 'home', params: {clubhouseId: this.me.clubhouse.id}})
        },
        createClubhouse(){
            this.$apollo.mutate({
                mutation: gql`
                    mutation createClubhouse($input: CreateClubhouseInput!){
                        createClubhouse(input: $input){
                            id
                            name
                            email
                            users {
                                id
                            }
                            clubs {
                                id
                            }
                        }
                    }
                `,
                variables: {
                    input: {
                        name: this.name,
                        email: this.email,
                        user: {
                            connect: [this.me.id]
                        },
                        users: {
                            connect: [this.me.id]
                        },
                        clubs: {
                            connect: [this.me.club.id]
                        }
                    }
                },
                refetchQueries: [ME]
                })
                .then(({data}) => {
                    this.$router.push({name: 'onboarding'})
                })
                .catch((err) => {
                    console.log(err)
                })
        },
    }
}
</script>

<template>
    <card-component
        title="Tilslut klubhus"
        icon="lock"
    >
        <template v-slot:default>
            <b-message type="is-info" v-if="!hasClubhouse | showMissingClubhouseMessage">
                Du er ikke tilknyttet et klubhus. Opret din egen eller blev inviteret af en klubhus administrator fra en anden klub.
            </b-message>
            <div v-show=""></div>
            <div v-show="hasClubhouse">
                <h2 class="subtitle">Du er allerede tilknyttet et klubhus</h2>
                <b-button @click="toDashboard">Videre</b-button>
            </div>
            <form v-show="!hasClubhouse" @submit.prevent="createClubhouse">
                <b-field
                    horizontal
                    label="Name"
                    message="Navnet på klubhuset. Oftes klubbens navn">
                    <b-input
                        v-model="name"
                        name="name"
                        placeholder="SAIF"
                        required
                    />
                </b-field>
                <b-field
                    horizontal
                    label="E-mail"
                    message="Klubbens email eller primær kontaktperson">
                    <b-input
                        v-model="email"
                        name="email"
                        type="email"
                        placeholder="info@nembadminton.dk"
                        required
                    />
                </b-field>
                <b-button
                    native-type="submit"
                    type="is-info"
                    :loading="isLoading">
                    Opret
                </b-button>
            </form>
        </template>
    </card-component>
</template>

<style scoped>

</style>
