<template>
    <div class="columns">
        <div v-if="!$apollo.queries.me.loading" class="column">
            <b-field label="Navn">
                <b-input v-model="name" placeholder="fx. Runde 1"></b-input>
            </b-field>
            <b-field label="Spille dato">
                <b-datepicker
                    v-model="gameDate"
                    icon="calendar-alt"
                    placeholder="Klik for at vælge dato..."
                    locale="da-DK"
                    :first-day-of-week="1"
                    trap-focus>
                </b-datepicker>
            </b-field>
            <b-field label="Rangliste">
                <RankingVersionSelect v-model="version" :playing-date="gameDate" expanded/>
            </b-field>
            <b-field label="Klub">
                <b-input v-model="me.club.name1" disabled="true"></b-input>
            </b-field>
            <b-button class="mt-2" icon-left="save" :loading="loading" @click="createTeam">Opret</b-button>
        </div>
    </div>
</template>

<script>
import gql from 'graphql-tag'
import ME from "../queries/me.gql";
import RankingListDatePicker from "../components/team-fight/RankingListDatePicker";
import RankingVersionSelect from "../components/team-fight/RankingVersionSelect";

export default {
    name: "TeamFightCreate",
    components: {RankingVersionSelect, RankingListDatePicker},
    data() {
        return {
            name: null,
            gameDate: null,
            clubId: null,
            version: null,
            loading: false
        }
    },
    apollo: {
        me: {
            query: ME
        }
    },
    methods: {
        selectClub(id) {
            this.clubId = id
        },
        createTeam() {
            this.loading = true
            const createTeamGQL = gql`
                        mutation ($input: CreateTeamInput!){
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
                                version: this.version,
                                club: {
                                    connect: this.me.organization_id
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
                }).finally(() => {
                    this.loading = false
            })
        }
    }
}
</script>

<style scoped>

</style>
