<template>
    <div class="mb-2">
        <b-field label="Email">
            <b-input v-model="email" icon="envelope" placeholder="info@nembadminton.dk" type="email"></b-input>
        </b-field>
        <b-message
            v-if="showSuccess"
            title="Modtaget"
            type="is-success"
            aria-close-label="Luk">
            Vi har sendt dig en email!
        </b-message>
        <b-button :loading="loading" @click="resetPassword">Send email</b-button>
    </div>
</template>

<script>
import gql from "graphql-tag";
import {extractErrors} from "../../helpers";

export default {
    name: "ForgotPassword",
    data() {
        return {
            email: '',
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
                        mutation ($input: ForgotPasswordInput!){
                            forgotPassword(input: $input){
                                status
                                message
                            }
                        }
                    `,
                    variables: {
                        input: {
                            email: this.email
                        }
                    }
                }
            ).then(({data}) => {
                this.showSuccess = true;
                console.log(data)
            }).catch(({graphQLErrors}) => {
                let errors = extractErrors(graphQLErrors)
                this.$buefy.snackbar.open(
                    {
                        duration: 6000,
                        type: 'is-danger',
                        message: `Kunne ikke finde din email :( <br />` + errors.join('<br />')
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
