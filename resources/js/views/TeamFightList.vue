<template>
    <div>
        <h1 class="title">Holdkampe</h1>
        <b-button v-if="!$apollo.loading && teams.data.length !== 0" :to="{name: 'team-fight-create'}" icon-left="save" tag="router-link">Opret holdkamp</b-button>
        <ListTeamFights v-if="!$apollo.loading && teams.data.length !== 0" :loading="$apollo.loading" :teams="teams.data"/>
        <CreateTeamFightAction v-if="!$apollo.loading && teams.data.length === 0"></CreateTeamFightAction>
    </div>
</template>

<script>
import CreateTeamFightAction from "../components/team-fight/CreateTeamFightAction";
import gql from "graphql-tag"
import ListTeamFights from "../components/team-fight/ListTeamFights";
import UpdateYourProfileAction from "./UpdateYourProfileAction";

export default {
    name: "TeamFightList",
    components: {UpdateYourProfileAction, ListTeamFights, CreateTeamFightAction},
    data() {
        return {
            teams: [],
            teamsByBadmintonId: []
        }
    },
    apollo: {
        teamsByBadmintonId: {
            query: gql`
                query {
                    teamsByBadmintonId{
                        id,
                        name,
                        gameDate,
                        createdAt,
                        updatedAt
                    }
                }
            `,
            fetchPolicy: 'network-only'
        },
        teams: {
            query: gql`
                query {
                    teams(order: {column: GAME_DATE, order: DESC}, first: 20){
                        data{
                            id,
                            name,
                            version,
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

