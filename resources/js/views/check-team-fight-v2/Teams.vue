<template>
    <div>
        <b-field v-for="(team, index) in badmintonPlayerApiTeamMatches" :key="team.divisionName"
                 :label="team.divisionName">
            <b-select v-model="value[index]" placeholder="Kampe" expanded>
                <option :value="lineup" v-for="(lineup, index) in team.lineups">
                    {{index+1}} {{lineup.match.matchTime}} - {{lineup.match.teamName1}} - {{lineup.match.teamName2}}
                </option>
            </b-select>
            <!--        <BadmintonPlayerTeamFights v-model="selectedTeamMatches[index]" :clubId="clubId"-->
            <!--                                   :player-team="team" :season="season"/>-->
        </b-field>
    </div>
</template>

<script>
import gql from "graphql-tag";

export default {
    name: "Teams",
    props: {
        clubId: Number,
        value: Array
    },
    apollo: {
        badmintonPlayerApiTeamMatches: {
            query: gql`
                query badmintonPlayerApiTeamMatches($input: BadmintonPlayerApiTeamsInput){
                    badmintonPlayerApiTeamMatches(input: $input){
                        divisionName
                        lineups {
                            match {
                                leagueMatchId
                                divisionName
                                groupName
                                teamName1
                                teamName2
                                matchTime
                            }
                        }
                    }
                }
            `,
            variables() {
                return {
                    input: {
                        clubId: this.clubId
                    }
                }
            },
            skip(){
                return this.clubId === null
            }
        }
    }
}
</script>

<style scoped>

</style>
