<template>
    <div>
        <b-field label="Navn">
            <b-input v-model="me.name"></b-input>
        </b-field>
        <b-field label="Email">
            <b-input v-model="me.email" type="email"></b-input>
        </b-field>
        <b-button :loading="updatingProfile" @click="update">Gem</b-button>
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
        <b-button :loading="updatingPassword" @click="updatePassword">Skift adgangskode</b-button>
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
            old_password: '',
            updatingPassword: false,
            updatingProfile: false
        }
    },
    apollo: {
        me: {
            query: gql`
                query{
                    me{
                        id
                        email
                        name
                    }
                }`,
            fetchPolicy: "network-only"
        }
    },
    methods: {
        updatePassword() {
            this.updatingPassword = true
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
                        type: 'is-success',
                        message: `Din adgangskode er nu opdateret`
                    })
            }).finally(() => {
                this.updatingPassword = false
            })
        },
        update() {
            this.updatingProfile = true
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation updateMe($input: UpdateMe!){
                            updateMe(input: $input){
                                id
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
                this.$root.$emit('userUpdated')
                this.$buefy.snackbar.open(
                    {
                        duration: 2000,
                        type: 'is-success',
                        message: `Din profil er nu opdateret`
                    })
            }).finally(() => {
                this.updatingProfile = false
            })
        }
    }
}
</script>

<style scoped>

</style>
