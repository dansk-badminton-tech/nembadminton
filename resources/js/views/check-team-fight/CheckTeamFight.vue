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
                            <b-select v-model="season" expanded placeholder="Vælge sæson">
                                <option value="2019">2019/2020</option>
                                <option value="2020">2020/2021</option>
                                <option value="2021">2021/2022</option>
                                <option value="2022">2022/2023</option>
                                <option value="2023">2023/2024</option>
                            </b-select>
                        </b-field>
                    </b-step-item>
                    <b-step-item label="Hold">
                        <h1 class="title">Hold</h1>
                        <h2 class="subtitle">Vælge hvilke hold som skal være med i spillerunden.</h2>
                        <BadmintonPlayerTeamsMultiSelect v-model="playerTeams" :clubId="clubId" :season="season"
                                                         @input="clearTeamFights"/>
                    </b-step-item>
                    <b-step-item label="Kampe">
                        <h1 class="title">Rangliste</h1>
                        <h2 class="subtitle">§ 38. Den først offentliggjorte rangliste i en ny måned er gældende for
                            holdsætning fra den 10. i den pågældende måned til og med den 9. i den efterfølgende
                            måned. </h2>
                        <b-field>
                            <ranking-list-dropdown v-model="rankingList" :use-system-rankings="true" :season="season"></ranking-list-dropdown>
                        </b-field>
                        <h1 class="title">Hold kampe</h1>
                        <h2 class="subtitle">Vælge den specifikke hold kamp. Husk ranglisten skal passe med holdkamps
                            runden</h2>
                        <b-field v-for="(team, index) in playerTeams" :key="team.leagueGroupId+playerTeams.length"
                                 :label="team.name">
                            <BadmintonPlayerTeamFights v-model="selectedTeamMatches[index]" :clubId="clubId"
                                                       :player-team="team" :season="season"/>
                            <OptionalRanking
                            v-model="selectedVersionsForTeamMatches[index]" :season="season"></OptionalRanking>
                        </b-field>
                    </b-step-item>
                    <b-step-item label="Bekræft">
                        <h1 class="title">Hold sortering</h1>
                        <h2 class="subtitle">Sortering er vigtig når spillerunden skal tjekkes. Drag and Drop holdene
                            rundt eller via knapperne, så styrkeordenen passer</h2>
                        <b-table :data="castToArray(selectedTeamMatches)"
                                 :draggable="true"
                                 @dragstart="dragstart"
                                 @drop="drop"
                                 @dragover="dragover"
                                 @dragleave="dragleave">
                            <b-table-column
                                label="#"
                                width="20"
                                numeric
                                v-slot="props">
                                {{ props.index + 1 }}
                            </b-table-column>
                            <b-table-column
                                field="team.name"
                                label="Hold"
                                v-slot="props">
                                {{ props.row.team.name }}
                            </b-table-column>
                            <b-table-column
                                field="team.league"
                                label="Række"
                                v-slot="props">
                                {{ props.row.team.league }}
                            </b-table-column>
                            <b-table-column
                                field="teamMatch.teams"
                                label="Kamp"
                                v-slot="props">
                                {{ props.row.teamMatch.teams.join(' - ') }} {{ props.row.teamMatch.gameTime }}
                            </b-table-column>
                            <b-table-column
                                v-slot="props">
                                <b-button :disabled="props.index === 0" @click="moveUp(props.index)" type="is-success">
                                    Op
                                </b-button>
                                <b-button :disabled="maybeMoveDown(props.index)" @click="moveDown(props.index)" i
                                          type="is-success">Ned
                                </b-button>
                                <b-button tag="a" target="_blank"
                                          :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+season+',,,,,'+props.row.teamMatch.matchId+',,'"
                                          type="is-success">Se på BP
                                </b-button>
                            </b-table-column>
                        </b-table>
                        <hr/>
                        <b-field>
                            <b-checkbox v-model="sortingConfirmed">Holdene står i den rigtige sortering. (Flyt hold
                                rundt via Drag&Drop eller via knapperne)
                            </b-checkbox>
                        </b-field>
                        <b-button size="is-large mt-2" @click="badmintonPlayerTeamMatchesImport"
                                  :disabled="!sortingConfirmed">Tjek spillerunden
                        </b-button>
                        <b-message v-if="errorImporting" title="Fejl ved import" class="mt-2" type="is-danger">
                            En eller flere hold kunne ikke importeres. Prøv at tjek på badmintonplayer.dk om der er
                            indrapporteret spiller på alle holde?
                        </b-message>
                    </b-step-item>
                </template>
            </b-steps>
        </form>
        <b-button v-if="done" class="mb-2" @click="goToStart">Tjek nyt hold</b-button>
<!--                <b-button v-if="done" class="mb-2" @click="validate">Valider igen</b-button>-->
        <b-message v-if="done && !hasViolations" title="Fandt ingen overtrædelser" type="is-success">
            Fandt ingen fejl.
        </b-message>
        <b-message v-if="done && hasViolations" title="Fandt mulige overtrædelser" type="is-warning">
            Hold musen henover de farvede spillere for beskrivelse.
        </b-message>
        <div v-if="done" class="columns is-multiline">
            <div v-for="team in teams" class="column is-4">
                <h1 class="title">{{ team.name }}
                    <b-button class="is-pulled-right" tag="a" target="_blank"
                              :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+season+',,,,,'+team.leagueMatchId+',,'"
                              type="is-link">Se på BP
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
                            <b-tag v-if="isYoungPlayer(player, null)" >U17/U19</b-tag>
                        </b-tooltip>
                    </b-table-column>
                </b-table>
            </div>
        </div>
    </section>
