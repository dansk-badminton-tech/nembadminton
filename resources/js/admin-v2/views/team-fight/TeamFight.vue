<template>
    <div dusk="team-fight-edit-page">
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="true">
            {{ name || `Holdrunde nr. ${round}` }}
            <template v-slot:subtitle>
                <div class="is-flex is-align-items-center has-text-grey">
                    <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                    <span>Dato: {{ gameDate.toLocaleDateString('da-DK') }}</span>
                    <template v-if="version">
                        <b-icon icon="format-list-numbered" size="is-small" class="ml-3 mr-1"></b-icon>
                        <span>Rangliste: {{ timeToMonth(version) }}</span>
                    </template>
                </div>
            </template>
            <template v-slot:right>
                <b-button dusk="team-round-settings-button" icon-left="pencil" @click="openSettingsModal">Rediger</b-button>
                <b-button class="ml-2 is-pulled-right" icon-right="refresh" @click="refreshTeam"
                          alt="Genindlæs holdrunden"></b-button>
            </template>
        </hero-bar>
        <section class="section is-main-section">
            <b-loading :active="$apollo.loading || this.updating" :can-cancel="true" :is-full-page="true"></b-loading>
            <div class="is-flex is-align-items-center mb-4">
                <b-dropdown aria-role="list" class="mr-2">
                    <template #trigger="{ active }">
                        <button class="button is-link" dusk="team-round-share-button">
                            <span>Del</span>
                            <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                        </button>
                    </template>
                    <b-dropdown-item aria-role="listitem" dusk="team-round-csv-export-option" @click="openExportCsvModal">
                        <b-icon icon="file-export"></b-icon>
                        CSV
                    </b-dropdown-item>
                    <b-dropdown-item aria-role="listitem" dusk="team-round-share-link-option" @click="openLinkSharingModal">
                        <b-icon icon="share"></b-icon>
                        Link
                    </b-dropdown-item>
                </b-dropdown>
                <b-button
                    class="ml-2"
                    icon-left="email-fast"
                    dusk="send-team-notification-button"
                    @click="notify"
                >
                    Send hold til spillere
                </b-button>
            </div>
            <hr/>
            <div class="columns">
                <div class="column is-6">
                    <div class="is-flex is-justify-content-space-between is-align-items-flex-start is-flex-wrap-wrap mb-3" style="gap: 0.75rem;">
                        <div>
                            <h1 class="title">Søg på spiller</h1>
                            <h2 class="subtitle">{{
                                    hasMultipleClubs
                                        ? 'Klubber:'
                                        : 'Klub:'
                                }} {{ clubsNames }}
                                <router-link class="is-size-6"
                                             :to="{name: 'my-clubhouse', params: {clubhouseId: this.clubhouseId}, hash: '#add-clubs'}">
                                    (tilføj ekstra klub)
                                </router-link>
                            </h2>
                        </div>
                        <b-button dusk="open-add-member-button" icon-left="plus" @click="openAddMemberModal()">
                            Opret spiller
                        </b-button>
                    </div>
                    <PlayersListSearch :clubhouse-id="clubhouseId" :loading="session.saving"
                                        :add-player="addPlayerToNextCategory" :team-round-id="this.teamRoundId"
                                        :scenario-id="session.scenarioId"
                                        :version="new Date(version)" :game-date="gameDate"
                                       @open-add-member="openAddMemberModal"/>
                </div>
                <div class="column is-6 container">
                    <div class="is-flex is-justify-content-space-between is-align-items-flex-start is-flex-wrap-wrap mb-3" style="gap: 0.75rem;">
                        <div>
                            <div class="is-flex is-align-items-center is-flex-wrap-wrap" style="gap: 0.5rem;">
                                <h1 class="title mb-0">Holdene i holdrunden</h1>
                                <b-dropdown
                                    v-if="hasMultipleScenarios"
                                    aria-role="list"
                                    dusk="scenario-selector-dropdown"
                                >
                                    <template #trigger="{ active }">
                                        <button class="button" :class="isCurrentScenarioDraft ? 'is-warning is-light' : 'is-success is-light'">
                                            <span class="mr-1">{{ isCurrentScenarioDraft ? '🟡' : '🟢' }}</span>
                                            <span>{{ currentScenarioLabel }}</span>
                                            <b-icon size="is-small" :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                                        </button>
                                    </template>
                                    <b-dropdown-item
                                        v-for="scenario in availableScenarios"
                                        :key="scenario.id ?? 'official'"
                                        aria-role="listitem"
                                        :class="{ 'is-active': String(currentScenario?.id) === String(scenario.id) }"
                                        @click="selectScenario(scenario)"
                                    >
                                        <span class="mr-2">{{ scenario.isOfficial ? '🟢' : '🟡' }}</span>
                                        <span>{{ scenario.name }}</span>
                                        <span class="has-text-grey ml-1">({{ scenario.isOfficial ? 'Officiel' : 'Udkast' }})</span>
                                    </b-dropdown-item>
                                </b-dropdown>
                            </div>
                            <h2 class="subtitle mt-1">Træk spillerne rundt ved drag-and-drop</h2>
                        </div>
                        <div class="is-flex is-flex-direction-column is-align-items-flex-end">
                            <b-button
                                icon-left="source-branch"
                                dusk="create-scenario-button"
                                @click="promptCreateScenario"
                            >
                                Nyt scenarie
                            </b-button>
                        </div>
                    </div>

                    <b-message
                        v-if="isCurrentScenarioDraft"
                        type="is-warning"
                        :closable="false"
                        class="mb-4"
                        dusk="scenario-draft-warning-banner"
                    >
                        <div class="is-flex is-justify-content-space-between is-align-items-center is-flex-wrap-wrap" style="gap: 0.75rem;">
                            <div class="is-flex is-align-items-center">
                                <b-icon icon="alert" class="mr-2"></b-icon>
                                <span>
                                    Du redigerer udkastet <strong>"{{ currentScenario?.name }}"</strong>. Spillere ser fortsat den officielle opstilling.
                                </span>
                            </div>
                            <div class="buttons are-small mb-0">
                                <b-button
                                    type="is-success"
                                    icon-left="check-circle"
                                    dusk="promote-scenario-button"
                                    @click="promptPromoteScenario"
                                >
                                    Gør til officiel
                                </b-button>
                                <b-button
                                    icon-left="pencil"
                                    dusk="rename-scenario-button"
                                    @click="promptRenameScenario(currentScenario)"
                                >
                                    Omdøb
                                </b-button>
                                <b-button
                                    type="is-danger"
                                    icon-left="delete"
                                    dusk="delete-scenario-button"
                                    @click="promptDeleteScenario(currentScenario)"
                                >
                                    Slet udkast
                                </b-button>
                            </div>
                        </div>
                    </b-message>

                    <CheckPanel :statuses="checkStatuses" :requests="checkRequests"
                                @toggle="onCheckSwitch" @retry="validate" @revalidate="refreshTeam"/>
                    <TeamTable :confirm-delete="deleteTeam"
                               :delete-player="deletePlayerFromCategory"
                               :add-player="addPlayer"
                               :move-squad-order-down="moveSquadOrderDown"
                               :move-squad-order-up="moveSquadOrderUp"
                               :player-move="playerMove"
                               :playing-to-high="playingToHighList"
                               :playing-to-high-in-squad="playingToHighSquadList"
                               :squads="teamRound.squads"
                               :scenario-id="session.scenarioId"
                               :team-round-id="teamRoundId"
                               :clubhouse-id="clubhouseId"
                               :season-id="teamRound?.season?.id"
                               :used-team-ids="usedTeamIds"
                               :teams-base-validations="validateBasicSquads"
                               :too-young-players="tooYoungPlayers"
                               :season-start-year="teamRound.seasonStartYear"
                               :version="new Date(version)"
                               :loading="session.saving"
                    />
                    <hr>
                    <AddTeamsButtons :team-round-date="gameDate" :team-round-number="round" :season-id="teamRound?.season?.id"
                                     :clubhouse-id="clubhouseId" :existing-squad-count="teamRound.squads.length"
                                     :used-team-ids="usedTeamIds"/>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import gql from "graphql-tag"
