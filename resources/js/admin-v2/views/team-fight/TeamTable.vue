<template>
    <div>
        <b-loading :is-full-page="false" v-model="loading" :can-cancel="true"></b-loading>
        <div v-for="(squad, index) in squads" :key="squad.id" class="column is-full">
            <table class="table is-striped mt-5 is-fullwidth">
                <thead>
                <tr>
                    <th colspan="2">
                        <h2 class="is-pulled-left"><strong>Hold {{ index + 1 }}</strong> {{ squad.name || 'intet navn' }}</h2>
                        <b-taglist class="ml-2 is-pulled-left">
                            <b-tag type="is-danger" v-if="hasEmptySpots(index)">
                                Ugyldigt hold
                            </b-tag>
                        </b-taglist>
                        <div class="buttons is-pulled-right">
                            <b-button title="Udfyld holdnavn, kampnummer, spille start, spillested, adresse, postnummer og by" icon-left="pencil" @click="openEditSquadModal(squad)"></b-button>
                            <b-button :disabled="index === 0" @click="moveSquadOrderUp(squad)" title="Flyt hold op" icon-left="arrow-up"></b-button>
                            <b-button :disabled="index === squads.length-1" @click="moveSquadOrderDown(squad)" title="Flyt hold ned" icon-left="arrow-down"></b-button>
                            <b-button icon-right="open-in-new" title="Link til badmintonplayer. Kraver kamp nummer" :disabled="!!!squad?.externalTeamFightID" class="is-pulled-right" tag="a" target="_blank"
                                      :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+getCurrentSeason+',,,,,'+squad.externalTeamFightID+',,'"
                                      type="is-link">
                            </b-button>
                            <b-button type="is-danger" @click="confirmDelete(squad)" title="Slet" icon-left="delete"></b-button>
                        </div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="category in squad.categories" :key="category.name">
                    <th>{{ category.name }}</th>
                    <td @drop="onDrop($event, squad, category)"
                        @dragover.prevent
                        @dragenter.prevent>
                        <div draggable="true"
                             v-for="player in category.players"
                             @dragstart="startDrag($event, squad, category, player)"
                             :key="player.id"
                             :data-player-id="player.id"
                             class="is-clearfix mt-1">
                            <input type="hidden" :data-player-id-input="player.id"/>
                            <b-tooltip
                                class="is-pulled-left"
                                :active="isPlayingToHigh(player) || isPlayingToHighInSquad(player, category.category)"
                                multilined>
                                <template v-slot:content>
                                    <span v-html="resolveLabel(player, category.category, squad.league)"></span>
                                </template>
                                <p class="fa-pull-left handle" v-bind:class="highlight(player, category.category)">
                                    <b-icon
                                        v-show="player.gender === 'M'"
                                        icon="gender-male"
                                        size="is-small">
                                    </b-icon>
                                    <b-icon
                                        v-show="player.gender === 'K'"
                                        icon="gender-female"
                                        size="is-small">
                                    </b-icon>
                                    {{ player.name }}
                                    ({{ findPositions(player, 'N') + ' ' + findPositions(player, category.category) }})
                                </p>
                                <b-tag v-if="isYoungPlayer(player, null)">U17/U19</b-tag>
                            </b-tooltip>
                            <b-tooltip class="is-pulled-left" label="Point er redigeret manuelt">
                                <b-icon
                                    v-show="hasCorrectedPoints(player.points)"
                                    icon="information"
                                    type="is-info"
                                    size="is-small">
                                </b-icon>
                            </b-tooltip>
                            <div class="buttons is-pulled-right">
                                <b-button :disabled="loading" size="is-small" title="Rediger" icon-right="pen"
                                          @click="openEditPlayerModal(squad, category, player)"></b-button>
                                <b-button :disabled="loading" size="is-small" title="Slet" icon-right="close"
                                          @click="deletePlayer(squad, category, player)"></b-button>
                            </div>
                        </div>
                        <PlayerSearch
                            v-if="category.players.length === 0"
                            @select-player="addPlayer(squad, category, $event)"
                            :squad="squad"
                            :disabled="loading"
                            :club-id="clubId" :exclude-players="[]"
                            :version="new Date(version)" :category="category"/>
                        <PlayerSearch
                            class="mt-1"
                            v-if="isDouble(category) && category.players.length <= 1"
                            @select-player="addPlayer(squad, category, $event)"
                            :squad="squad"
                            :disabled="loading"
                            :club-id="clubId" :exclude-players="[]"
                            :version="new Date(version)" :category="category"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
