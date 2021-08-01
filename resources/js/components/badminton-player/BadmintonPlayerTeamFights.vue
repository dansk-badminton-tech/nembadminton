<template>
    <b-select :loading="$apollo.queries.badmintonPlayerTeamFights.loading" expanded placeholder="Vælge kamp" @input="handleInput">
        <option
            v-for="option in badmintonPlayerTeamFights"
            :key="option.matchId"
            :value="option">
            {{ option.gameTime }} - {{ option.teams.join(' - ') }}
        </option>
    </b-select>
</template>

<script>
import gql from "graphql-tag"

export default {
    name: "BadmintonPlayerTeamFights",
    props: ['value', 'clubId', 'season', 'playerTeam'],
    methods: {
        handleInput(value) {
            this.$emit('input', value, this.playerTeam)
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
                    }
                }
            `,
            variables() {
                return {
                    input: {
                        clubId: this.clubId,
                        season: this.season,
                        ageGroupId: this.playerTeam.ageGroupId,
                        leagueGroupId: this.playerTeam.leagueGroupId,
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
