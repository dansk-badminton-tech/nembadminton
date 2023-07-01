<template>
    <card-component
        title="Glemt adgangskode"
        icon="lock"
    >
        <b-field label="Adgangskode">
            <b-input v-model="password" icon="lock" placeholder="********" type="password"></b-input>
        </b-field>
        <b-field label="Gentag adgangskode">
            <b-input v-model="password_confirmation" icon="lock" placeholder="********" type="password"></b-input>
        </b-field>
        <b-button :loading="loading" @click="resetPassword">Ændre adgangskode</b-button>
        <b-message
            v-if="showSuccess"
            title="Modtaget"
            type="is-success"
            class="mt-2"
            aria-close-label="Luk">
            Din adgangskode er nu ændret. <router-link class="is-clearfix" to="/login">Gå til login</router-link>
        </b-message>
    </card-component>
</template>

<script>
import gql from "graphql-tag";
import CardComponent from "../../components/CardComponent.vue";

export default {
    name: "FinishForgotPassword",
    components: {CardComponent},
    props: {
        token: String,
        email: String
    },
    data() {
        return {
            password: null,
            password_confirmation: null,
            showSuccess: false,
            loading: false
        }
    },
    methods: {
        resetPassword() {
            this.loading = true
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation ($input: NewPasswordWithCodeInput){
                            updateForgottenPassword(input: $input){
                                status
                                message
                            }
                        }
                                    `,
                    variables: {
                        input: {
                            email: this.email,
                            token: this.token,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        }
                    }
                }
            ).then(() => {
                this.showSuccess = true
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 6000,
                        type: 'is-danger',
                        message: 'Kunne ikke reset din adgangskode'
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