import {computed} from "vue"
import {
    containsMen,
    containsWomen, extractErrorMessages,
    isMensDouble,
    isMensSingle,
    isMixDouble,
    isWomenDouble,
    isWomensSingle
} from "@/helpers.js";
import TeamRoundQuery from "../../../queries/teamRound.graphql"
import ValidateReservesMutation from "../../../queries/validateReserves.graphql"
import ValidatePaperSquadsMutation from "../../../queries/validatePaperSquads.graphql"
import ValidateFinalRoundEligibilityMutation from "../../../queries/validateFinalRoundEligibility.graphql"
import ValidateQualificationEligibilityMutation from "../../../queries/validateQualificationEligibility.graphql"
import ValidatePlayoffParticipationMutation from "../../../queries/validatePlayoffParticipation.graphql"
import {checkStatuses} from "./checks/check-status.mjs"
import CheckPanel from "./CheckPanel.vue"
import {
    wrapInTeamAndSquads,
    wrapSquadsInTeamWithoutLeague,
    timeToMonth
} from "./helper";
import AddTeamsButtons from "./AddTeamsButtons.vue";
import ShareLinkModal from "./ShareLinkModal.vue";
import PlayersListSearch from "./PlayersListSearch.vue";
import RankingVersionSelect from "../common/RankingVersionSelect.vue";
import TeamTable from "./TeamTable.vue";
import {createLineupSession} from "./lineup-session.js";
import {createApolloLineupGateway} from "./lineup-gateway-apollo.js";
import TeamRoundSettingsModal from "./TeamRoundSettingsModal.vue";
import ValidateTeams from "./ValidateTeams.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import clubhouse from "../../../queries/clubhouse.gql";
import AddMemberModal from "@/views/team-fight/AddMemberModal.vue";
import ExportCsvModal from "./ExportCsvModal.vue";

