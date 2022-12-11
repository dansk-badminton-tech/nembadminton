<template>
    <div>
        <div v-for="(squad, index) in squads" :key="squad.id" class="column is-full">
            <table class="table is-striped mt-5 is-fullwidth">
                <thead>
                <tr>
                    <th colspan="2">
                        <h2 class="is-pulled-left">Hold {{ index + 1 }}</h2>
                        <b-taglist class="ml-2 is-pulled-left">
                            <b-tag>{{ squad.league }}</b-tag>
                            <b-tag type="is-danger" v-if="hasMissingPlayerInCategory(index) || hasEmptySpots(index)">
                                Ugyldigt hold
                            </b-tag>
                        </b-taglist>
                        <b-dropdown aria-role="list" class="is-pulled-right">
                            <template #trigger="{ active }">
                                <b-button
                                    :icon-right="active ? 'angle-up' : 'angle-down'"/>
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
                            <b-dropdown-item :disabled="index === 0" @click="changeOrder(index, -1)" aria-role="listitem">Flyt
                                hold op
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="index === squads.length-1" @click="changeOrder(index, 1)"
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
<!--                    <draggable :list="category.players" group="players" handle=".handle" tag="td"-->
<!--                               @change="playerChange($event, squad, category)"-->
<!--                               @update="playerMoved"-->
<!--                               @add="playerMoved"-->
<!--                    >-->
                    <td @drop="onDrop($event, squad, category)"
                        @dragover.prevent
                        @dragenter.prevent>
                        <div draggable="true"
                             v-for="player in category.players"
                             @dragstart="startDrag($event, squad, category, player)"
                             @dragend="endDrag"
                             :key="player.id"
                             class="is-clearfix mt-1">
                            <b-tooltip
                                :active="(isPlayingToHigh(player, category.category) || isPlayingToHighInSquad(player, category.category)) && !hideTooltip"
                                multilined>
                                <template v-slot:content>
                                    <span v-html="resolveLabel(player, category.category, squad.league)"></span>
                                </template>
                                <p class="fa-pull-left handle" v-bind:class="highlight(player, category.category)">
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
                                    ({{ findPositions(player, 'N') + ' ' + findPositions(player, category.category) }})
                                </p>
                                <b-tag v-if="isYoungPlayer(player, null)">U17/U19</b-tag>
                            </b-tooltip>
                            <div class="buttons is-pulled-right">
                                <b-button :disabled="loading" size="is-small" title="Slet" icon-right="times-circle"
                                          @click="deletePlayer(squad, category, player)"></b-button>
                            </div>
                        </div>
                        <PlayerSearch
                            v-if="category.players.length === 0"
                            @select-player="addPlayer(squad, category, $event)"
                            :squad="squad"
                            :disabled="loading"
                            :club-id="clubId" :exclude-players="[]"
                            :version="new Date(version)" :category="category"></PlayerSearch>
                        <PlayerSearch
                            class="mt-1"
                            v-if="isDouble(category) && category.players.length <= 1"
                            @select-player="addPlayer(squad, category, $event)"
                            :squad="squad"
                            :disabled="loading"
                            :club-id="clubId" :exclude-players="[]"
                            :version="new Date(version)" :category="category"></PlayerSearch>
<!--                    </draggable>-->
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
} from "../helpers";
import PlayerSearch from "../components/search-player/PlayerSearch";
import PlayersListSearch from "./PlayersListSearch";

export default {
    name: 'TeamTable',
    components: {PlayerSearch, PlayersListSearch, Draggable},
    props: {
        version: Date,
        clubId: String,
        confirmDelete: Function,
        changeOrder: Function,
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
    data(){
        return {
            hideTooltip: false
        }
    },
    methods: {
        endDrag() {
            console.log("start")
            this.hideTooltip = false
        },
        startDrag(evt, squad, category, player) {
            this.hideTooltip = true
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
        hasMissingPlayerInCategory(index) {
            if (this.teamsBaseValidations.length === 0) {
                return false;
            } else {
                const found = this.teamsBaseValidations.find((base) => {
                    return index === base.index && base.missingPlayerInCategory === true
                })
                return found !== undefined;
            }
        },
        resolveLabel(player, category, league) {
            return resolveToolTip(player, category, league, this.playingToHigh, this.playingToHighInSquad)
        },
        isPlayingToHigh(player, category) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHigh, player, category);
        },
        isPlayingToHighInSquad(player, category) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHighInSquad, player, category);
        },
        findPositions,
        highlight: function (player, category) {
            return simpleHighlight(this.playingToHigh, this.playingToHighInSquad, player, category);
        }
    }
}
</script>