import Draggable from "vuedraggable"
import {
    findPositions, getCurrentSeason,
    highlight as simpleHighlight,
    isDoubleCategory,
    isPlayingToHighByBadmintonPlayerId,
    isYoungPlayer,
    resolveToolTip
} from "../../helpers";
import PlayersListSearch from "./PlayersListSearch";
import PlayerSearch from "../common/PlayerSearch.vue";
import EditPlayerModal from "./EditPlayerModal.vue";
import EditSquadModal from "./EditSquadModal.vue";

export default {
    name: 'TeamTable',
    components: {PlayerSearch, PlayersListSearch, Draggable},
    props: {
        version: Date,
        clubId: String,
        confirmDelete: Function,
        moveSquadOrderUp: Function,
        moveSquadOrderDown: Function,
        playerMove: Function,
        deletePlayer: Function,
        addPlayer: Function,
        updateSquad: Function,
        playingToHigh: {
            type: Array,
            default: []
        },
        playingToHighInSquad: {
            type: Array,
            default: []
        },
        squads: {
            type: Array,
            default: []
        },
        teamsBaseValidations: {
            type: Array,
            default: []
        },
        loading: Boolean
    },
    computed: {
        getCurrentSeason
    },
    methods: {
        startDrag(evt, squad, category, player) {
            evt.dataTransfer.dropEffect = 'move'
            evt.dataTransfer.effectAllowed = 'move'
            evt.dataTransfer.setData('squad', JSON.stringify(squad))
            evt.dataTransfer.setData('category', JSON.stringify(category))
            evt.dataTransfer.setData('player', JSON.stringify(player))
        },
        onDrop(evt, targetSquad, targetCategory) {
            let sourceSquad = JSON.parse(evt.dataTransfer.getData('squad'))
            let sourceCategory = JSON.parse(evt.dataTransfer.getData('category'))
            let player = JSON.parse(evt.dataTransfer.getData('player'))
            if (sourceSquad.id !== targetSquad.id || targetCategory.id !== sourceCategory.id) {
                this.playerMove(evt, player, sourceSquad, sourceCategory, targetSquad, targetCategory)
            }
        },
        setSquadLeague(squad, league) {
            squad.league = league
            this.updateSquad(squad)
        },
        isYoungPlayer,
        isDouble(category) {
            return isDoubleCategory(category)
        },
        hasEmptySpots(index) {
            if (this.teamsBaseValidations.length === 0) {
                return false;
            } else {
                const found = this.teamsBaseValidations.find((base) => {
                    return index === base.index && base.spotsFulfilled === false
                })
                return found !== undefined;
            }
        },
        resolveLabel(player, category, league) {
            return resolveToolTip(player, category, league, this.playingToHigh, this.playingToHighInSquad)
        },
        isPlayingToHigh(player) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHigh, player);
        },
        isPlayingToHighInSquad(player, category) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHighInSquad, player, category);
        },
        findPositions,
        hasCorrectedPoints(points) {
            return points.some((point) => point.corrected_manually)
        },
        highlight: function (player, category) {
            return simpleHighlight(this.playingToHigh, this.playingToHighInSquad, player, category);
        },
        openEditPlayerModal(squad, category, player) {
            this.$buefy.modal.open(
                {
                    parent: this,
                    props: {
                        value: player
                    },
                    events: {
                        close() {
                        }
                    },
                    canCancel: ["x"],
                    component: EditPlayerModal,
                    hasModalCard: true,
                    trapFocus: true
                })
        },
        openEditSquadModal(squad) {
            this.$buefy.modal.open(
                {
                    parent: this,
                    width: 1500,
                    props: {
                        squad: squad
                    },
                    events: {
                        close() {
                        }
                    },
                    canCancel: ["x"],
                    component: EditSquadModal,
                    hasModalCard: true,
                    trapFocus: true
                })
        }
    }
}
</script>
