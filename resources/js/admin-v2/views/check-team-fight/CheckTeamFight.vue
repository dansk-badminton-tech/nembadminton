<template>
    <section>
        <b-loading v-model="fetchingAndValidating" :is-full-page="true"></b-loading>
        <form v-if="!done">
            <b-steps v-model="activeStep">
                <template>
                    <b-step-item label="Basis">
                        <b-field label="Klub">
                            <BadmintonPlayerClubs v-model="clubId" @input="clearTeams"/>
                        </b-field>
                        <b-field label="Sæson">
                            <b-select v-model.number="season" expanded placeholder="Vælge sæson">
                                <option v-for="seasonOption in availableSeasons" :key="seasonOption.value" :value="seasonOption.value">
                                    {{ seasonOption.label }}
                                </option>
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
                        <b-button size="is-large mt-2" @click="badmintonPlayerTeamMatches"
                                  :disabled="!sortingConfirmed">Tjek spillerunden
                        </b-button>
                        <b-message v-if="errorImporting" title="Fejl ved import" class="mt-2" type="is-danger">
                            En eller flere hold kunne ikke importeres. Prøv at tjek på badmintonplayer.dk om der er
                            indrapporteret spiller på alle holde?
                            <br />
                            Fejl: {{errorImportingErrors}}
                        </b-message>
                    </b-step-item>
                </template>
            </b-steps>
        </form>
        <b-button v-if="done" class="mb-2" @click="goToStart">Tjek nyt hold</b-button>
        <b-button v-if="done" class="mb-2" @click="validate">Valider igen</b-button>
        <b-button v-if="done" class="mb-2" @click="badmintonPlayerTeamMatches">Hent igen</b-button>
        <b-checkbox v-if="done" v-model="markYouthAsError">Marker ungdom som fejl (kategori)</b-checkbox>
        <ValidationStatus v-if="done"
                          :hide-incomplete-team="true"
                          :invalid-category="errorSquadCheck"
                          :invalid-level="errorCrossSquadCheck"
                          :loading-category="validatingSquad"
                          :loading-level="validatingCrossSquad"
        ></ValidationStatus>
        <div v-if="done" class="columns is-multiline">
            <div v-for="team in teams" class="column is-6">
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
                            :active="isPlayingToHighInLevel(player) || isPlayingToHighInCategory(player, props.row.category)">
                            <template v-slot:content>
                                <span v-html="resolveLabel(player, props.row.category, team.squad.league)"></span>
                            </template>
                            <p v-bind:class="highlight(player, props.row.category)">{{ player.name }}
                                ({{findPositions(player, props.row.category) }})
                            </p>
                            <b-tag v-if="isYoungPlayer(player)">{{ageGroupLabel(player)}}</b-tag>
                        </b-tooltip>
                    </b-table-column>
                    <b-table-column width="30%" :td-attrs="(row, column) => resolveAttrs(row, column, team)" v-slot="props" field="results" label="Result">
                        {{ resolveResultDisplay(props.row.results, team)?.join(" ") }}
                    </b-table-column>
                </b-table>
            </div>
        </div>
    </section>
</template>

<script>
import BadmintonPlayerClubs from "../../components/badminton-player/BadmintonPlayerClubs.vue";
import BadmintonPlayerTeamFights from "../../components/badminton-player/BadmintonPlayerTeamFights.vue";
import gql from "graphql-tag";
import {
    ageGroupLabel,
    findPositions,
    highlight as simpleHighlight,
    isPlayingToHighByBadmintonPlayerId,
    isYoungPlayer,
    resolveToolTip,
    swapObject
} from "../../helpers";
import BadmintonPlayerTeamsMultiSelect from "../../components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue";
import RankingListDropdown from "../../components/ranking-list-dropdown/RankingListDropDown.vue";
import OptionalRanking from "./OptionalRanking.vue";
import ValidationStatus from "@/views/team-fight/ValidationStatus.vue";
import {filterYouthFromCategory, filterYouthFromLevel, hasInvalidCategory, hasInvalidLevel, wrapInTeamAndSquads, wrapSquadsInTeamWithoutLeague} from "../team-fight/helper";

