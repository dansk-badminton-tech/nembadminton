<template>
    <div>
        <slot :loading="$apollo.queries.badmintonPlayerTeamFights.loading" :teamFights="badmintonPlayerTeamFights" :playerTeam="playerTeam">
            <b-select :loading="$apollo.queries.badmintonPlayerTeamFights.loading" expanded placeholder="Vælge kamp" @input="handleInput">
                <option
                    v-for="option in badmintonPlayerTeamFights"
                    :key="option.matchId"
                    :value="option">
                    {{option.round}} - {{ option.gameTime }} - {{ option.teams.join(' - ') }}
                </option>
            </b-select>
        </slot>
    </div>
</template>

<script>
import gql from "graphql-tag"

export default {
    name: "BadmintonPlayerTeamFights",
    props: ['value', 'clubId', 'season', 'playerTeam'],
    methods: {
        handleInput(value) {
            this.$emit('input', {
                           teamMatch: value,
                           team: this.playerTeam
                       }
            )
        }
    },
    apollo: {
        badmintonPlayerTeamFights: {
            query: gql`
                query($input: BadmintonPlayerTeamFightsInput){
                    badmintonPlayerTeamFights(input: $input){
                        teams
                        matchId
                        gameTime
                        round
                        roundDate
                    }
                }
            `,
            variables() {
                return {
                    input: {
                        clubId: this.clubId,
                        season: parseInt(this.season),
                        ageGroupId: parseInt(this.playerTeam.ageGroupId),
                        leagueGroupId: parseInt(this.playerTeam.leagueGroupId),
                        clubName: this.playerTeam.name
                    }
                }
            },
            skip() {
                return this.playerTeam === null
            }
        }
    }
}
</script>

<style scoped>

</style>
