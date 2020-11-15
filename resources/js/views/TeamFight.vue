<template>
    <div>
        <b-loading v-model="$apollo.loading" :can-cancel="true" :is-full-page="true"></b-loading>
        <b-button icon-left="save" @click="saveTeams">{{
                this.createMode
                ? 'Opret'
                : 'Gem'
            }}
        </b-button>
        <b-dropdown aria-role="list">
            <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                <span>Tilføj hold</span>
                <b-icon :icon="active ? 'angle-up' : 'angle-down'"></b-icon>
            </button>
            <b-dropdown-item aria-role="listitem" @click="addTeam8">
                <b-icon icon="users" size="is-small"></b-icon>
                8 personer
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" @click="addTeam10">
                <b-icon icon="users" size="is-small"></b-icon>
                10 personer
            </b-dropdown-item>
        </b-dropdown>
        <ValidateTeams ref="validateTeams" :teams="this.teams"/>
        <b-dropdown aria-role="list">
            <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                <span>Indstillinger</span>
                <b-icon :icon="active ? 'angle-up' : 'angle-down'"></b-icon>
            </button>
            <b-dropdown-item aria-role="listitem" @click="loadTeamFromCache">
                <b-icon icon="save"></b-icon>
                Load fra cache
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" @click="$refs.validateTeams.validTeams()">
                <b-icon icon="brain"></b-icon>
                Validere hold
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" @click="deleteTeam">
                <b-icon icon="trash"></b-icon>
                Slet holdet
            </b-dropdown-item>
        </b-dropdown>
        <div class="columns">
            <div class="column">
                <b-field label="Navn">
                    <b-input v-model="name" placeholder="fx. Runde 1"></b-input>
                </b-field>
            </div>
            <div class="column">
                <b-field label="Spille dato">
                    <b-datepicker
                        v-model="gameDate"
                        icon="calendar-alt"
                        placeholder="Klik for at vælge dato..."
                        trap-focus>
                    </b-datepicker>
                </b-field>
            </div>
        </div>
        <div class="columns mt-2">
            <div class="column">
                <ClubSearch :select-club="selectClub"></ClubSearch>
            </div>
            <div class="column">
                <PlayerSearch :add-player="this.addPlayer" :club-id="clubId" :exclude-players="[]"></PlayerSearch>
            </div>
        </div>
        <PlayerList :players="this.players"></PlayerList>
        <p>Træk spillerne fra bænken til et hold</p>
        <div v-if="this.teams.length === 0" class="content has-text-grey has-text-centered">
            <p>
                <b-icon
                    icon="users"
                    size="is-large">
                </b-icon>
            </p>
            <p>Kom i gang med din næste holdkamp planlægning her</p>
            <b-button
                type="is-primary"
                @click="addTeam8">
                Tilføj 8 personers hold
            </b-button>
            <b-button
                type="is-primary"
                @click="addTeam10">
                Tilføj 10 personers hold
            </b-button>
        </div>
        <draggable :list="this.teams" class="columns is-multiline" handle=".handle">
            <div v-for="(team, index) in this.teams" :key="team.id" class="column is-4">
                <table class="table is-striped mt-5 is-fullwidth">
                    <thead>
                    <tr>
                        <th colspan="2">
                            Hold {{ index + 1 }}
                            <b-button class="is-pulled-right" icon-left="trash-alt" @click="confirmDelete(team)"></b-button>
                            <b-tooltip class="is-pulled-right" label="Flyt hold op">
                                <b-button v-if="index !== 0" icon-left="angle-up" @click="move(index, -1)"></b-button>
                            </b-tooltip>
                            <b-tooltip class="is-pulled-right" label="Flyt hold ned">
                                <b-button v-if="index !== teams.length-1" icon-left="angle-down" @click="move(index, 1)"></b-button>
                            </b-tooltip>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="category in team.categories" :key="category.name">
                        <th>{{ category.name }}</th>
                        <draggable :list="category.players" group="players" tag="td">
                            <p v-for="player in category.players" :key="player.id">{{ player.name }} {{ findPositions(player) }}</p>
                            <p v-if="!category.players.length">---------------</p>
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
import gql from "graphql-tag"
import {findPositions} from "../helpers";
import ValidateTeams from "./ValidateTeams";

