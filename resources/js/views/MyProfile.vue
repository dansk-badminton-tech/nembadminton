<template>
    <div>
        <b-field label="Navn">
            <b-input v-model="me.name"></b-input>
        </b-field>
        <b-field label="Email">
            <b-input v-model="me.email" type="email"></b-input>
        </b-field>
        <b-button @click="update">Gem</b-button>
        <div class="mt-4"></div>
        <b-field label="Nuværende Adgangskode">
            <b-input v-model="old_password" type="password"></b-input>
        </b-field>
        <b-field label="Ny Adgangskode">
            <b-input v-model="password" type="password"></b-input>
        </b-field>
        <b-field label="Gentag ny adgangskode">
            <b-input v-model="password_confirmation" type="password"></b-input>
        </b-field>
        <b-button @click="updatePassword">Skift adgangskode</b-button>
    </div>
</template>

<script>
import gql from 'graphql-tag'

export default {
    name: "MyProfile",
    data() {
        return {
            me: {
                name: '',
                email: '',
            },
            password: '',
            password_confirmation: '',
            old_password: ''
        }
    },
    apollo: {
        me: gql`
            query{
                me{
                    id
                    email
                    name
                }
            }
        `
    },
    methods: {
        updatePassword() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation updatePassword($input: UpdatePassword!){
                            updatePassword(input: $input){
                                status
                                message
                            }
                        }
                    `,
                    variables: {
                        input: {
                            old_password: this.old_password,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        }
                    }
                }
            ).then(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 2000,
                        type: 'is-sucess',
                        message: `Din adgangskode er nu opdateret`
                    })
            })
        },
        update() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation updatePassword($input: UpdateMe!){
                            updateMe(input: $input){
                                name
                                email
                            }
                        }
                    `,
                    variables: {
                        input: {
                            name: this.me.name,
                            email: this.me.email
                        }
                    }
                }
            ).then(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 2000,
                        type: 'is-sucess',
                        message: `Din profil er nu opdateret`
                    })
            })
        }
    }
}
</script>

<style scoped>

</style>
