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
        <b-button :loading="loading" @click="create">Opret</b-button>
    </div>
</template>

<script>
import gql from "graphql-tag"
import {extractErrors} from "../helpers";
import {setAuthToken} from "../auth";

export default {
    name: "CreateUser",
    data() {
        return {
            name: null,
            email: null,
            password: null,
            password_confirmation: null,
            loading: false
        }
    },
    methods: {
        create() {
            this.loading = true;
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
                    setAuthToken(data.register.tokens.access_token)
                    this.$root.$emit('loggedIn')
                    this.$router.push({name: 'home'})
                })
                .catch(({graphQLErrors}) => {
                    let errors = extractErrors(graphQLErrors)
                    this.$buefy.snackbar.open(
                        {
                            duration: 6000,
                            type: 'is-danger',
                            message: `Kunne ikke oprette bruger. <br />` + errors.join('<br />')
                        })
                }).finally(
                () => {
                    this.loading = false;
                })
        }
    }
}
</script>

<style scoped>

</style>