// The toast for each failed LineupSession operation; the server's messages are added after it.
const LINEUP_EDIT_MESSAGES = {
    addPlayer: 'Kunne ikke tilføje spiller til holdet :(',
    removePlayer: 'Kunne ikke fjerne spilleren fra holdet :(',
    move: 'Kunne ikke flytte spilleren :(',
    addReserve: 'Kunne ikke tilføje reserven.',
    removeReserve: 'Kunne ikke fjerne reserven :(',
    createSquad: 'Kunne ikke tilføje holdet til holdopstillingen :(',
    updateSquad: 'Fejl :( Kunne ikke opdater holdet',
    moveSquadUp: 'Kunne ikke ændre sortering',
    moveSquadDown: 'Kunne ikke ændre sortering',
    deleteSquad: 'Kunne ikke slette holdet :(',
    createScenario: 'Kunne ikke oprette scenariet.',
    promoteScenario: 'Kunne ikke gøre scenariet officielt.',
    renameScenario: 'Kunne ikke omdøbe scenariet.',
    deleteScenario: 'Kunne ikke slette scenariet.'
}

// The session's onError has already shown the failure.
const shownByOnError = () => {}

export default {
    name: "TeamFight",
    components: {
        HeroBar,
        TitleBar,
        AddTeamsButtons,
        ShareLinkModal,
        PlayersListSearch,
        CheckPanel,
        RankingVersionSelect,
        TeamTable,
        TeamRoundSettingsModal,
        ValidateTeams
    },
    props: {
        teamRoundId: String
    },
    inject: {
        clubhouseId: {
            default: 0
        }
    },
    provide() {
        return {
            lineupSession: computed(() => this.session)
        }
    },
    computed: {
        hasMultipleClubs() {
            return this.clubhouse?.clubs?.length > 1;
        },
        clubsNames() {
            return this.clubhouse?.clubs?.map((club) => {
                return club.name1
            }).join(', ')
        },
        lineupKey() {
            return `${this.session.teamRoundId}:${this.session.scenarioId ?? ''}`
        },
        checkStatuses() {
            return checkStatuses({teamRound: this.teamRound, switches: this.checkSwitches, responses: this.checkResponses})
        },
        // The responses the team table marks players from.
        validateBasicSquads() {
            return this.checkResponses.basicSquads ?? []
        },
        playingToHighList() {
            return this.checkResponses.crossSquads ?? []
        },
        playingToHighSquadList() {
            return this.checkResponses.squads?.findings ?? []
        },
        tooYoungPlayers() {
            return this.checkResponses.tooYoung ?? []
        },
        usedTeamIds() {
            return (this.teamRound?.squads || [])
                .map((squad) => squad?.team?.id)
                .filter((id) => id !== null && id !== undefined);
        },
        hasMultipleScenarios() {
            return (this.availableScenarios || []).length > 1;
        },
        availableScenarios() {
            const scenarios = this.teamRound?.scenarios || [];
            const hasOfficialScenario = scenarios.some(s => s.isOfficial);

            if (hasOfficialScenario) {
                return scenarios;
            }

            return [
                {
                    id: null,
                    name: 'Officiel opstilling',
                    isOfficial: true
                },
                ...scenarios
            ];
        },
        currentScenario() {
            const list = this.availableScenarios;
            if (this.session.scenarioId !== null && this.session.scenarioId !== undefined) {
                const found = list.find(s => String(s.id) === String(this.session.scenarioId));
                if (found) {
                    return found;
                }
            }
            return list.find(s => s.isOfficial) || list[0] || null;
        },
        currentScenarioLabel() {
            if (!this.currentScenario) {
                return 'Indlæser...';
            }
            if (this.currentScenario.isOfficial) {
                return this.currentScenario.name.includes('(Officiel)')
                    ? this.currentScenario.name
                    : `${this.currentScenario.name} (Officiel)`;
            }
            return `${this.currentScenario.name} (Udkast)`;
        },
        isCurrentScenarioDraft() {
            return Boolean(this.currentScenario && !this.currentScenario.isOfficial);
        }
    },
    data() {
        return {
            titleStack: ['Admin', 'Holdrunde'],
            // Each Check request's last answer, keyed like CHECKS[].requests in checks/check-status.mjs; null or missing
            // until it answers.
            checkResponses: {},
            // Each Check request's state: 'loading', 'failed' or 'done'.
            checkRequests: {},
            // Tidligere hold and Kvalifikation start switched off, as before, until slice 5 (#345) remembers them.
            checkSwitches: {complete: true, finalRound: false, qualification: false},
            teamCount: 1,
            players: [],
            // Created here, so the teamRound query below starts with its key.
            session: this.createSession(),
            updating: false,
            gameDate: new Date(),
            version: null,
            round: null,
            name: '',
            showLinkSharing: false,
            teamRound: {
                squads: [],
                club: {}
            }
        }
    },
    watch: {
        teamRoundId() {
            this.session = this.createSession()
        },
        // Another Lineup: the last answers belong to the one before.
        lineupKey() {
            for (const name of Object.keys(this.checkRuns)) this.checkRuns[name]++
            this.checkResponses = {}
            this.checkRequests = {}
        }
    },
    created() {
        // The latest run of each Check request, so an answer a newer run has replaced is dropped. Not reactive.
        this.checkRuns = {}
    },
    apollo: {
        clubhouse: {
            query: clubhouse,
            variables() {
                return {
                    id: this.clubhouseId
                }
            }
        },
        teamRound: {
            query: TeamRoundQuery,
            variables() {
                return {
                    id: this.session.teamRoundId,
                    scenarioId: this.session.scenarioId
                }
            },
            result({data, loading}) {
                if (loading || !data?.teamRound) return
                this.gameDate = new Date(data.teamRound.gameDate);
                this.version = data.teamRound.version;
                this.name = data.teamRound.name;
                this.round = data.teamRound.round;
                this.validate()
            }
        }
    },
    methods: {
        openAddMemberModal(name = '') {
            this.$buefy.modal.open({
                props: {
                    version: new Date(this.version),
                    clubhouseId: this.clubhouseId,
                    initialName: typeof name === 'string' ? name : '',
                },
                events: {
                    close() {
                    }
                },
                canCancel: ["x"],
                component: AddMemberModal,
                hasModalCard: true,
                trapFocus: true
            })
        },
        timeToMonth,
        notify() {
            this.$router.push({name: 'team-fight-notify', params: {teamUUID: this.teamRoundId}})
        },
        refreshTeam() {
            return this.$apollo.queries.teamRound.refetch();
        },
        openLinkSharingModal() {
            this.$buefy.modal.open({
                component: ShareLinkModal,
                props: {
                    teamRoundId: this.teamRoundId
                },
                scroll: "keep",
                width: 640,
                events: {
                    close: () => {
                        this.$emit('input', false)
                    }
                }
            })
        },
        openExportCsvModal() {
            this.$buefy.modal.open({
                component: ExportCsvModal,
                hasModalCard: true,
                trapFocus: true,
                events: {
                    export: ({ includeCategories }) => {
                        this.exportToCSV(includeCategories);
                    }
                }
            });
        },
        openSettingsModal() {
            this.$buefy.modal.open({
                component: TeamRoundSettingsModal,
                props: {
                    teamRound: this.teamRound
                },
                scroll: "keep",
                events: {
                    close: () => {
                    },
                    save: () => {
                        this.refreshTeam();
                    }
                },
                width: 640
            })
        },
        promptCreateScenario() {
            const sourceName = this.currentScenario ? this.currentScenario.name : 'den officielle opstilling';
            this.$buefy.dialog.prompt({
                title: 'Nyt scenarie',
                message: `Indtast navn på det nye scenarie (kopieres fra "${sourceName}"):`,
                placeholder: 'F.eks. Plan B - Hvis Nikolaj er skadet',
                inputAttrs: {
                    maxlength: 255
                },
                trapFocus: true,
                confirmText: 'Opret',
                cancelText: 'Annuller',
                onConfirm: (name) => this.createScenario(name)
            })
        },
        createSession() {
            return createLineupSession({
                gateway: createApolloLineupGateway(this.$apollo.getClient()),
                teamRoundId: this.teamRoundId,
                onError: error => this.showLineupEditError(error)
            })
        },
        showLineupEditError(error) {
            const details = extractErrorMessages(error.cause?.graphQLErrors)
            this.$buefy.snackbar.open({
                duration: 5000,
                type: 'is-danger',
                queue: false,
                message: [LINEUP_EDIT_MESSAGES[error.operation], ...details].join(' ')
            })
        },
        selectScenario(scenario) {
            this.session.switchScenario(scenario ? scenario.id : null);
        },
        async createScenario(name) {
            if (!name || !name.trim()) {
                return;
            }
            this.updating = true;
            try {
                const scenario = await this.session.createScenario(name.trim());
                this.$buefy.toast.open({
                    message: `Scenariet "${scenario.name}" blev oprettet.`,
                    type: 'is-success'
                });
            } catch (e) {
                shownByOnError(e);
            } finally {
                this.updating = false;
            }
        },
        promptPromoteScenario() {
            if (!this.currentScenario || this.currentScenario.isOfficial) {
                return;
            }
            const name = this.currentScenario.name;
            this.$buefy.dialog.confirm({
                title: 'Gør til officiel opstilling',
                message: `Er du sikker på, at du vil gøre "${name}" til den officielle holdopstilling? Den nuværende officielle opstilling bevares som et udkast.`,
                confirmText: 'Gør officiel',
                cancelText: 'Annuller',
                type: 'is-success',
                hasIcon: true,
                icon: 'check-circle',
                onConfirm: () => this.promoteCurrentScenario()
            });
        },
        async promoteCurrentScenario() {
            if (!this.currentScenario || !this.currentScenario.id) {
                return;
            }
            const name = this.currentScenario.name;
            this.updating = true;
            try {
                await this.session.promoteScenario();
                this.$buefy.toast.open({
                    message: `"${name}" er nu den officielle holdopstilling.`,
                    type: 'is-success'
                });
            } catch (e) {
                shownByOnError(e);
            } finally {
                this.updating = false;
            }
        },
        promptRenameScenario(scenario) {
            if (!scenario || scenario.isOfficial) {
                return;
            }
            this.$buefy.dialog.prompt({
                title: 'Omdøb scenarie',
                message: `Indtast nyt navn for scenariet "${scenario.name}":`,
                placeholder: 'F.eks. Plan B',
                inputAttrs: {
                    maxlength: 255,
                    value: scenario.name
                },
                trapFocus: true,
                confirmText: 'Omdøb',
                cancelText: 'Annuller',
                onConfirm: (name) => this.renameScenario(name)
            });
        },
        // Renames the open Scenario.
        async renameScenario(name) {
            if (!name || !name.trim()) {
                return;
            }
            this.updating = true;
            try {
                await this.session.renameScenario(name.trim());
                this.$buefy.toast.open({
                    message: `Scenariet blev omdøbt til "${name.trim()}".`,
                    type: 'is-success'
                });
            } catch (e) {
                shownByOnError(e);
            } finally {
                this.updating = false;
            }
        },
        promptDeleteScenario(scenario) {
            if (!scenario || scenario.isOfficial) {
                return;
            }
            const name = scenario.name;
            this.$buefy.dialog.confirm({
                title: 'Slet scenarie',
                message: `Er du sikker på, at du vil slette udkastet "${name}"? Alle holdopstillinger i dette udkast vil gå tabt.`,
                confirmText: 'Slet',
                cancelText: 'Annuller',
                type: 'is-danger',
                hasIcon: true,
                icon: 'delete',
                onConfirm: () => this.deleteScenario(scenario)
            });
        },
        // Deletes the open Scenario, which is the one passed in.
        async deleteScenario(scenario) {
            if (!scenario || !scenario.id) {
                return;
            }
            this.updating = true;
            try {
                await this.session.deleteScenario();
                this.$buefy.toast.open({
                    message: `Udkastet "${scenario.name}" blev slettet.`,
                    type: 'is-success'
                });
            } catch (e) {
                shownByOnError(e);
            } finally {
                this.updating = false;
            }
        },
        openLinkSharingCancellationModel() {
            this.$router.push({name: 'cancellation-redirect'})
        },
        // A Check's "Kontrollér dette tjek" switch. Not remembered yet (slice 5, #345).
        onCheckSwitch(key, value) {
            this.checkSwitches = {...this.checkSwitches, [key]: value}
            if (value && key !== 'complete') this.checkResponses = {...this.checkResponses, [key]: null}
            this.validate()
        },
        playerMove(event, player, sourceSquad, sourceCategory, targetSquad, targetCategory) {
            return this.session.move(player, this.place(targetSquad, targetCategory)).catch(shownByOnError)
        },
        // Where a player is added: the Category, on the Squad's Ranking List or else the TeamRound's.
        place(squad, category) {
            return {categoryId: category.id, version: squad.version ? squad.version : this.version}
        },
        exportToCSV(includeCategories = true) {
            this.$apollo.query({
                query: gql`
                    query exportToCSV($teamRoundId: ID!, $includeCategories: Boolean){
                        export(teamRoundId: $teamRoundId, includeCategories: $includeCategories)
                    }
                `,
                variables: {
                    teamRoundId: this.teamRoundId,
                    includeCategories: includeCategories
                },
                fetchPolicy: "network-only"
            }).then(({data}) => {
                let file_path = data.export;
                let a = document.createElement('A');
                a.href = file_path;
                a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

            }).catch((error) => {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-danger',
                        message: `Kunne ikke download CSV :(`
                    })
            })
        },
        focusNext(player) {
            const element = document.querySelector('[data-player-id-input="' + player.id + '"]')
            const inputs = document.querySelectorAll('input')
            const index = Array.from(inputs).indexOf(element) + 1
            if (inputs[index] !== undefined) {
                inputs[index].focus()
            }
        },
        addPlayer(squad, category, player) {
            return this.session.addPlayer({...this.place(squad, category), refId: player.refId})
                .then((member) => {
                    setTimeout(() => {
                        this.focusNext(member)
                    }, 100);
                })
                .catch(shownByOnError)
        },
        deletePlayerFromCategory(squad, category, player) {
            return this.session.removePlayer(player).catch(shownByOnError)
        },
        // Runs one Check request and keeps its answer in checkResponses. An answer that a newer run of the same request
        // has replaced is dropped. A failed request is a panel state ("Prøv igen"), so it shows no snackbar.
        runCheckRequest(name, fetch) {
            const run = (this.checkRuns[name] ?? 0) + 1
            this.checkRuns[name] = run
            this.checkRequests = {...this.checkRequests, [name]: 'loading'}
            const current = () => this.checkRuns[name] === run
            return fetch()
                .then(response => {
                    if (!current()) return
                    if (response === undefined || response === null) throw new Error(`No answer from ${name}`)
                    this.checkResponses = {...this.checkResponses, [name]: response}
                    this.checkRequests = {...this.checkRequests, [name]: 'done'}
                })
                .catch(() => {
                    if (!current()) return
                    // The last answer is outdated now; the panel offers "Prøv igen" instead.
                    this.checkResponses = {...this.checkResponses, [name]: null}
                    this.checkRequests = {...this.checkRequests, [name]: 'failed'}
                })
        },
        // A request the panel doesn't need: its Check doesn't apply, is switched off or is waiting.
        skipCheckRequest(name) {
            this.checkRuns[name] = (this.checkRuns[name] ?? 0) + 1
            this.checkResponses = {...this.checkResponses, [name]: null}
            const {[name]: _skipped, ...requests} = this.checkRequests
            this.checkRequests = requests
        },
        // The Check Status as far as it is known from these responses: without any, Gælder ikke, Slået fra or null.
        knownStatus(key, responses = {}) {
            return checkStatuses({teamRound: this.teamRound, switches: this.checkSwitches, responses})
                .find(check => check.key === key).status
        },
        mutationData(mutation, variables, field) {
            return this.$apollo.mutate({mutation, variables}).then(({data}) => data?.[field])
        },
        // Runs every Check that applies and is switched on. Niveau and Kategori wait for Hold fuldendt, unless it is
        // switched off.
        validate() {
            const squads = this.teamRound.squads
            const lineup = {teamRoundId: this.teamRoundId, scenarioId: this.session.scenarioId}
            const optional = {
                reserves: () => this.mutationData(ValidateReservesMutation, {input: wrapInTeamAndSquads(squads)}, 'validateReserves'),
                paperSquads: () => this.mutationData(ValidatePaperSquadsMutation, {teamRoundId: this.teamRoundId, input: wrapInTeamAndSquads(squads)}, 'validatePaperSquads'),
                finalRound: () => this.mutationData(ValidateFinalRoundEligibilityMutation, lineup, 'validateFinalRoundEligibility'),
                qualification: () => this.mutationData(ValidateQualificationEligibilityMutation, lineup, 'validateQualificationEligibility'),
                playoff: () => this.mutationData(ValidatePlayoffParticipationMutation, lineup, 'validatePlayoffParticipation')
            }
            for (const [name, fetch] of Object.entries(optional)) {
                if (['not-applicable', 'switched-off'].includes(this.knownStatus(name))) this.skipCheckRequest(name)
                else this.runCheckRequest(name, fetch)
            }
            this.validateTooYoungPlayers()
            const basicSquads = this.runCheckRequest('basicSquads', () => this.mutationData(gql`
                mutation validateBasicSquads($input: [ValidateTeam!]!){
                    validateBasicSquads(input: $input){
                        index
                        spotsFulfilled
                    }
                }
            `, {input: wrapInTeamAndSquads(squads)}, 'validateBasicSquads'))
            if (this.checkSwitches.complete === false) {
                this.validateSquadOrder()
                return
            }
            basicSquads.then(() => {
                if (this.checkRequests.basicSquads !== 'done') return
                if (this.knownStatus('level', {basicSquads: this.checkResponses.basicSquads}) === 'waiting') {
                    this.skipCheckRequest('squads')
                    this.skipCheckRequest('crossSquads')
                } else {
                    this.validateSquadOrder()
                }
            })
        },
        // Kategori (validateSquads) and Niveau (validateTeamRoundCrossSquads).
        validateSquadOrder() {
            this.runCheckRequest('squads', () => this.mutationData(gql`
                mutation validateSquads($input: [ValidateTeam!]!){
                    validateSquads(input: $input){
                        playersWithoutPoints {
                            squadIndex
                            refId
                            name
                            category
                        }
                        findings {
                            name
                            id
                            refId
                            category
                            gender
                            isYouthPlayer
                            hasYouthPlayerPartner
                            belowPlayer {
                                name
                                id
                                refId
                            }
                        }
                    }
                }
            `, {input: wrapSquadsInTeamWithoutLeague(this.teamRound.squads)}, 'validateSquads'))
            // The server reads the lineup itself, with the points on the TeamRound's ranking (§38 stk. 4).
            this.runCheckRequest('crossSquads', () => this.mutationData(gql`
                mutation validateTeamRoundCrossSquads($teamRoundId: ID!, $scenarioId: ID){
                    validateTeamRoundCrossSquads(teamRoundId: $teamRoundId, scenarioId: $scenarioId){
                        name
                        id
                        refId
                        isYouthPlayer
                        belowPlayer {
                            name
                            id
                            refId
                            category
                            balance
                        }
                    }
                }
            `, {teamRoundId: this.teamRoundId, scenarioId: this.session.scenarioId}, 'validateTeamRoundCrossSquads'))
        },
        validateTooYoungPlayers() {
            // Without the Season's start year the age can't be told; as before, no player is marked.
            if (!this.teamRound.seasonStartYear) {
                this.runCheckRequest('tooYoung', () => Promise.resolve([]))
                return
            }
            this.runCheckRequest('tooYoung', () => this.mutationData(gql`
                mutation validateTooYoungPlayers($input: [ValidateTeam!]!, $seasonStartYear: Int!){
                    validateTooYoungPlayers(input: $input, seasonStartYear: $seasonStartYear){
                        id
                        refId
                        name
                    }
                }
            `, {input: wrapInTeamAndSquads(this.teamRound.squads), seasonStartYear: this.teamRound.seasonStartYear}, 'validateTooYoungPlayers'))
        },
        deleteTeam(targetSquad) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette hold ' + (this.teamRound.squads.indexOf(targetSquad) + 1) + '?',
                    onConfirm: () => this.session.deleteSquad(targetSquad).catch(shownByOnError)
                })
        },
        addedPlayerNotification(squadIndex, category) {
            this.$buefy.snackbar.open(
                {
                    duration: 3000,
                    type: 'is-success',
                    queue: false,
                    message: 'Tilføjet til Hold ' + (squadIndex + 1) + ' i ' + category
                })
        },
        // The first Category, Squad by Squad, with a free place for the player's gender.
        findFreePlace(player) {
            for (const [index, squad] of this.teamRound.squads.entries()) {
                for (const category of squad.categories) {
                    if (this.hasFreePlace(category, player)) {
                        return {index, squad, category}
                    }
                }
            }
            return null
        },
        hasFreePlace(category, player) {
            const taken = category.players.length
            if (isWomenDouble(category)) {
                return taken < 2 && player.gender === 'WOMEN'
            }
            if (isMensDouble(category)) {
                return taken < 2 && player.gender === 'MEN'
            }
            if (isMixDouble(category)) {
                return taken === 0
                    || (taken === 1 && containsWomen(category) && player.gender === 'MEN')
                    || (taken === 1 && containsMen(category) && player.gender === 'WOMEN')
            }
            if (isMensSingle(category)) {
                return taken < 1 && player.gender === 'MEN'
            }
            if (isWomensSingle(category)) {
                return taken < 1 && player.gender === 'WOMEN'
            }
            return false
        },
        addPlayerToNextCategory(player) {
            const place = this.findFreePlace(player)
            if (place === null) {
                this.$buefy.snackbar.open(
                    {
                        duration: 3000,
                        type: 'is-danger',
                        queue: false,
                        message: `Kunne ikke finde en ledig plads på nogle hold`
                    })
                return Promise.resolve()
            }
            const {index, squad, category} = place
            return this.session.addPlayer({...this.place(squad, category), refId: player.refId})
                .then(() => this.addedPlayerNotification(index, category.name))
                .catch(shownByOnError)
        },
        moveSquadOrderUp(squad) {
            return this.session.moveSquadUp(squad).catch(shownByOnError)
        },
        moveSquadOrderDown(squad) {
            return this.session.moveSquadDown(squad).catch(shownByOnError)
        },
    }
}
</script>
