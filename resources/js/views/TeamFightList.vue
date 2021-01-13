<template>
    <fragment>
        <b-button :to="{name: 'team-fight-create'}" icon-left="save" tag="router-link">Opret holdkamp</b-button>
        <b-table :data="teams.data" :loading="$apollo.loading">
            <b-table-column v-slot="props" field="id" label="Navn">
                <router-link v-bind:to="'/team-fight/'+props.row.id+'/edit'">{{ props.row.name }}</router-link>
            </b-table-column>
            <b-table-column v-slot="props" field="gameDate" label="Spille Dato">
                {{ props.row.gameDate }}
            </b-table-column>
            <b-table-column v-slot="props" field="updatedAt" label="Opdateret">
                {{ props.row.updatedAt }}
            </b-table-column>
            <b-table-column v-slot="props" field="createdAt" label="Oprettet">
                {{ props.row.createdAt }}
            </b-table-column>
            <b-table-column v-slot="props" label="Funktioner">
                <b-button size="is-small"
                          tag="router-link"
                          type="is-link"
                          v-bind:to="'/team-fight/'+props.row.id+'/edit'">Rediger
                </b-button>
            </b-table-column>
        </b-table>
        <CreateTeamFightAction v-if="!$apollo.loading && teams.data.length === 0"></CreateTeamFightAction>
    </fragment>
</template>

<script>
import CreateTeamFightAction from "../components/team-fight/CreateTeamFightAction";
import gql from "graphql-tag"

export default {
    name: "TeamFightList",
    components: {CreateTeamFightAction},
    data() {
        return {
            teams: []
        }
    },
    apollo: {
        teams: {
            query: gql`
                query {
                    teams(order: {column: GAME_DATE, order: DESC}, first: 20){
                        data{
                            id,
                            name,
                            gameDate,
                            createdAt,
                            updatedAt
                        }
                    }
                }
            `,
            fetchPolicy: 'network-only'
        }
    }
}
</script>

<style scoped>

</style>