export default {
    name: "TeamFight",
    components: {
        ValidateTeams,
        PlayerList,
        PlayerSearch,
        ClubSearch,
        Draggable
    },
    props: {
        teamFightId: String,
        createMode: Boolean
    },
    data() {
        return {
            teamCount: 1,
            clubId: null,
            name: null,
            gameDate: null,
            players: [],
            teams: []
        }
    },
    apollo: {
        teams: {
            query: gql` query ($id: ID!){
                  team(id: $id){
                    id
                    teams
                    name
                    gameDate
                  }
                }`,
            variables: function () {
                return {
                    id: this.teamFightId
                }
            },
            update: function (data) {
                this.name = data.team.name
                this.gameDate = new Date(data.team.gameDate)
                return JSON.parse(data.team.teams)
            },
            skip() {
                return this.createMode
            }
        }
    },
    methods: {
        confirmDelete(team) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette hold ' + (this.teams.indexOf(team) + 1) + '?',
                    onConfirm: () => {
                        this.teams.splice(this.teams.indexOf(team), 1)
                    }
                })
        },
        selectClub(id) {
            this.clubId = id
        },
        addPlayer(player) {
            this.players.push(player);
        },
        move(index, offset) {
            let teams = this.teams.slice()
            let temp = teams[index]
            teams[index] = teams[index + offset]
            teams[index + offset] = temp
            this.teams = teams
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
                                    category: "DS",
                                    players: []
                                }, {
                                    name: "2. DS",
                                    category: "DS",
                                    players: []
                                }, {
                                    name: "1. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "2. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "3. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "4. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "1. DD",
                                    category: "DD",
                                    players: []
                                }, {
                                    name: "2. DD",
                                    category: "DD",
                                    players: []
                                }, {
                                    name: "1. HD",
                                    category: "HD",
                                    players: []
                                }, {
                                    name: "2. HD",
                                    category: "HD",
                                    players: []
                                }, {
                                    name: "3. HD",
                                    category: "HD",
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
                                    category: "DS",
                                    players: []
                                }, {
                                    name: "2. DS",
                                    category: "DS",
                                    players: []
                                }, {
                                    name: "1. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "2. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "3. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "4. HS",
                                    category: "HS",
                                    players: []
                                }, {
                                    name: "1. DD",
                                    category: "DD",
                                    players: []
                                }, {
                                    name: "1. HD",
                                    category: "HD",
                                    players: []
                                }, {
                                    name: "2. HD",
                                    category: "HD",
                                    players: []
                                }
                                ]
                            })
        },
        loadTeamFromCache() {
            this.teams = JSON.parse(localStorage.getItem('teams'));
        },
        saveTeams() {
            localStorage.setItem('teams', JSON.stringify(this.teams));
            const updateTeamGQL = gql`
                        mutation ($id: ID!, $teams: String, $name: String, $gameDate: String){
                          updateTeam(id: $id, teams: $teams, name: $name, gameDate: $gameDate){
                            id
                            teams
                            name
                          }
                        }
                    `;
            const createTeamGQL = gql`
                        mutation ($teams: String, $name: String, $gameDate: String){
                          createTeam(teams: $teams, name: $name, gameDate: $gameDate){
                            id
                            teams
                            name
                            gameDate
                          }
                        }
                    `;
            this.$apollo.mutate(
                {
                    mutation: this.createMode
                              ? createTeamGQL
                              : updateTeamGQL,
                    variables:
                        this.createMode
                        ? {
                                teams: JSON.stringify(this.teams),
                                name: this.name,
                                gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate()
                            }
                        : {
                                id: this.teamFightId,
                                teams: JSON.stringify(this.teams),
                                name: this.name,
                                gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate()
                            }
                }).then(({data}) => {
                if (this.createMode) {
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-sucess',
                            message: `Dit hold er gemt`
                        })
                    this.$router.push({name: 'team-fight-edit', params: {teamUUID: data.createTeam.id}})
                } else {
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-sucess',
                            message: `Dit hold er gemt`
                        })
                }
            }).catch((error) => {
                this.$buefy.snackbar.open(
                    {
                        duration: 2000,
                        type: 'is-dagner',
                        message: `Kunne ikke gemme dit hold :(`
                    })
            })
        },
        deleteTeam() {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette helt holdet?',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation ($id: ID!){
                                        deleteTeam(id: $id){
                                            id
                                        }
                                    }
                                `,
                                variables: {
                                    id: this.teamFightId
                                }
                            }).then(() => {
                            this.$router.push({name: 'team-fight-dashboard'})
                        })
                    }
                })
        }
    }
}
</script>

