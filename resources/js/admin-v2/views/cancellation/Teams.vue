<template>
    <b-field label="Holdene" message="Vælg de hold som du vil melde afbud til">
        <b-field v-for="team in collapsedTeams" :key="team.name">
            <b-checkbox size="is-medium" v-model="selectedTeams"
                        :native-value="team">
                {{ team.name }} - {{ team.league }}
            </b-checkbox>
        </b-field>
    </b-field>
</template>
<script>
import _ from "lodash/fp.js";
import gql from "graphql-tag";
import {getCurrentSeason} from "@/helpers.js";

export default {
    name: 'teams',
    props: {
        value: {
            type: Array,
            default() {
                return []
            }
        },
        clubs: {
            type: Array
        }
    },

    data() {
        return {
            selectedTeams: []
        };
    },
    watch: {
        selectedTeams(newVal){
            this.$emit('input', newVal)
        }
    },
    computed: {
        collapsedTeams() {
            return _.uniqBy('name', this.badmintonPlayerTeamsBulk)
        },
    },
    apollo: {
        badmintonPlayerTeamsBulk: {
            query: gql`
                query badmintonPlayerTeamsBulk($input: [BadmintonPlayerTeamsInput!]!){
                                badmintonPlayerTeamsBulk(input: $input){
                                    name
                                    ageGroupId
                                    league
                                    leagueGroupId
                                    clubId
                                }
            }`,
            variables() {
                return {
                    input: this.clubs.map(c => ({
                        season: getCurrentSeason(),
                        clubId: parseInt(c.id) // Change me
                    }))
                }
            },
            skip() {
                return this.club === null
            },
            error(err) {
                this.$buefy.toast.open({message: `Error fetching teams`, type: "is-danger", duration: 5000});
            }
        }
    }
}
</script>
