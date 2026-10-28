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

                    <ValidationStatus :incomplete-team="resolveIncompleteTeam"
                                      :invalid-category="resolveInvalidCategory"
                                      :invalid-level="resolveInvalidLevel"
                                      :basic-squads="validateBasicSquads"
                                      :invalid-category-list="playingToHighSquadList"
                                      :invalid-level-list="playingToHighList"
                                      :reserve-findings="reserveFindings"
                                      :show-reserves="hasReserves"
                                      :paper-squad-validation="paperSquadValidation"
                                      :final-round-eligibility="finalRoundValidation.findings"
                                      :final-round-validation-status="showFinalRoundControl ? finalRoundValidation.status : null"
                                      :final-round-validation-enabled="finalRoundValidation.enabled"
                                      @update:finalRoundValidationEnabled="onFinalRoundValidationEnabledChange"
                                      :playoff-participation="playoffParticipationValidation.findings"
                                      :playoff-participation-status="showPlayoffParticipationControl ? playoffParticipationValidation.status : null"
                                      @revalidate="refreshTeam"
                                      :show-paper-squads="hasPaperSquad"
                                      :ignore-incomplete-team="ignoreIncompleteTeam"
                                      @update:ignoreIncompleteTeam="onIgnoreIncompleteTeamChange"/>
                    <QualificationEligibilityStatus v-if="showQualificationControl"
                                                    :validation="qualificationValidation"
                                                    @enabled-change="onQualificationValidationEnabledChange"
                                                    @revalidate="refreshTeam" />
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
                    <AddTeamsButtons :team-round-date="gameDate" :season-id="teamRound?.season?.id"
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
import QualificationEligibilityStatus from './QualificationEligibilityStatus.vue'
import ValidatePlayoffParticipationMutation from "../../../queries/validatePlayoffParticipation.graphql"
import {hasPaperSquad} from "./paper-squads.js"
import {showPlayoffParticipationControl} from "./playoff-participation.js"
import {createValidationState, resetValidation, runValidation, setValidationEnabled, showFinalRoundControl} from './final-round-validation.mjs'
import {
    hasInvalidCategory,
    hasInvalidLevel,
    wrapInTeamAndSquads,
    wrapSquadsInTeamWithoutLeague,
    timeToMonth
} from "./helper";
import AddTeamsButtons from "./AddTeamsButtons.vue";
import ShareLinkModal from "./ShareLinkModal.vue";
import PlayersListSearch from "./PlayersListSearch.vue";
import ValidationStatus from "./ValidationStatus.vue";
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
        ValidationStatus,
        QualificationEligibilityStatus,
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
        resolveIncompleteTeam() {
            if (this.ignoreIncompleteTeam) {
                return null
            }
            if (this.validateBasicSquads.length === 0) {
                return null
            }
            return this.validateBasicSquads.find(data => data.missingPlayerInCategory === true || data.spotsFulfilled === false) !== undefined
        },
        resolveInvalidCategory() {
            if (this.errorValidatingCategory) {
                return null
            }
            if (!this.canValidateSquads) {
                return null
            }
            return hasInvalidCategory(this.playingToHighSquadList)
        },
        resolveInvalidLevel() {
            if (this.errorValidatingLevel) {
                return null
            }
            if (!this.canValidateCrossSquads) {
                return null
            }
            return hasInvalidLevel(this.playingToHighList)
        },
        hasReserves() {
            return (this.teamRound?.squads || []).some(squad => squad.reserves?.length)
        },
        hasPaperSquad() {
            return hasPaperSquad(this.teamRound?.squads)
        },
        showFinalRoundControl() {
            return showFinalRoundControl(this.teamRound)
        },
        showQualificationControl() {
            if (this.teamRound?.round != null && Number(this.teamRound.round) !== 12) return false
            return (this.teamRound?.squads ?? []).some(squad => {
                if (squad.isPaperSquad) return false
                const tier = (squad.effectiveTier ?? '').trim()
                // Known excluded tiers stay hidden; unknown context is sent to the server for a cannot-verify result.
                return !/^(?:badmintonligaen\b|1\.\s*division\b|(?:\d+\.\s*)?serie\b|(?:københavns|sjællands|kreds)serien\b|lf-serien\b)/i.test(tier)
            })
        },
        showPlayoffParticipationControl() {
            return showPlayoffParticipationControl(this.teamRound)
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
            validateBasicSquads: [],
            playingToHighList: [],
            playingToHighSquadList: [],
            tooYoungPlayers: [],
            reserveFindings: [],
            paperSquadValidation: null,
            finalRoundValidation: createValidationState(),
            qualificationValidation: createValidationState(),
            // Unlike the final-round check it needs no Team links, so it runs without being switched on.
            playoffParticipationValidation: createValidationState(true),
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
            },
            ignoreIncompleteTeam: false,
            canValidateCrossSquads: false,
            canValidateSquads: false,
            errorValidatingCategory: false,
            errorValidatingLevel: false
        }
    },
    watch: {
        teamRoundId() {
            this.session = this.createSession()
        }
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
        onIgnoreIncompleteTeamChange(value) {
            this.ignoreIncompleteTeam = value
            this.validate()
        },
        onFinalRoundValidationEnabledChange(value) {
            setValidationEnabled(this.finalRoundValidation, value)
            if (value) this.refreshTeam()
        },
        onQualificationValidationEnabledChange(value) {
            setValidationEnabled(this.qualificationValidation, value)
            if (value) this.refreshTeam()
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
        validateSquads() {
            this.errorValidatingCategory = false;
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateSquads($input: [ValidateTeam!]!){
                            validateSquads(input: $input){
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
                    `,
                    variables: {
                        input: wrapSquadsInTeamWithoutLeague(this.teamRound.squads)
                    }
                })
                .then(({data}) => {
                    this.playingToHighSquadList = data.validateSquads;
                })
                .catch(({graphQLErrors}) => {
                    this.errorValidatingCategory = true;
                    const errorMessages = extractErrorMessages(graphQLErrors);
                    this.$buefy.snackbar.open(
                        {
                            duration: 5000,
                            type: 'is-danger',
                            queue: false,
                            message: 'Noget gik galt under intern valideringen af holdet. <br/><br /> ' + errorMessages.join(', ')
                        })
                })
        },
        validateCrossSquads() {
            this.errorValidatingLevel = false
            this.$apollo.mutate(
                {
                    mutation: gql`
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
                    `,
                    // The server reads the lineup itself, with the points on the TeamRound's ranking (§38 stk. 4).
                    variables: {
                        teamRoundId: this.teamRoundId,
                        scenarioId: this.session.scenarioId
                    }
                })
                .then(({data}) => {
                    this.playingToHighList = data.validateTeamRoundCrossSquads;
                })
                .catch((error) => {
                    this.errorValidatingLevel = true
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (crossSquadsValidate)`
                        })
                })
        },
        validateTooYoungPlayers() {
            if (!this.teamRound.seasonStartYear) {
                this.tooYoungPlayers = [];
                return;
            }
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateTooYoungPlayers($input: [ValidateTeam!]!, $seasonStartYear: Int!){
                            validateTooYoungPlayers(input: $input, seasonStartYear: $seasonStartYear){
                                id
                                refId
                                name
                            }
                        }
                    `,
                    variables: {
                        input: wrapInTeamAndSquads(this.teamRound.squads),
                        seasonStartYear: this.teamRound.seasonStartYear
                    }
                })
                .then(({data}) => {
                    this.tooYoungPlayers = data.validateTooYoungPlayers;
                })
                .catch(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validateTooYoungPlayers)`
                        })
                })
        },
        validateReserves() {
            this.$apollo.mutate(
                {
                    mutation: ValidateReservesMutation,
                    variables: {
                        input: wrapInTeamAndSquads(this.teamRound.squads)
                    }
                })
                .then(({data}) => {
                    this.reserveFindings = data.validateReserves;
                })
                .catch(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validateReserves)`
                        })
                })
        },
        validatePaperSquads() {
            if (!this.hasPaperSquad) {
                this.paperSquadValidation = null;
                return;
            }
            this.$apollo.mutate(
                {
                    mutation: ValidatePaperSquadsMutation,
                    variables: {
                        teamRoundId: this.teamRoundId,
                        input: wrapInTeamAndSquads(this.teamRound.squads)
                    }
                })
                .then(({data}) => {
                    this.paperSquadValidation = data.validatePaperSquads;
                })
                .catch(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validatePaperSquads)`
                        })
                })
        },
        validate() {
            this.validateFinalRoundEligibility();
            this.validateQualificationEligibility();
            this.validatePlayoffParticipation();
            this.validateTooYoungPlayers();
            this.validateReserves();
            this.validatePaperSquads();
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateBasicSquads($input: [ValidateTeam!]!){
                            validateBasicSquads(input: $input){
                                index
                                spotsFulfilled
                            }
                        }
                    `,
                    variables: {
                        input: wrapInTeamAndSquads(this.teamRound.squads)
                    }
                })
                .then(({data}) => {
                    this.validateBasicSquads = data.validateBasicSquads;
                    if (!this.resolveIncompleteTeam || this.ignoreIncompleteTeam) {
                        this.canValidateCrossSquads = true
                        this.canValidateSquads = true
                        this.validateSquads()
                        this.validateCrossSquads();
                    } else {
                        this.canValidateCrossSquads = false
                        this.canValidateSquads = false
                    }
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validateSquad)`
                        })
                })
        },
        validateFinalRoundEligibility() {
            if (!this.showFinalRoundControl) {
                resetValidation(this.finalRoundValidation)
                return
            }
            return runValidation(this.finalRoundValidation, async () => {
                const {data} = await this.$apollo.mutate({
                    mutation: ValidateFinalRoundEligibilityMutation,
                    variables: {
                        teamRoundId: this.teamRoundId,
                        scenarioId: this.session.scenarioId
                    }
                })
                return data?.validateFinalRoundEligibility
            })
        },
        validateQualificationEligibility() {
            if (!this.showQualificationControl) {
                resetValidation(this.qualificationValidation)
                return
            }
            return runValidation(this.qualificationValidation, async () => {
                const {data} = await this.$apollo.mutate({
                    mutation: ValidateQualificationEligibilityMutation,
                    variables: {teamRoundId: this.teamRoundId, scenarioId: this.session.scenarioId}
                })
                return data?.validateQualificationEligibility
            })
        },
        validatePlayoffParticipation() {
            if (!this.showPlayoffParticipationControl) {
                resetValidation(this.playoffParticipationValidation)
                return
            }
            return runValidation(this.playoffParticipationValidation, async () => {
                const {data} = await this.$apollo.mutate({
                    mutation: ValidatePlayoffParticipationMutation,
                    variables: {
                        teamRoundId: this.teamRoundId,
                        scenarioId: this.session.scenarioId
                    }
                })
                return data?.validatePlayoffParticipation
            })
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
