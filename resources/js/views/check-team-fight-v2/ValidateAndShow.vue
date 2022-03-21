<template>
    <fragment>
        <b-loading v-model="isLoading"></b-loading>
        <ValidationStatus :invalid-level="resolveInvalidLevel" :invalid-category="resolveInvalidCategory"
                          :hide-incomplete-team="true"/>
        <div class="columns is-multiline">
            <div v-for="team in teamMatches" v-if="playingToHighInSquad !== null && playingToHigh !== null" class="column is-4">
                <h1 class="title">{{ team.name }}
                    <b-button class="is-pulled-right"
                              tag="a"
                              target="_blank"
                              icon-right="external-link-square-alt"
                              :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+currentSeason+',,,,,'+team.leagueMatchId+',,'"
                              type="is-light">Se på BP
                    </b-button>
                </h1>
                <b-table :data="team.squad.categories">
                    <b-table-column v-slot="props" field="name" label="Kategori">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column v-slot="props" field="players" label="Spillere">
                        <b-tooltip
                            v-for="player in props.row.players"
                            :key="player.name+props.row.category"
                            :active="isPlayingToHigh(player, props.row.category) || isPlayingToHighInSquad(player, props.row.category)">
                            <template v-slot:content>
                                <span v-html="resolveLabel(player, props.row.category, team.squad.league)"></span>
                            </template>
                            <p v-bind:class="highlight(player, props.row.category)">{{ player.name }}
                                ({{ findPositions(player, 'N') + ' ' + findPositions(player, props.row.category) }})
                            </p>
                            <b-tag v-if="isYoungPlayer(player, null)">U17/U19</b-tag>
                        </b-tooltip>
                    </b-table-column>
                </b-table>
            </div>
        </div>
    </fragment>
</template>

<script>
import omitDeep from "omit-deep";
import gql from "graphql-tag";
import {
    findPositions, getCurrentSeason,
    highlight as simpleHighlight,
    isPlayingToHighByBadmintonPlayerId, isYoungPlayer,
    resolveToolTip
} from "../../helpers";
import ValidationStatus from "../ValidationStatus";
import {hasInvalidCategory, hasInvalidLevel} from "../team-fight/helper";

export default {
    name: "ValidateAndShow",
    components: {ValidationStatus},
    props: {
        teamMatches: Array
    },
    computed: {
        isLoading(){
            return this.loadingSquad || this.loadingCrossSquads
        },
        currentSeason: getCurrentSeason,
        resolveInvalidCategory() {
            if(this.playingToHighInSquad === null){
                return null
            }
            return hasInvalidCategory(this.playingToHighInSquad)
        },
        resolveInvalidLevel() {
            if(this.playingToHigh === null){
                return null
            }
            return hasInvalidLevel(this.playingToHigh)
        },
    },
    data(){
        return {
            playingToHigh: null,
            playingToHighInSquad: null,
            loadingCrossSquads: false,
            loadingSquad: false
        }
    },
    mounted() {
        this.validateSquads()
        this.validateCrossSquads()
    },
    methods: {
        validateCrossSquads() {
            let teamsClone = JSON.parse(JSON.stringify(this.teamMatches));
            teamsClone = omitDeep(teamsClone, ['__typename', 'leagueMatchId'])
            this.loadingCrossSquads = true
            this.$apollo.mutate(
                {
                    mutation: gql`mutation validateCrossSquads($input: [ValidateTeam!]!){
                        validateCrossSquads(input: $input){
                            name
                            id
                            gender
                            category
                            refId
                            isYouthPlayer
                            belowPlayer {
                                name
                                id
                                refId
                            }
                        }
                    }
                    `,
                    variables: {
                        input: teamsClone
                    }
                }
            ).then(({data}) => {
                this.playingToHigh = data.validateCrossSquads
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-danger',
                        message: `Fejlede cross squad validering`
                    })
            }).finally(() => {
                this.loadingCrossSquads = false
            })
        },
        validateSquads() {
            let teamsSquadCheck = JSON.parse(JSON.stringify(this.teamMatches));
            teamsSquadCheck = omitDeep(teamsSquadCheck, ['__typename', 'league', 'leagueMatchId'])
            this.loadingSquad = true;
            this.$apollo.mutate(
                {
                    mutation: gql`mutation validateSquads($input: [ValidateTeam!]!){
                      validateSquads(input: $input){
                        name
                        id
                        gender
                        category
                        refId
                        isYouthPlayer
                        hasYouthPlayerPartner
                        belowPlayer {
                            name
                            id
                            refId
                        }
                      }
                }
                `,
                    variables: {
                        input: teamsSquadCheck
                    }
                }
            ).then(({data}) => {
                this.playingToHighInSquad = data.validateSquads
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-danger',
                        message: `Fejlede squad validering`
                    })
            }).finally(() => {
                this.loadingSquad = false
            })
        },
        isPlayingToHigh(player, category) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHigh, player, category);
        },
        isPlayingToHighInSquad(player, category) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHighInSquad, player, category);
        },
        resolveLabel(player, category, league) {
            return resolveToolTip(player, category, league, this.playingToHigh, this.playingToHighInSquad)
        },
        highlight(player, category) {
            return simpleHighlight(this.playingToHigh, this.playingToHighInSquad, player, category)
        },
        findPositions,
        isYoungPlayer
    }
}
</script>

<style scoped>

</style>
