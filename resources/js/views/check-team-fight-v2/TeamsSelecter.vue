<template>
    <b-table
        :checked-rows.sync="selectedTeams"
        :columns="columns"
        :data="badmintonPlayerApiTeamMatches"
        :loading="$apollo.queries.badmintonPlayerApiTeamMatches.loading"
        checkable>
        <template #empty>
            <div class="has-text-centered">Ingen hold fundet. Understøtter kun fra Danmarks serien og op</div>
        </template>
    </b-table>
</template>
<script>
import gql from "graphql-tag";

export default {
    name: 'TeamsSelector',
    props: {
        clubId: Number,
        value: Array
    },
    watch: {
        selectedTeams(newValue) {
            this.$emit('input', newValue)
        }
    },
    data() {
        return {
            selectedTeams: [],
            columns: [
                {
                    field: 'divisionName'
                }
            ]
        }
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
            skip() {
                return this.clubId === null
            }
        }
    }

}
</script>
