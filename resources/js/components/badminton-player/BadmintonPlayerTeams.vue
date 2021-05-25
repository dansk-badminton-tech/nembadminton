<template>
    <b-select :loading="$apollo.queries.badmintonPlayerTeams.loading" expanded placeholder="Vælge hold" @input="handleInput">
        <option
            v-for="option in badmintonPlayerTeams"
            :key="option.leagueGroupID"
            :value="option">
            {{ option.name }} - {{ option.league }}
        </option>
    </b-select>
</template>

<script>
import gql from "graphql-tag"

export default {
    name: "BadmintonPlayerTeams",
    props: ['value', 'clubId', 'season'],
    methods: {
        handleInput(value) {
            this.$emit('input', value)
        }
    },
    apollo: {
        badmintonPlayerTeams: {
            query: gql`
                query($input: BadmintonPlayerTeamsInput){
                    badmintonPlayerTeams(input: $input){
                        leagueGroupId
                        ageGroupId
                        name
                        league
                    }
                }
            `,
            variables() {
                return {
                    input: {
                        clubId: this.clubId,
                        season: this.season
                    }
                }
            },
            skip() {
                return this.clubId === null || this.season === null
            }
        },
    }
}
</script>

<style scoped>

</style>
