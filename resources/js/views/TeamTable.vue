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
                    <draggable :disabled="viewMode" :list="category.players" group="players" handle=".handle" tag="td">
                        <div v-for="player in category.players" :key="player.id" class="is-clearfix mt-1">
                            <p class="fa-pull-left handle" v-bind:class="highlight(player.name)">
                                <b-icon
                                    v-if="!viewMode"
                                    icon="bars"
                                    size="is-small">
                                </b-icon>
                                {{ player.name }} {{ findPositions(player) }}
                            </p>
                            <b-dropdown aria-role="list" class="is-pulled-right">
                                <b-button v-if="category.players.length && !viewMode" slot="trigger" icon-left="ellipsis-v" size="is-small"></b-button>
                                <b-dropdown-item aria-role="menuitem" has-link>
                                    <a href="#" @click.prevent="deletePlayer(category, player)">
                                        <b-icon icon="times-circle"></b-icon>
                                        Slet
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
import {findPositions} from "../helpers";

export default {
    name: 'TeamTable',
    components: {Draggable},
    props: {
        viewMode: Boolean,
        confirmDelete: Function,
        move: Function,
        deletePlayer: Function,
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
        findPositions,
        highlight: function (name) {
            let base = {'pointer': !this.viewMode};
            if (this.search.trim() !== '') {
                base = {
                    ...{
                        'has-text-white': name.includes(this.search),
                        'has-background-black': name.includes(this.search)
                    }, ...base
                }
            }
            return base;
        }
    }
}
</script>