</template>

<script>
import BadmintonPlayerClubs from "../../components/badminton-player/BadmintonPlayerClubs";
import BadmintonPlayerTeamFights from "../../components/badminton-player/BadmintonPlayerTeamFights";
import gql from "graphql-tag";
import omitDeep from "omit-deep";
import {
    findPositions,
    highlight as simpleHighlight,
    isPlayingToHighByBadmintonPlayerId, isYoungPlayer,
    resolveToolTip,
    swapObject
} from "../../helpers";
import BadmintonPlayerTeamsMultiSelect from "../../components/badminton-player/BadmintonPlayerTeamsMultiSelect";
import RankingListDropdown from "../../components/ranking-list-dropdown/RankingListDropDown";
import OptionalRanking from "./OptionalRanking";

export default {
    name: "CheckTeamFight",
    components: {
        OptionalRanking,
        RankingListDropdown,
        BadmintonPlayerTeamsMultiSelect,
        BadmintonPlayerTeamFights,
        BadmintonPlayerClubs
    },
    data() {
        return {
            columns: [
                {
                    field: 'team.name',
                    label: 'Hold',
                }
            ],
            clubId: null,
            playerTeams: [],
            season: null,
            teamFight: null,
            selectedTeamMatches: {},
            selectedVersionsForTeamMatches: [],
            teams: [],
            playingToHigh: [],
            playingToHighInSquad: [],
            rankingList: null,
            activeStep: 0,
            fetchingAndValidating: false,
            done: false,
            sortingConfirmed: false,
            draggingRow: null,
            draggingRowIndex: null,
            draggingColumn: null,
            draggingColumnIndex: null,
            errorImporting: false
        }
    },
    computed: {
        hasViolations() {
            return this.playingToHigh.length > 0 || this.playingToHighInSquad.length > 0;
        }
    },
    methods: {
        isYoungPlayer,
        maybeMoveDown(index) {
            return this.castToArray(this.selectedTeamMatches).length - 1 === index
        },
        moveUp(index) {
            swapObject(this.selectedTeamMatches, index, index - 1)
        },
        moveDown(index) {
            swapObject(this.selectedTeamMatches, index, index + 1)
        },
        castToArray(object) {
            return Object.values(object)
        },
        dragstart(payload) {
            this.draggingRow = payload.row
            this.draggingRowIndex = payload.index
            payload.event.dataTransfer.effectAllowed = 'copy'
        },
        dragover(payload) {
            payload.event.dataTransfer.dropEffect = 'copy'
            payload.event.target.closest('tr').classList.add('is-selected')
            payload.event.preventDefault()
        },
        dragleave(payload) {
            payload.event.target.closest('tr').classList.remove('is-selected')
            payload.event.preventDefault()
        },
        drop(payload) {
            payload.event.target.closest('tr').classList.remove('is-selected')
            const droppedOnRowIndex = payload.index
            swapObject(this.selectedTeamMatches, this.draggingRowIndex, droppedOnRowIndex)
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
        nextStep() {
            this.activeStep = 1;
        },
        highlight(player, category) {
            return simpleHighlight(this.playingToHigh, this.playingToHighInSquad, player, category)
        },
        findPositions,
        badmintonPlayerTeamMatchesImport() {
            this.fetchingAndValidating = true;
            this.errorImporting = false;
            this.$apollo.mutate(
                {
                    query: gql`query ($input: BadmintonPlayerTeamMatchInput!){
                        badmintonPlayerTeamMatchesImport(input: $input){
                            name
                            leagueMatchId
                            squad{
                              playerLimit
                              league
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
                                    vintage
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
                            leagueMatches: this.castToArray(this.selectedTeamMatches).map((teamMatch, index) => {
                                return {
                                    id: teamMatch.teamMatch.matchId,
                                    teamNameHint: teamMatch.team.name,
                                    league: teamMatch.team.league,
                                    version: (typeof this.selectedVersionsForTeamMatches[index] === 'undefined' ? null : this.selectedVersionsForTeamMatches[index])
                                }
                            }),
                            season: parseInt(this.season),
                            version: this.rankingList
                        }
                    }
                }
            ).then(({data}) => {
                this.teams = data.badmintonPlayerTeamMatchesImport
                this.done = true
                this.validate()
            }).catch(() => {
                this.$buefy.toast.open({
                    duration: 5000,
                    message: `Et eller flere hold kunne ikke hentes`,
                    position: 'is-bottom',
                    type: 'is-danger'
                })
                this.errorImporting = true;
                this.fetchingAndValidating = false;
            })
        },
        validateCrossSquads() {
            let teamsClone = JSON.parse(JSON.stringify(this.teams));
            teamsClone = omitDeep(teamsClone, ['__typename', 'leagueMatchId'])
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
            }).finally(() => {
                this.fetchingAndValidating = false
            })
        },
        validateSquads() {
            let teamsSquadCheck = JSON.parse(JSON.stringify(this.teams));
            teamsSquadCheck = omitDeep(teamsSquadCheck, ['__typename', 'leagueMatchId', 'league'])

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
            }).finally(() => {
                this.fetchingAndValidating = false
            })
        },
        validate() {
            this.validateCrossSquads()
            this.validateSquads()
        },
        goToStart() {
            this.done = false;
            this.activeStep = 0;
        },
        clearTeams() {
            this.clearTeamFights()
            this.clearSelectedVersionsForTeamMatches()
            this.playerTeams = [];
        },
        clearSelectedVersionsForTeamMatches(){
            this.selectedVersionsForTeamMatches = [];
        },
        clearTeamFights() {
            this.selectedTeamMatches = {};
        }
    }
}
</script>

