<template>
    <div class="columns">
        <div class="column">
            <b-field label="Navn">
                <b-input v-model="name" placeholder="fx. Runde 1"></b-input>
            </b-field>
            <b-field label="Spille dato">
                <b-datepicker
                    v-model="gameDate"
                    icon="calendar-alt"
                    placeholder="Klik for at vælge dato..."
                    trap-focus>
                </b-datepicker>
            </b-field>
            <ClubSearch :select-club="selectClub"></ClubSearch>
            <b-button class="mt-2" icon-left="save" @click="createTeam">Opret</b-button>
        </div>
    </div>
</template>

<script>
import gql from 'graphql-tag'
import ClubSearch from "../components/search-club/ClubSearch";

export default {
    name: "TeamFightCreate",
    components: {ClubSearch},
    data() {
        return {
            name: null,
            gameDate: null,
            clubId: null
        }
    },
    methods: {
        selectClub(id) {
            this.clubId = id
        },
        createTeam() {
            const createTeamGQL = gql`
                        mutation ($input: CreateTeamInput){
                          createTeam(input: $input){
                            id
                            name
                            gameDate
                          }
                        }
                    `;
            this.$apollo
                .mutate(
                    {
                        mutation: createTeamGQL,
                        variables: {
                            input: {
                                name: this.name,
                                gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
                                club: {
                                    connect: this.clubId
                                }
                            }
                        }
                    })
                .then(({data}) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-success',
                            message: `Dit hold er gemt`
                        })
                    this.$router.push({name: 'team-fight-edit', params: {teamUUID: data.createTeam.id}})
                })
        }
    }
}
</script>

<style scoped>

</style>
