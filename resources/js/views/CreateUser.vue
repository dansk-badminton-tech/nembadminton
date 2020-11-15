<template>
    <div>
        <b-field label="Navn">
            <b-input v-model="name"></b-input>
        </b-field>
        <b-field label="Email">
            <b-input v-model="email" type="email"></b-input>
        </b-field>
        <b-field label="Adgangskode">
            <b-input v-model="password" type="password"></b-input>
        </b-field>
        <b-field label="Gentag adgangskode">
            <b-input v-model="password_confirmation" type="password"></b-input>
        </b-field>
        <b-button @click="create">Opret</b-button>
    </div>
</template>

<script>
import gql from "graphql-tag"
import {extractErrors} from "../helpers";

export default {
    name: "CreateUser",
    data() {
        return {
            name: null,
            email: null,
            password: null,
            password_confirmation: null
        }
    },
    methods: {
        create() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation ($input: RegisterInput!){
                          register(input: $input){
                            status
                            tokens{access_token}
                          }
                        }
                    `,
                    variables: {
                        input: {
                            name: this.name,
                            email: this.email,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        }
                    }
                })
                .then(({data}) => {
                    localStorage.setItem('access_token', data.register.tokens.access_token)
                    this.$router.push({name: 'team-fight-recent'})
                })
                .catch(({graphQLErrors}) => {
                    let errors = extractErrors(graphQLErrors)
                    this.$buefy.snackbar.open(
                        {
                            duration: 6000,
                            type: 'is-danger',
                            message: `Kunne ikke oprette bruger. <br />` + errors.join('<br />')
                        })
                })
        }
    }
}
</script>

<style scoped>

</style>
