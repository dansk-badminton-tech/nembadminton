<template>
    <div>
        <title-bar :title-stack="titleStack" />
        <hero-bar :has-right-visible="false">
            Holdkamp
        </hero-bar>
        <section class="section is-main-section">
        <b-button v-if="!$apollo.loading && teams.data.length !== 0" :to="{name: 'team-fight-create'}" icon-left="save" tag="router-link">Opret holdkamp</b-button>
        <ListTeamFights v-if="!$apollo.loading && teams.data.length !== 0" :loading="$apollo.loading" :teams="teams.data"/>
        <CreateTeamFightAction v-if="!$apollo.loading && teams.data.length === 0"></CreateTeamFightAction>
        </section>
    </div>
</template>

<script>
import gql from "graphql-tag"
import ListTeamFights from "./ListTeamFights.vue";
import CreateTeamFightAction from "./CreateTeamFightAction.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";

export default {
    name: "TeamFightList",
    components: {HeroBar, TitleBar, ListTeamFights, CreateTeamFightAction},
    data() {
        return {
            titleStack: ['Admin', 'Holdkamp'],
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

