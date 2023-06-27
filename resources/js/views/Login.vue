<template>
    <form class="mt-2" @submit.prevent="login">
        <b-field label="Email">
            <b-input v-model="email" type="email"></b-input>
        </b-field>
        <b-field label="Adgangskode">
            <b-input v-model="password" type="password"></b-input>
        </b-field>
            <router-link class="is-clearfix" to="/forgot-password">Glemt adgangskode?</router-link>
        <b-button class="mt-2" :loading="loading" native-type="submit">Login</b-button>
    </form>
</template>

<script>
import gql from 'graphql-tag'
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
    props: {
        afterLogin: Function
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
                if (this.afterLogin instanceof Function) {
                    this.afterLogin()
                } else {
                    this.$router.push({name: 'my-club'})
                }
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
