<template>
    <fragment v-if="!$apollo.loading">
        <h1 class="title">{{ team.name }} - {{ team.club.name1 }}</h1>
        <h2 class="subtitle">Spille dato: {{ team.gameDate }}</h2>
        <div class="columns is-multiline">
            <div v-for="(squad, index) in team.squads" :key="squad.id" class="column is-half">
                <table class="table is-striped mt-5 is-fullwidth">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <h2 class="is-pulled-left">Hold {{ index + 1 }}</h2>
                            <b-taglist class="ml-2 is-pulled-left">
                                <b-tag>{{ squad.league }}</b-tag>
                            </b-taglist>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="category in squad.categories" :key="category.name">
                        <th>{{ category.name }}</th>
                        <td>
                            <div v-for="player in category.players" class="is-clearfix mt-1">

                                <p class="fa-pull-left">
                                    <b-icon
                                        v-show="player.gender === 'M'"
                                        icon="mars"
                                        size="is-small">
                                    </b-icon>
                                    <b-icon
                                        v-show="player.gender === 'K'"
                                        icon="venus"
                                        size="is-small">
                                    </b-icon>
                                    {{ player.name }}
                                    ({{
                                        findPositions(player, 'N') + ' ' + findPositions(player, category.category)
                                    }})
                                </p>
                                <b-tag v-if="isYoungPlayer(player, null)">U17/U19</b-tag>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </fragment>
</template>

<script>
import gql from 'graphql-tag'
import CreateNotification from "../components/team-fight/CreateNotification";
import {findPositions, isYoungPlayer} from "../helpers";

export default {
    name: "TeamFightPublic",
    components: {
        CreateNotification
    },
    methods: {
        isYoungPlayer,
        findPositions,
        sendNotification() {
            this.loading = true
            this.$apollo.mutate({
                mutation: gql`
                                    mutation{
                                        sendNotification
                                    }
                                `
            })
        }
    },
    data() {
        return {
            searchPlayer: '',
            showNotificationPopUp: false
        }
    },
    props: {
        teamId: String,
    },
    apollo: {
        team: {
            query: gql` query ($id: ID!){
                  team(id: $id){
                    id
                    name
                    gameDate
                    squads{
                        id
                        playerLimit
                        league
                        categories{
                            id
                            category
                            name
                            players{
                                id
                                gender
                                name
                                refId
                                points{
                                    category
                                    points
                                    position
                                    vintage
                                }
                            }
                        }
                    }
                    club {
                        id
                        name1
                    }
                  }
                }`,
            fetchPolicy: "network-only",
            variables: function () {
                return {
                    id: this.teamId
                }
            }
        }
    }
}
</script>

<style scoped>

</style>
