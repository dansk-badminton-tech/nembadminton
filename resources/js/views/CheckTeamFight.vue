<template>
    <section>
        <b-loading v-model="this.fetchingAndValidating" :is-full-page="true"></b-loading>
        <form v-if="!done">
            <b-steps v-model="activeStep">
                <template>
                    <b-step-item label="Basis">
                        <b-field label="Klub">
                            <BadmintonPlayerClubs v-model="clubId" @input="clearTeams"/>
                        </b-field>
                        <b-field label="Sæson">
                            <b-select v-model="season" expanded placeholder="Vælge sæson" @input="goToStepTeamsStep">
                                <option value="2020">2019/2020</option>
                                <option value="2021">2021/2022</option>
                            </b-select>
                        </b-field>
                    </b-step-item>
                    <b-step-item label="Hold">
                        <BadmintonPlayerTeamsMultiSelect v-model="playerTeams" :clubId="clubId" :season="season" @input="clearTeamFights"/>
                    </b-step-item>
                    <b-step-item label="Kampe">
                        <b-field label="Rangliste">
                            <b-select v-model="rankingList" expanded placeholder="Vælge rangliste">
                                <option value="2021-07-01">2021-07-01</option>
                                <option value="2020-12-01">2020-12-01</option>
                                <option value="2020-11-01">2020-11-01</option>
                                <option value="2020-10-01">2020-10-01</option>
                                <option value="2020-09-01">2020-09-01</option>
                                <option value="2020-08-01">2020-08-01</option>
                                <option value="2020-07-01">2020-07-01</option>
                            </b-select>
                        </b-field>
                        <draggable
                            :list="playerTeams"
                            handle=".handle"
                        >
                            <b-field v-for="team in playerTeams" :key="team.leagueGroupId+playerTeams.length" :label="team.name">
                                <b-icon class="handle mr-2" icon="align-justify"></b-icon>
                                <BadmintonPlayerTeamFights :clubId="clubId" :player-team="team" :season="season" @input="addTeamFight"/>
                            </b-field>
                        </draggable>
                    </b-step-item>
                    <b-step-item label="Bekræft">
                        <b-field label="Rangliste">
                            <b-input v-model="rankingList" disabled/>
                        </b-field>
                        <b-table :data="selectedTeamMatches">
                            <b-table-column v-slot="props" field="team.name" label="Hold">
                                {{ props.row.team.name }}
                            </b-table-column>
                            <b-table-column v-slot="props" field="team.name" label="Kamp">
                                {{ props.row.teamMatch.gameTime }} - {{ props.row.teamMatch.teams.join(' - ') }}
                            </b-table-column>
                        </b-table>

                        <b-button size="is-large mt-2" @click="badmintonPlayerTeamMatchesImport">Hent og tjek</b-button>
                    </b-step-item>
                </template>
            </b-steps>
        </form>
        <b-button v-if="done" @click="goToStart">Tilbage til start</b-button>
        <div v-if="done" class="columns is-multiline">
            <div v-for="team in teams" class="column is-4">
                <h1 class="title">{{ team.name }}</h1>
                <b-table :data="team.squad.categories">
                    <b-table-column v-slot="props" field="name" label="Kategori">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column v-slot="props" field="players" label="Spillere">
                        <b-tooltip
                            v-for="player in props.row.players"
                            :key="player.name+props.row.category"
                            :label="resolveLabel(player)"
                            :active="isPlayingToHigh(player) || isPlayingToHighInSquad(player)"
                            multilined>
                            <p v-bind:class="highlight(player)">{{ player.name }} ({{ findPositions(player, 'N') + ' ' + findPositions(player, props.row.category) }})</p>
                        </b-tooltip>
                    </b-table-column>
                </b-table>
            </div>
        </div>
    </section>
</template>

<script>
import BadmintonPlayerClubs from "../components/badminton-player/BadmintonPlayerClubs";
import BadmintonPlayerTeams from "../components/badminton-player/BadmintonPlayerTeams";
import BadmintonPlayerTeamFights from "../components/badminton-player/BadmintonPlayerTeamFights";
import gql from "graphql-tag";
import omitDeep from "omit-deep";
import {findPositions, isPlayingToHigh} from "../helpers";
import BadmintonPlayerTeamsMultiSelect from "../components/badminton-player/BadmintonPlayerTeamsMultiSelect";
import Draggable from "vuedraggable";

