<script>
import gql from "graphql-tag";
import _ from "lodash/fp.js";
import TeamFights from "@/views/cancellation/TeamFights.vue";
import {getCurrentSeason} from "@/helpers.js";
import Form from "@/components/Kustomer/Partials/Form.vue";
import Teams from "@/views/cancellation/Teams.vue";
import MemberSearchCancellation from "./MemberSearchCancellation.vue";

export default {
    name: "CancellationPublic",
    props: {"sharingId": String},
    components: {Teams, Form, TeamFights, MemberSearchCancellation},
    data() {
        return {
            form: {
                selectedTeamFights: [],
                selectedPlayer: {},
                email: ''
            },
            selectedTeams: [],
            badmintonPlayerTeamFightsBulk: [],
            cancellationCollectorPublic: {
                clubs: []
            }
        }
    },
    apollo: {
        cancellationCollectorPublic: {
            query: gql`
                query cancellationCollectorPublic($sharingId: String!){
                    cancellationCollectorPublic(sharingId: $sharingId){
                        id
                        clubs {
                            id
                        }
                    }
                }
                `,
            variables() {
                return {
                    sharingId: this.sharingId
                }
            },
            error(err) {
                this.$buefy.toast.open({message: `Error fetching cancellation collector`, type: "is-danger", duration: 5000});
            }
        },
        badmintonPlayerTeamFightsBulk: {
            query: gql`
                query badmintonPlayerTeamFightsBulk($input: [BadmintonPlayerTeamFightsInput]){
                                badmintonPlayerTeamFightsBulk(input: $input){
                                    gameTime
                                    matchId
                                    round
                                    roundDate
                                    teams
                                }
            }`,
            variables() {
                return {
                    input: this.selectedTeams.map(t => ({
                        season: getCurrentSeason(),
                        clubId: t.clubId,
                        ageGroupId: Number(t.ageGroupId),
                        leagueGroupId: Number(t.leagueGroupId),
                        clubName: t.name
                    }))
                }
            },
            error(err) {
                this.$buefy.toast.open({message: `Error fetching teams rounds`, type: "is-danger", duration: 5000});
            }
        }
    },
    methods: {
        createCancellation(){
            this.$apollo.mutate({
                mutation: gql`
                    mutation createCancellationViaCollector($sharingId: String!, $input: CancellationViaCollectorInput!){
                        createCancellationViaCollector(sharingId: $sharingId, input: $input){
                            success
                        }
                    }
                `,
                variables: {
                    sharingId: this.sharingId,
                    input: {
                        name: this.form.name,
                        email: this.form.email,
                        teamFights: this.form.selectedTeamFights.map(tf => ({
                            gameTime: tf.gameTime,
                            matchId: tf.matchId,
                            round: tf.round,
                            roundDate: tf.roundDate,
                            teams: tf.teams
                        }))
                    }
                }
                                })
                .then((data) => {
                    console.log(data)
            })
        }
    }
}

</script>

<template>
    <section>
        <form @submit.prevent="createCancellation">
            <b-loading v-model="$apollo.queries.badmintonPlayerTeamFightsBulk.loading"></b-loading>
            <b-field label="Dit navn">
                <MemberSearchCancellation v-model="form.selectedPlayer" :clubs="cancellationCollectorPublic.clubs"></MemberSearchCancellation>
            </b-field>
            <b-field label="Dit email" message="Bruges til at sende en kvittering">
                <b-input type="email" v-model="form.email" required/>
            </b-field>
            <teams :clubs="cancellationCollectorPublic.clubs" v-model="selectedTeams"/>
            <hr>
            {{selectedTeams}}
            <strong>Vælg hvilke(n) holdkamp(e) du vil melde afbud til</strong>
            <p v-if="selectedTeams.length === 0">Ingen hold valgt</p>
            <div class="columns is-multiline mt-2">
                <TeamFights v-model="form.selectedTeamFights" :data="badmintonPlayerTeamFightsBulk"/>
            </div>
            <b-button native-type="submit" expanded size="is-medium">Meld afbud</b-button>
            {{ form }}
        </form>
    </section>
</template>
