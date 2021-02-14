<template>
    <form @submit.prevent>
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
    </form>
</template>
<script>
export default {
    name: 'ChangePassword',
    data: () => {
        return {
            password: '',
            password_confirmation: '',
            old_password: '',
            updatingPassword: false,
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
    }
}
</script>
