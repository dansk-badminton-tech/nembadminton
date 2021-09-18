<template>
    <fragment>
        <div v-for="(team, index) in teams" :key="team.id" class="column is-4">
            <table class="table is-striped mt-5 is-fullwidth">
                <thead>
                <tr>
                    <th colspan="2">
                        Hold {{ index + 1 }}
                        <b-button v-if="!viewMode" class="is-pulled-right" icon-left="trash-alt" @click="confirmDelete(team)"></b-button>
                        <b-tooltip v-if="!viewMode" class="is-pulled-right" label="Flyt hold op">
                            <b-button v-if="index !== 0" icon-left="angle-up" @click="move(index, -1)"></b-button>
                        </b-tooltip>
                        <b-tooltip v-if="!viewMode" class="is-pulled-right" label="Flyt hold ned">
                            <b-button v-if="index !== teams.length-1" icon-left="angle-down" @click="move(index, 1)"></b-button>
                        </b-tooltip>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="category in team.categories" :key="category.name">
                    <th>{{ category.name }}</th>
                    <draggable :disabled="viewMode" :list="category.players" group="players" handle=".handle" tag="td" @end="$emit('end')">
                        <div v-for="player in category.players" class="is-clearfix mt-1">
                            <b-tooltip
                                :label="resolveLabel(player, category.category)"
                                :active="isPlayingToHigh(player, category.category) || isPlayingToHighInSquad(player, category.category)"
                                multilined>
                                <p class="fa-pull-left handle" v-bind:class="highlight(player, category.category)">
                                    <b-icon
                                        v-if="!viewMode"
                                        icon="bars"
                                        size="is-small">
                                    </b-icon>
                                    {{ player.name }} ({{ findPositions(player, 'N') + ' ' + findPositions(player, category.category) }})
                                </p>
                            </b-tooltip>
                            <b-dropdown aria-role="list" class="is-pulled-right">
                                <b-button v-if="category.players.length && !viewMode" slot="trigger" icon-left="ellipsis-v" size="is-small"></b-button>
                                <b-dropdown-item aria-role="menuitem" has-link>
                                    <a href="#" @click.prevent="deletePlayer(category, player)">
                                        <b-icon icon="times-circle"></b-icon>
                                        Slet
                                    </a>
                                </b-dropdown-item>
                                <b-dropdown-item aria-role="menuitem" has-link>
                                    <a href="#" @click.prevent="copyPlayer(category, player)">
                                        <b-icon icon="copy"></b-icon>
                                        Kopier
                                    </a>
                                </b-dropdown-item>
                            </b-dropdown>
                        </div>
                        <p v-if="!category.players.length">---------------</p>
                    </draggable>
                </tr>
                </tbody>
            </table>
        </div>
    </fragment>
</template>
<script>
import Draggable from "vuedraggable"
import {
    findPositions,
    isPlayingToHigh,
    highlight as simpleHighlight,
    resolveToolTip,
    isPlayingToHighByBadmintonPlayerId
} from "../helpers";

export default {
    name: 'TeamTable',
    components: {Draggable},
    props: {
        viewMode: Boolean,
        confirmDelete: Function,
        move: Function,
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
        search: {
            type: String,
            default: ''
        }
    },
    methods: {
        resolveLabel(player, category){
            return resolveToolTip(player, category, this.playingToHigh, this.playingToHighInSquad)
        },
        isPlayingToHigh(player, category){
            return isPlayingToHighByBadmintonPlayerId(this.playingToHigh, player, category);
        },
        isPlayingToHighInSquad(player, category){
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
