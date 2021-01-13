<template>
    <form @submit.prevent="login">
        <b-field label="Email">
            <b-input v-model="email" type="email"></b-input>
        </b-field>
        <b-field label="Adgangskode">
            <b-input v-model="password" type="password"></b-input>
        </b-field>
        <b-button :loading="loading" native-type="submit" tag="input" value="Login"/>
    </form>
</template>

<script>
import gql from 'graphql-tag'
import {extractErrors} from "../helpers";
import {setAuthToken} from "../auth";

export default {
    name: "Login",
    data() {
        return {
            email: null,
            password: null,
            loading: false
        }
    },
    methods: {
        login() {
            this.loading = true;
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation ($input: LoginInput){
                          login(input: $input){
                            access_token
                          }
                        }
                    `,
                    variables: {
                        input: {
                            username: this.email,
                            password: this.password
                        }
                    }
                }
            ).then(({data}) => {
                setAuthToken(data.login.access_token)
                this.$root.$emit('loggedIn')
                this.$router.push({name: 'team-fight-dashboard'})
            }).catch(({graphQLErrors}) => {
                this.$buefy.snackbar.open(
                    {
                        duration: 6000,
                        type: 'is-danger',
                        message: `Forkert brugernavn eller adgangskode.`
                    })
            }).finally(() => {
                this.loading = false
            })
        }
    }
}
</script>

<style scoped>

</style>