export default {
    name: "CheckTeamFight",
    components: {
        ValidationStatus,
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
            season: (() => {
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth();
                // Only use current year as default if we're 6+ months into it
                return currentMonth >= 6 ? currentYear : currentYear - 1;
            })(),
            teamFight: null,
            selectedTeamMatches: {},
            selectedVersionsForTeamMatches: [],
            teams: [],
            playingToHighInLevel: [],
            playingToHighInCategory: [],
            rankingList: null,
            activeStep: 0,
            fetchingAndValidating: false,
            done: false,
            sortingConfirmed: false,
            draggingRow: null,
            draggingRowIndex: null,
            draggingColumn: null,
            draggingColumnIndex: null,
            errorImporting: false,
            errorImportingErrors: [],
            validatingCrossSquad: false,
            validatingSquad: false,
            markYouthAsError: false
        }
    },
    computed: {
        availableSeasons() {
            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth(); // 0-based (0 = January, 5 = June)
            const seasons = [];

            // Only include current year if we're 6+ months into it (July or later)
            const startYear = currentMonth >= 6 ? currentYear : currentYear - 1;

            // Generate 5 seasons: start year and 4 years back
            for (let i = 0; i < 5; i++) {
                const year = startYear - i;
                seasons.push({
                    value: year,
                    label: `${year}/${year + 1}`
                });
            }

            return seasons;
        },
        errorSquadCheck() {
            if(this.markYouthAsError){
                return this.currentPlayingToHighInCategory?.length > 0
            }
            return hasInvalidCategory(this.playingToHighInCategory)
        },
        errorCrossSquadCheck() {
            return hasInvalidLevel(this.playingToHighInLevel)
        },
        currentPlayingToHighInCategory(){
            return this.playingToHighInCategory
        },
        currentPlayingToHighInLevel(){
            return filterYouthFromLevel(this.playingToHighInLevel)
        }
    },
    methods: {
        ageGroupLabel,
        isYoungPlayer,
        resolveAttrs(row, column, team){
            let winnerSide = this.determineBadmintonMatchWinner(row.results);
            if(winnerSide === team.side){
                return {
                    class: 'is-success'
                }
            }
            if(winnerSide === 'UNKNOWN'){
                return {
                    class: 'is-info'
                }
            }
            return {
                class: 'is-danger'
            }
        },
        resolveResultDisplay(results, team){
            return results.map((result) => {
                if(result.homePoints === null){
                    return ''
                }
                if(team.side === 'HOME'){
                    return result.homePoints+'/'+result.guestPoints
                }else{
                    return result.guestPoints+'/'+result.homePoints
                }
            })
        },
        determineBadmintonMatchWinner(games) {
            let homeWins = 0;
            let guestWins = 0;

            for (let game of games) {
                const { homePoints, guestPoints } = game;

                // Skip if the game was not played
                if (homePoints === null || guestPoints === null) {
                    continue;
                }

                // Check for valid score
                if (homePoints < 0 || guestPoints < 0 || homePoints > 30 || guestPoints > 30) {
                    throw 'Invalid score found';
                }

                // Determine the winner of the game
                if ((homePoints >= 21 && homePoints - guestPoints >= 2) || homePoints === 30) {
                    homeWins++;
                } else if ((guestPoints >= 21 && guestPoints - homePoints >= 2) || guestPoints === 30) {
                    guestWins++;
                }
            }

            // Check if match winner is already determined
            if (homeWins === 2) {
                return 'HOME';
            } else if (guestWins === 2) {
                return 'GUEST';
            }

            // In case all three games are played without a winner
            return 'UNKNOWN';
        },
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
            return resolveToolTip(player, category, league, this.currentPlayingToHighInLevel, this.currentPlayingToHighInCategory)
        },
        isPlayingToHighInLevel(player) {
            return isPlayingToHighByBadmintonPlayerId(this.currentPlayingToHighInLevel, player);
        },
        isPlayingToHighInCategory(player, category) {
            return isPlayingToHighByBadmintonPlayerId(this.currentPlayingToHighInCategory, player, category);
        },
        nextStep() {
            this.activeStep = 1;
        },
        highlight(player, category) {
            return simpleHighlight(this.currentPlayingToHighInLevel, this.currentPlayingToHighInCategory, player, category, this.markYouthAsError)
        },
        findPositions,
        badmintonPlayerTeamMatches() {
            this.fetchingAndValidating = true;
            this.errorImporting = false;
            this.$apollo.query(
                {
                    query: gql`query ($input: BadmintonPlayerTeamMatchesInput!){
                        badmintonPlayerTeamMatches(input: $input){
                            name
                            leagueMatchId
                            side
                            squad{
                              playerLimit
                              league
                              categories{
                                category
                                name
                                results {
                                    homePoints
                                    guestPoints
                                }
                                players{
                                  refId
                                  name
                                  gender
                                  vintage
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
                        input:
                            {
                            clubId: parseInt(this.clubId),
                            leagueMatches: this.castToArray(this.selectedTeamMatches).map((teamMatch, index) => {
                                return {
                                    id: teamMatch.teamMatch.matchId,
                                    teamNameHint: teamMatch.team.name,
                                    league: teamMatch.team.league,
                                    version: (typeof this.selectedVersionsForTeamMatches[index] === 'undefined'
                                              ? null
                                              : this.selectedVersionsForTeamMatches[index])
                                }
                            }),
                            season: parseInt(this.season),
                            version: this.rankingList
                        }
                    }
                }
            ).then(({data}) => {
                this.teams = data.badmintonPlayerTeamMatches
                this.done = true
                this.validate()
            }).catch((error) => {
                this.fetchingAndValidating = false;
                this.$buefy.toast.open({
                                           duration: 5000,
                                           message: `Et eller flere hold kunne ikke hentes`,
                                           position: 'is-bottom',
                                           type: 'is-danger'
                                       })
                this.errorImporting = true;
                if (error.graphQLErrors){
                    this.errorImportingErrors = error.graphQLErrors.map((error) => {return error.message});
                }
            })
        },
        validateCrossSquads() {
            this.validatingCrossSquad = true
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
                        input: wrapInTeamAndSquads(this.teams.map(team => team.squad))
                    }
                }
            )
            .then(({data}) => {
                this.playingToHighInLevel = data.validateCrossSquads
            })
            .catch((error) => {
                this.$buefy.toast.open({
                    duration: 5000,
                    message: `Noget gik galt under valideringen af holdet (validateCrossSquads) <br/> ${error.graphQLErrors.map((error) => {return error.message}).join(', ')}`,
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            })
            .finally(() => {
                this.fetchingAndValidating = false
                this.validatingCrossSquad = false
            })
        },
        validateSquads() {
            this.validatingSquad = true
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
                        input: wrapSquadsInTeamWithoutLeague(this.teams.map(team => team.squad))
                    }
                }
            ).then(({data}) => {
                this.playingToHighInCategory = data.validateSquads
            })
            .catch((error) => {
                this.$buefy.toast.open({
                    duration: 5000,
                    message: `Noget gik galt under valideringen af holdet (validateSquads) <br/> ${error.graphQLErrors.map((error) => {return error.message}).join(', ')}`,
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            })
            .finally(() => {
                this.fetchingAndValidating = false
                this.validatingSquad = false
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
        clearSelectedVersionsForTeamMatches() {
            this.selectedVersionsForTeamMatches = [];
        },
        clearTeamFights() {
            this.selectedTeamMatches = {};
        }
    }
}
</script>

