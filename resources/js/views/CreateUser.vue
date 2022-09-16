<template>
    <div class="mb-2">
        <b-field label="Navn">
            <b-input v-model="name" icon="user-alt" placeholder="Dit navn"></b-input>
        </b-field>
        <b-field label="Email">
            <b-input v-model="email" icon="envelope" placeholder="din@email.dk" type="email"></b-input>
        </b-field>
        <b-field label="Badmintonklub">
            <BadmintonPlayerClubs v-model="clubId"></BadmintonPlayerClubs>
        </b-field>
<!--        <b-field label="Badminton Player ID (Valgfrit)">-->
<!--            <b-input v-model="playerId" icon="user-alt" placeholder="900910-17" type="text"></b-input>-->
<!--        </b-field>-->
<!--        <a class="is-clearfix" href="https://www.badmintonplayer.dk/DBF/Ranglister/" target="_blank">Find dit Badminton ID på ranglisten</a>-->
        <b-field label="Adgangskode">
            <b-input v-model="password" icon="lock" placeholder="********" type="password"></b-input>
        </b-field>
        <b-field label="Gentag adgangskode">
            <b-input v-model="password_confirmation" icon="lock" placeholder="********" type="password"></b-input>
        </b-field>
        <label class="label">Vigtig info</label>
        <article class="message is-warning-passive">
            <div class="message-body">Værktøjet nembadminton.dk er udviklet som et bidrag til badmintonsporten i Danmark for at gøre det nemmere at være træner og frivillig. Badminton Danmark har hverken valideret eller kontrolleret beregningerne.
                Alle udregninger er lavet på baggrund af reglementet for <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer">DH turneringen</a>. Det er altid jer som klub og bruger, der har ansvaret for, at opstillingerne er korrekte og I har selv ansvaret for at kontrollere i forhold til ranglister på badmintonplayer og DH reglementet.
                Værktøjet hjælper kun med opstillinger internt og mellem hold i forhold til ranglistepoint, hvis spillere f.eks. har karantæne ses det ikke her.</div>
        </article>
        <b-field>
            <b-checkbox v-model="accepted">Jeg har læst og forstået overstående</b-checkbox>
        </b-field>
        <b-button :disabled="!accepted" :loading="loading" @click="create">Opret</b-button>
    </div>
</template>

<script>
import gql from "graphql-tag"
import {extractErrors} from "../helpers";
import {setAuthToken} from "../auth";
import ClubSearch from "../components/search-club/ClubSearch";
import BadmintonPlayerClubs from "../components/badminton-player/BadmintonPlayerClubs";

export default {
    name: "CreateUser",
    components: {BadmintonPlayerClubs},
    props: {
        afterRegister: Function
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
                            organization_id: this.clubId,
                            player_id: this.playerId,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        }
                    }
                })
                .then(({data}) => {
                    setAuthToken(data.register.tokens.access_token)
                    this.$root.$emit('loggedIn')
                    if (this.afterRegister instanceof Function) {
                        this.afterRegister()
                    } else {
                        this.$router.push({name: 'onboarding'})
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
