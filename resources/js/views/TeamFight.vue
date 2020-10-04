<template>
    <div>
        <div class="columns">
            <div class="column">
                <ClubSearch :select-club="selectClub"></ClubSearch>
            </div>
            <div class="column">
                <PlayerSearch :add-player="this.addPlayer" :club-id="clubId" :exclude-players="this.players"></PlayerSearch>
            </div>
        </div>
        <b-button @click="addTeam8">Tilføj hold - 8 personer</b-button>
        <b-button @click="addTeam10">Tilføj hold - 10 personer</b-button>
        <b-button @click="saveTeams">Gem</b-button>
        <b-button @click="loadTeams">Load</b-button>
        <b-button @click="validTeams">Validerer</b-button>
        <PlayerList :players="this.players" class="mt-5"></PlayerList>
        <p>Træk spillerne fra bænken til et hold</p>
        <pre>{{ logs.join('\n') }}</pre>
        <draggable :list="this.teams" class="columns is-multiline" handle=".handle">
            <div v-for="(team, index) in this.teams" :key="team.id" class="column is-4">
                <table class="table is-bordered mt-5">
                    <thead>
                    <tr>
                        <th><i class="handle">---</i> Hold {{ index + 1 }} ({{ team.playerLimit }} spiller)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="category in team.categories" :key="category.name">
                        <th>{{ category.name }}</th>
                        <draggable :list="category.players" group="players" tag="td">
                            <p v-for="player in category.players" :key="player.id">{{ player.name }} {{ findPositions(player) }}</p>
                            <p v-if="!category.players.length">---</p>
                        </draggable>
                    </tr>
                    </tbody>
                </table>
            </div>
        </draggable>
    </div>
</template>

<script>
import ClubSearch from "../components/search-club/ClubSearch";
import PlayerSearch from "../components/search-player/PlayerSearch";
import PlayerList from "./PlayerList";
import Draggable from "vuedraggable"
import {findLevel, findPlayersInCategory, findPositions} from "../helpers";

export default {
    name: "TeamFight",
    components: {
        PlayerList,
        PlayerSearch,
        ClubSearch,
        Draggable
    },
    data() {
        return {
            teamCount: 1,
            clubId: null,
            players: [],
            teams: [],
            logs: []
        }
    },
    methods: {
        selectClub(id) {
            this.clubId = id
        },
        addPlayer(player) {
            this.players.push(player);
        },
        validTeams() {
            this.logs = []
            let limit = 50
            const lowToHighTeams = this.teams.slice().reverse()
            lowToHighTeams.forEach((team, index) => {
                for (let category of team.categories) {
                    for (let player of category.players) {
                        let controlTeams = lowToHighTeams.slice()
                        controlTeams = controlTeams.slice(1 + index, controlTeams.length)
                        for (let checkTeam of controlTeams) {
                            let players = findPlayersInCategory(checkTeam.categories, category.category)
                            for (let controlPlayer of players) {
                                let controlPlayerLevel = findLevel(controlPlayer);
                                let playerLevel = findLevel(player);
                                if (controlPlayerLevel < playerLevel) {
                                    this.logs.push(player.name + ' has higher level then ' + controlPlayer.name)
                                    let playerMinusThreshold = playerLevel - limit;
                                    if (controlPlayerLevel < playerMinusThreshold) {
                                        this.logs.push(player.name + '(' + playerMinusThreshold + ') still has higher level then ' + controlPlayer.name + ' (' + controlPlayerLevel + ') after relax rules applied')
                                    }
                                }
                            }
                        }
                    }
                }
            })
            if (this.logs.length === 0) {
                this.logs.push('Alle hold er gyldig')
            }
        },
        findPositions,
        addTeam10() {
            this.teams.push({
                                id: this.teamCount++,
                                playerLimit: 10,
                                categories: [{
                                    name: "1. MD",
                                    category: "MD",
                                    players: []
                                }, {
                                    name: "2. MD",
                                    category: "MD",
                                    players: []
                                }, {
                                    name: "1. DS",
                                    players: []
                                }, {
                                    name: "2. DS",
                                    players: []
                                }, {
                                    name: "1. HS",
                                    players: []
                                }, {
                                    name: "2. HS",
                                    players: []
                                }, {
                                    name: "3. HS",
                                    players: []
                                }, {
                                    name: "4. HS",
                                    players: []
                                }, {
                                    name: "1. DD",
                                    players: []
                                }, {
                                    name: "2. DD",
                                    players: []
                                }, {
                                    name: "1. HD",
                                    players: []
                                }, {
                                    name: "2. HD",
                                    players: []
                                }, {
                                    name: "3. HD",
                                    players: []
                                }
                                ]
                            })
        },
        addTeam8() {
            this.teams.push({
                                id: this.teamCount++,
                                playerLimit: 8,
                                categories: [{
                                    name: "1. MD",
                                    category: "MD",
                                    players: []
                                }, {
                                    name: "2. MD",
                                    category: "MD",
                                    players: []
                                }, {
                                    name: "1. DS",
                                    players: []
                                }, {
                                    name: "2. DS",
                                    players: []
                                }, {
                                    name: "1. HS",
                                    players: []
                                }, {
                                    name: "2. HS",
                                    players: []
                                }, {
                                    name: "3. HS",
                                    players: []
                                }, {
                                    name: "4. HS",
                                    players: []
                                }, {
                                    name: "1. DD",
                                    players: []
                                }, {
                                    name: "1. HD",
                                    players: []
                                }, {
                                    name: "2. HD",
                                    players: []
                                }
                                ]
                            })
        },
        loadTeams() {
            this.teams = JSON.parse(localStorage.getItem('teams'));
        },
        saveTeams() {
            localStorage.setItem('teams', JSON.stringify(this.teams));
        }
    }
}
</script>

<style scoped>

</style>
