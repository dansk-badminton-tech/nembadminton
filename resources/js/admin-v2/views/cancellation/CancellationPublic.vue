<script>
import gql from "graphql-tag";
import _ from "lodash/fp.js";
import TeamFights from "@/views/cancellation/TeamFights.vue";

export default {
    name: "CancellationPublic",
    props: {"sharingId": String},
    components: {TeamFights},
    computed: {
        collapsedTeams() {
            return _.uniqBy('name', this.badmintonPlayerTeams)
        },
    },
    data(){
        return {
            selectedTeams: [],
            badmintonPlayerTeamFightsBulk: [],
            selectedTeamFights: []
        }
    },
    apollo: {
        badmintonPlayerTeams: {
            query: gql`
                query badmintonPlayerTeams($input: BadmintonPlayerTeamsInput){
                                badmintonPlayerTeams(input: $input){
                                    name
                                    ageGroupId
                                    league
                                    leagueGroupId
                                }
            }`,
            variables(){
                return {
                    input: {
                        season: 2024,
                        clubId: 1124
                    }
                }
            },
            error(err){
                this.$buefy.toast.open({message: `Error fetching teams`, type: "is-danger", duration: 5000});
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
            variables(){
                return {
                    input: this.selectedTeams.map(t => ({
                        season: 2024,
                        clubId: 1124,
                        ageGroupId: Number(t.ageGroupId),
                        leagueGroupId: Number(t.leagueGroupId),
                        clubName: t.name
                    }))
                }
            },
            error(err){
                this.$buefy.toast.open({message:`Error fetching teams rounds`, type: "is-danger", duration: 5000});
            }
        }
    }
}

</script>

<template>
    <section>
            <b-loading v-model="$apollo.queries.badmintonPlayerTeamFightsBulk.loading"></b-loading>
            <b-field label="Holdene" message="Vælg de hold som du vil melde afbud til">
                <b-checkbox size="is-medium" v-for="team in collapsedTeams" :key="team.name" v-model="selectedTeams"
                            :native-value="team">
                    {{team.name}} - {{team.league}}
                </b-checkbox>
            </b-field>
        <hr>
            <strong>Vælg hvilke(n) holdkamp(e) du vil melde afbud til</strong>
            <div class="columns is-multiline mt-2">
                <TeamFights v-model="selectedTeamFights" :data="badmintonPlayerTeamFightsBulk"/>
            </div>
            {{selectedTeamFights}}
            <b-button type="is-info" expanded size="is-medium" class="is-pulled-right">Meld afbud</b-button>
    </section>
</template>

