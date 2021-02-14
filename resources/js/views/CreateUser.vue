<template>
    <div class="mb-2">
        <b-field label="Navn">
            <b-input v-model="name" icon="user-alt" placeholder="Viktor Axelsen"></b-input>
        </b-field>
        <b-field label="Email">
            <b-input v-model="email" icon="envelope" placeholder="viktor@gmail.com" type="email"></b-input>
        </b-field>
        <ClubSearch :select-club="selectClub"></ClubSearch>
        <b-field label="Badminton Player ID">
            <b-input v-model="playerId" icon="user-alt" placeholder="100990-12" type="text"></b-input>
        </b-field>
        <b-field label="Adgangskode">
            <b-input v-model="password" icon="lock" placeholder="******" type="password"></b-input>
        </b-field>
        <b-field label="Gentag adgangskode">
            <b-input v-model="password_confirmation" icon="lock" placeholder="******" type="password"></b-input>
        </b-field>
        <b-button :loading="loading" @click="create">Opret</b-button>
    </div>
</template>

<script>
import gql from "graphql-tag"
import {extractErrors} from "../helpers";
import {setAuthToken} from "../auth";
import ClubSearch from "../components/search-club/ClubSearch";

export default {
    name: "CreateUser",
    components: {ClubSearch},
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
            playerId: null
        }
    },
    methods: {
        selectClub(clubId) {
            this.clubId = clubId
        },
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
                        this.$router.push({name: 'home'})
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