export default {
    name: "CheckTeamFight",
    components: {BadmintonPlayerTeamsMultiSelect, BadmintonPlayerTeamFights, BadmintonPlayerTeams, BadmintonPlayerClubs, Draggable},
    data() {
        return {
            clubId: null,
            playerTeams: [],
            season: null,
            teamFight: null,
            selectedTeamMatches: [],
            teams: [],
            playingToHigh: [],
            playingToHighInSquad: [],
            rankingList: null,
            activeStep: 0,
            fetchingAndValidating: false,
            done: false
        }
    },
    methods: {
        resolveLabel(player){
            let msg = ""
            if(this.isPlayingToHigh(player)){
                msg += "Gul: En eller flere spiller har mere end 50/100 point på NIVEAU-ranglisten, på et laverer hold"
            }
            if(this.isPlayingToHighInSquad(player)){
                msg += "\n Rød: En eller flere spiller har mere end 50 point på kategori-ranglisten, på et laverer hold";
            }
            return msg
        },
        isPlayingToHigh(player){
            return isPlayingToHigh(this.playingToHigh, player);
        },
        isPlayingToHighInSquad(player){
            return isPlayingToHigh(this.playingToHighInSquad, player);
        },
        nextStep() {
            this.activeStep = 1;
        },
        highlight: function (player) {
            let base = {}
            if (isPlayingToHigh(this.playingToHigh, player)) {
                base = {
                    ...{
                        'has-background-warning': true
                    }, ...base
                }
            }
            if (isPlayingToHigh(this.playingToHighInSquad, player)) {
                base = {
                    ...{
                        'has-background-danger': true
                    }, ...base
                }
            }
            return base;
        },
        findPositions,
        badmintonPlayerTeamMatchesImport() {
            this.fetchingAndValidating = true;
            this.$apollo.mutate(
                {
                    mutation: gql`mutation ($input: BadmintonPlayerTeamMatchInput!){
                        badmintonPlayerTeamMatchesImport(input: $input){
                            name
                            squad{
                              playerLimit
                              categories{
                                category
                                name
                                players{
                                  refId
                                  name
                                  gender
                                  points{
                                    points
                                    position
                                    category
                                    version
                                  }
                                }
                              }
                            }
                          }
                        }
                    `,
                    variables: {
                        input: {
                            clubId: parseInt(this.clubId),
                            leagueMatchIds: this.selectedTeamMatches.map((teamMatch) => (teamMatch.teamMatch.matchId)),
                            season: parseInt(this.season),
                            version: this.rankingList//"2020-08-01"
                        }
                    }
                }
            ).then(({data}) => {
                this.teams = data.badmintonPlayerTeamMatchesImport
                this.validate()
            }).catch(() => {
                this.fetchingAndValidating = false;
            })
        },
        validate() {
            this.$apollo.mutate(
                {
                    mutation: gql`mutation validateTeamMatch($input: [ValidateTeam!]!){
                      validateTeamMatch(input: $input){
                        name
                        id
                      }
                    }
                    `,
                    variables: {
                        input: omitDeep(this.teams, ['__typename'])
                    }
                }
            ).then(({data}) => {
                this.playingToHigh = data.validateTeamMatch
                this.done = true;
            }).finally(() => {
                this.fetchingAndValidating = false
            })
        },
        addTeamFight(teamMatch, team) {
            this.selectedTeamMatches.push(
                {
                    teamMatch: teamMatch,
                    team: team
                }
            );
        },
        goToStart() {
            this.done = false;
            this.activeStep = 0;
        },
        goToStepTeamsStep() {
            if (!(this.clubId === null || this.season === null)) {
                this.activeStep = 1;
            }
        },
        clearTeams() {
            this.playerTeams = [];
        },
        clearTeamFights() {
            this.selectedTeamMatches = [];
        }
    }
}
</script>

<style scoped>

</style>
