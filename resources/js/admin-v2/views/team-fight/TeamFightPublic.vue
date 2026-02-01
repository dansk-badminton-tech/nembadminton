<template>
    <fragment v-if="!$apollo.loading">
        <div v-if="team">
            <h1 class="title">{{ team.name }} - {{ team.clubhouse.name }}
            </h1>
            <h2 class="subtitle">Spilledato: {{ team.gameDate }}</h2>
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
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="category in squad.categories" :key="category.name">
                            <th>{{ category.name }}</th>
                            <td>
                                <div v-for="player in category.players" class="mt-1">
                                    <p>
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
                                        <b-tag v-if="isYoungPlayer(player)">{{ageGroupLabel(player)}}</b-tag>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <section v-else class="section has-text-centered">
            <h1 class="title">Kamp ikke fundet</h1>
            <p class="subtitle">Vi kunne ikke finde den ønskede kamp. Den kan være slettet, eller linket er forkert.</p>
        </section>
    </fragment>
</template>

<script>
import gql from 'graphql-tag'
import {ageGroupLabel, findPositions, getCurrentSeason, isYoungPlayer} from "../../helpers";

export default {
    name: "TeamFightPublic",
    methods: {
        ageGroupLabel,
        isYoungPlayer,
        findPositions
    },
    data() {
        return {
            searchPlayer: '',
            showNotificationPopUp: false,
            team: null
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
                      clubhouse {
                          name
                      }
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
