<template>
    <div>
        <b-loading :is-full-page="false" v-model="loading" :can-cancel="true"></b-loading>
        <div v-for="(squad, index) in squads" :key="squad.id" class="column is-full">
            <table class="table is-striped mt-5 is-fullwidth">
                <thead>
                <tr>
                    <th colspan="2">
                        <h2 class="is-pulled-left">Hold {{ index + 1 }}</h2>
                        <b-taglist class="ml-2 is-pulled-left">
                            <b-tag>{{ squad.league }}</b-tag>
                            <b-tag type="is-danger" v-if="hasEmptySpots(index)">
                                Ugyldigt hold
                            </b-tag>
                        </b-taglist>
                        <b-dropdown position="is-bottom-left" aria-role="list" class="is-pulled-right">
                            <template #trigger="{ active }">
                                <b-button
                                    :icon-right="active ? 'arrow-up' : 'cog'"/>
                            </template>
                            <b-dropdown-item :disabled="squad.league === 'OTHER'" @click="setSquadLeague(squad,'OTHER')"
                                             aria-role="listitem">Sæt som "andet" hold
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="squad.league === 'FIRSTDIVISION'"
                                             @click="setSquadLeague(squad,'FIRSTDIVISION')" aria-role="listitem">Sæt som 1.
                                division hold
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="squad.league === 'LIGA'" @click="setSquadLeague(squad, 'LIGA')"
                                             aria-role="listitem">Sæt som LIGA hold
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="index === 0" @click="moveSquadOrderUp(squad)" aria-role="listitem">Flyt
                                hold op
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="index === squads.length-1" @click="moveSquadOrderDown(squad)"
                                             aria-role="listitem">Flyt hold ned
                            </b-dropdown-item>
                            <b-dropdown-item aria-role="listitem" @click="confirmDelete(squad)">Slet</b-dropdown-item>
                        </b-dropdown>
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
                            <input type="hidden" :data-player-id-input="player.id" />
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
                            <b-tooltip class="is-pulled-left" label="Point er redigeret manuel">
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
                            :version="new Date(version)" :category="category" />
                        <PlayerSearch
                            class="mt-1"
                            v-if="isDouble(category) && category.players.length <= 1"
                            @select-player="addPlayer(squad, category, $event)"
                            :squad="squad"
                            :disabled="loading"
                            :club-id="clubId" :exclude-players="[]"
                            :version="new Date(version)" :category="category" />
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
    findPositions,
    highlight as simpleHighlight,
    isDoubleCategory,
    isPlayingToHighByBadmintonPlayerId,
    isYoungPlayer,
    resolveToolTip
} from "../../helpers";
import PlayersListSearch from "./PlayersListSearch";
import PlayerSearch from "../common/PlayerSearch.vue";
import EditPlayerModal from "./EditPlayerModal.vue";
import gql from "graphql-tag";

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
            if(sourceSquad.id !== targetSquad.id || targetCategory.id !== sourceCategory.id){
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
        hasCorrectedPoints(points){
            return points.some((point) => point.corrected_manually)
        },
        highlight: function (player, category) {
            return simpleHighlight(this.playingToHigh, this.playingToHighInSquad, player, category);
        },
        openEditPlayerModal(squad, category, player){
            this.$buefy.modal.open({
                parent: this,
                props: {
                    value: player
                },
                events: {
                    close(){}
                },
                canCancel: ["x"],
                component: EditPlayerModal,
                hasModalCard: true,
                trapFocus: true
            })
        }
    }
}
</script>
