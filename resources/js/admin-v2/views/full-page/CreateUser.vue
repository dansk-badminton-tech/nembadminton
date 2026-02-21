<template>
    <card-component
        title="Opret bruger"
        icon="lock"
    >
    <div class="mb-2" dusk="sign-up-form">
        <b-field label="Navn">
            <b-input v-model="name" dusk="name-input" icon="account" placeholder="Dit navn"></b-input>
        </b-field>
        <b-field label="Email">
            <b-input v-model="email" dusk="email-input" icon="email" placeholder="din@email.dk" type="email"></b-input>
        </b-field>
        <b-field label="Adgangskode">
            <b-input v-model="password" dusk="password-input" icon="lock" placeholder="********" type="password"></b-input>
        </b-field>
        <b-field label="Gentag adgangskode">
            <b-input v-model="password_confirmation" dusk="password-confirmation-input" icon="lock" placeholder="********" type="password"></b-input>
        </b-field>
        <label class="label">Vigtig info</label>
        <article class="message is-warning-passive">
            <div class="message-body">Værktøjet nembadminton.dk er udviklet som et bidrag til badmintonsporten i Danmark for at gøre det nemmere at være træner og frivillig. Badminton Danmark har hverken valideret eller kontrolleret beregningerne.
                Alle udregninger er lavet på baggrund af reglementet for <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer">DH turneringen</a>. Det er altid jer som klub og bruger, der har ansvaret for, at opstillingerne er korrekte, og I har selv ansvaret for at kontrollere i forhold til ranglister på badmintonplayer og DH reglementet.
                Værktøjet hjælper kun med opstillinger internt og mellem hold i forhold til ranglistepoint. Hvis spillere f.eks. har karantæne, ses det ikke her.</div>
        </article>
        <b-field>
            <b-checkbox dusk="term-checkbox" v-model="accepted">Jeg har læst og forstået overstående</b-checkbox>
        </b-field>
        <b-field grouped>
            <b-button
                dusk="signup-button"
                class="control"
                type="is-link"
                :disabled="!accepted"
                :loading="loading"
                expanded
                @click="create">Opret</b-button>
        </b-field>
        <hr class="my-5">

            <div class="has-text-centered">
                <p class="mb-3">Har du allerede en bruger?</p>
                <b-button
                    tag="router-link"
                    to="/login"
                    type="is-link"
                    outlined
                    expanded
                >
                    <b-icon icon="login" size="is-small" class="mr-2"></b-icon>
                    Login
                </b-button>
            </div>
    </div>
    </card-component>
</template>

<script>
import gql from "graphql-tag"
import CardComponent from "../../components/CardComponent.vue";
import BadmintonPlayerClubs from "@/components/badminton-player/BadmintonPlayerClubs.vue";
import {setAuthToken} from "../../../auth";
import {extractErrors} from "@/helpers";

export default {
    name: "CreateUser",
    components: {CardComponent, BadmintonPlayerClubs},
    props: {
        afterRegister: Function,
        invitationToken: {
            type: String,
            default: null
        }
    },
    data() {
        return {
            name: null,
            email: null,
            password: null,
            password_confirmation: null,
            clubId: null,
            loading: false,
            playerId: null,
            accepted: false
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
                            player_id: this.playerId,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        }
                    }
                })
                .then(({data}) => {
                    setAuthToken(data.register.tokens.access_token)
                    if(this.invitationToken !== null){
                        this.$router.push({name: 'invitation', params: {token: this.invitationToken}})
                    }else{
                        this.$router.push({name: 'sign-up-finish'})
                    }
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
