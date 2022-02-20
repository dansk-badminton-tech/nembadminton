<template>
    <div>
        <b-field v-for="team in badmintonPlayerApiTeams" :key="team.divisionName"
                 :label="team.divisionName">
            <h1>{{ team.divisionName }}</h1>
            <b-select placeholder="Kampe">
                <option v-for="lineup in team.lineups">
                    {{lineup.match.}}
                </option>
            </b-select>
            <h2 v-for="lineup in team.lineups">{{lineup.match.divisionName}} - {{lineup.match.groupName}}</h2>
            <!--        <BadmintonPlayerTeamFights v-model="selectedTeamMatches[index]" :clubId="clubId"-->
            <!--                                   :player-team="team" :season="season"/>-->
        </b-field>
    </div>
</template>

<script>
import gql from "graphql-tag";

export default {
    name: "Teams",
    props: ['clubId'],
    apollo: {
        badmintonPlayerApiTeams: {
            query: gql`
                query badmintonPlayerApiTeams($input: BadmintonPlayerApiTeamsInput){
                    badmintonPlayerApiTeams(input: $input){
                        divisionName
                        lineups {
                            match {
                                divisionName
                                groupName
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
