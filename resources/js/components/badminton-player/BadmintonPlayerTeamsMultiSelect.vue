<template>
    <b-table
        :checked-rows.sync="teams"
        :columns="columns"
        :data="badmintonPlayerTeams"
        :loading="$apollo.queries.badmintonPlayerTeams.loading"
        checkable
        :is-row-checkable="isRowCheckable"
    >
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
    methods: {
        isRowCheckable(row) {
            return !((new RegExp('u[0-9]+', 'gmi')).test(row.league)
                   || (new RegExp('sen\\+[0-9]+', 'gmi')).test(row.league)
                    || (new RegExp('senior motion', 'gmi')).test(row.league)
                    || (new RegExp('DMU', 'gmi')).test(row.league)
                    || (new RegExp('1\\. division', 'gmi')).test(row.league)
                    || (new RegExp('liga', 'gmi')).test(row.league)
                    || (new RegExp('4 spillere', 'gmi')).test(row.league)
            )
        }
    },
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
