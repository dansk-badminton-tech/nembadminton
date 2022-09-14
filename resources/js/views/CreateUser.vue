<template>
    <div class="mb-2">
        <b-field label="Navn">
            <b-input v-model="name" icon="user-alt" placeholder="Dit navn"></b-input>
        </b-field>
        <b-field label="Email">
            <b-input v-model="email" icon="envelope" placeholder="din@email.dk" type="email"></b-input>
        </b-field>
        <b-field label="Badminton Player Klub">
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
        <article class="message is-danger">
            <div class="message-body">Værktøjet er udviklet som et personligt bidrag til badminton sporten i Danmark. Badminton Danmark har ikke valideret og kontrolleret beregningerne.
                Alle udregninger er lavet på baggrund af reglementet for DH turneringen.
                Det er altid jer som klub og brugere, der har ansvaret for at opstillingerne er korrekte og I har selv ansvaret for at kontrollere i forhold til ranglister på badmintonplayer og DH reglementet.</div>
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
