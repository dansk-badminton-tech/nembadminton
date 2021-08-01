<template>
    <b-table
        :checked-rows.sync="teams"
        :columns="columns"
        :data="badmintonPlayerTeams"
        :loading="$apollo.queries.badmintonPlayerTeams.loading"
        checkable>

        <template #empty>
            <div class="has-text-centered">Ingen hold fundet. Har du valgt den rigtige sæson og klub?</div>
        </template>
    </b-table>
</template>

<script>
import gql from "graphql-tag"

export default {
    name: "BadmintonPlayerTeamsMultiSelect",
    props: ['value', 'clubId', 'season'],
    watch: {
        teams(newValue, oldValue) {
            this.$emit('input', newValue)
        }
    },
    data() {
        return {
            columns: [
                {
                    field: 'name',
                    label: 'Navn',
                },
                {
                    field: 'league',
                    label: 'Række',
                }
            ],
            teams: []
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
            result(ApolloQueryResult, key) {
                this.teams = [];
            },
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
