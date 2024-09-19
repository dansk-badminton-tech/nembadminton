<template>
    <fragment v-if="!$apollo.loading">
        <h1 class="title">{{ team.name }} - {{ team.club.name1 }}
        </h1>
        <h2 class="subtitle">Spille dato: {{ team.gameDate }}</h2>
        <div class="columns is-multiline">
            <div v-for="(squad, index) in team.squads" :key="squad.id" class="column is-half">
                <table class="table is-striped mt-5 is-fullwidth">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <h2 class="is-size-4">Hold {{ index + 1 }} {{squad.name || ''}}
                                <b-button icon-right="open-in-new" v-show="squad?.externalTeamFightID" class="is-pulled-right" tag="a" target="_blank"
                                          :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+getCurrentSeason+',,,,,'+squad.externalTeamFightID+',,'"
                                          type="is-link">Se på BP
                                </b-button>
                            </h2>
                            <p>{{squad.playingDatetime}}</p>
                            <p>{{squad.playingPlace}}</p>
                            <p>{{squad.playingAddress}} {{squad.playingZipCode}} {{squad.playingCity}}</p>
<!--                            <b-taglist class="ml-2 is-pulled-left">-->
<!--                                <b-tag>{{ squad.league }}</b-tag>-->
<!--                            </b-taglist>-->
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
                                        v-show="player.gender === 'MEN'"
                                        icon="mars"
                                        size="is-small">
                                    </b-icon>
                                    <b-icon
                                        v-show="player.gender === 'WOMEN'"
                                        icon="venus"
                                        size="is-small">
                                    </b-icon>
                                    {{ player.name }}
                                    ({{
                                        findPositions(player, category.category)
                                    }})
                                </p>
                                <b-tag v-if="isYoungPlayer(player)">U17/U19</b-tag>
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
import {findPositions, getCurrentSeason, isYoungPlayer} from "../../helpers";

export default {
    name: "TeamFightPublic",
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
    computed: {
        getCurrentSeason
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
                        name
                        playingDatetime
                        playingPlace
                        playingAddress
                        playingZipCode
                        playingCity
                        externalTeamFightID
                        categories{
                            id
                            category
                            name
                            players{
                                id
                                gender
                                name
                                refId
                                vintage
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
