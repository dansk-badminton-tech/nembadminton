<template>
    <div>
        <div v-for="(team, index) in teams" :key="team.id" class="column is-full">
            <table class="table is-striped mt-5 is-fullwidth">
                <thead>
                <tr>
                    <th colspan="2">
                        <h2 class="is-pulled-left">Hold {{ index + 1 }}</h2>
                        <b-taglist class="ml-2 is-pulled-left">
                            <b-tag>{{ team.league }}</b-tag>
                            <b-tag type="is-danger" v-if="hasMissingPlayerInCategory(index) || hasEmptySpots(index)">
                                Ugyldig hold
                            </b-tag>
                        </b-taglist>
                        <b-dropdown aria-role="list" class="is-pulled-right">
                            <template #trigger="{ active }">
                                <b-button
                                    :icon-right="active ? 'angle-up' : 'angle-down'"/>
                            </template>
                            <b-dropdown-item :disabled="team.league === 'OTHER'" @click="team.league = 'OTHER'"
                                             aria-role="listitem">Sæt som "andet" hold
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="team.league === 'FIRSTDIVISION'"
                                             @click="team.league = 'FIRSTDIVISION'" aria-role="listitem">Sæt som 1.
                                division hold
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="team.league === 'LIGA'" @click="team.league = 'LIGA'"
                                             aria-role="listitem">Sæt som LIGA hold
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="index === 0" @click="move(index, -1)" aria-role="listitem">Flyt
                                hold op
                            </b-dropdown-item>
                            <b-dropdown-item :disabled="index === teams.length-1" @click="move(index, 1)"
                                             aria-role="listitem">Flyt hold ned
                            </b-dropdown-item>
                            <b-dropdown-item aria-role="listitem" @click="confirmDelete(team)">Slet</b-dropdown-item>
                        </b-dropdown>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="category in team.categories" :key="category.name">
                    <th>{{ category.name }}</th>
                    <draggable :disabled="viewMode" :list="category.players" group="players" handle=".handle" tag="td"
                               @end="emitEnd">
                        <div v-for="player in category.players" class="is-clearfix mt-1">
                            <b-tooltip
                                :active="isPlayingToHigh(player, category.category) || isPlayingToHighInSquad(player, category.category)"
                                multilined>
                                <template v-slot:content>
                                    <span v-html="resolveLabel(player, category.category, team.league)"></span>
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
                                <b-button size="is-small" title="Slet" icon-right="times-circle"
                                          @click="deletePlayer(category, player)"></b-button>
                                <b-button size="is-small" title="Kopier spiller" icon-right="copy"
                                          @click="copyPlayer(category, player)"></b-button>
                            </div>
                        </div>
                        <player-search
                            v-if="category.players.length === 0"
                            :club-id="clubId" :exclude-players="[]"
                            :version="new Date(version)" :category="category"
                        ></player-search>
                        <player-search
                            class="mt-1"
                            v-if="isDouble(category) && category.players.length <= 1"
                            :club-id="clubId" :exclude-players="[]"
                            :version="new Date(version)" :category="category"
                        ></player-search>
                    </draggable>
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
    resolveToolTip,
    isPlayingToHighByBadmintonPlayerId, isDoubleCategory, isYoungPlayer
} from "../helpers";
import PlayerSearch from "../components/search-player/PlayerSearch";
import PlayersListSearch from "./PlayersListSearch";

export default {
    name: 'TeamTable',
    components: {PlayerSearch, PlayersListSearch, Draggable},
    props: {
        version: Date,
        clubId: String,
        viewMode: Boolean,
        confirmDelete: Function,
        move: Function,
        playerMove: Function,
        deletePlayer: Function,
        copyPlayer: Function,
        playingToHigh: {
            type: Array,
            default: []
        },
        playingToHighInSquad: {
            type: Array,
            default: []
        },
        teams: {
            type: Array,
            default: []
        },
        teamsBaseValidations: {
            type: Array,
            default: () => ([])
        },
        search: {
            type: String,
            default: ''
        }
    },
    methods: {
        emitEnd(evt){
            this.$emit('end', evt)
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
            let base = {}
            if (this.viewMode) {
                base = {'pointer': false};
                if (this.search.trim() !== '') {
                    base = {
                        ...{
                            'has-text-white': player.name.toLowerCase().includes(this.search.toLowerCase()),
                            'has-background-black': player.name.toLowerCase().includes(this.search.toLowerCase())
                        }, ...base
                    }
                }
            } else {
                base = simpleHighlight(this.playingToHigh, this.playingToHighInSquad, player, category)
            }
            return base;
        }
    }
}
</script>
