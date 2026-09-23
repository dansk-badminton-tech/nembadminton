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
                <b-button icon-left="pencil" @click="openSettingsModal">Rediger</b-button>
                <b-button class="ml-2 is-pulled-right" icon-right="refresh" @click="refreshTeam"
                          alt="Genindlæs holdrunden"></b-button>
            </template>
        </hero-bar>
        <section class="section is-main-section">
            <b-loading :active="$apollo.loading || this.updating" :can-cancel="true" :is-full-page="true"></b-loading>
            <div class="is-flex is-align-items-center mb-4">
                <b-dropdown aria-role="list" class="mr-2">
                    <template #trigger="{ active }">
                        <button class="button is-link">
                            <span>Del</span>
                            <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                        </button>
                    </template>
                    <b-dropdown-item aria-role="listitem" @click="openExportCsvModal">
                        <b-icon icon="file-export"></b-icon>
                        CSV
                    </b-dropdown-item>
                    <b-dropdown-item aria-role="listitem" @click="openLinkSharingModal">
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
                        <b-button icon-left="plus" @click="openAddMemberModal()">
                            Opret spiller
                        </b-button>
                    </div>
                    <PlayersListSearch :clubhouse-id="clubhouseId" :loading="saving"
                                        :add-player="addPlayerToNextCategory" :team-round-id="this.teamRoundId"
                                        :scenario-id="currentScenario?.id ?? null"
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
                        <b-button
                            icon-left="source-branch"
                            dusk="create-scenario-button"
                            @click="promptCreateScenario"
                        >
                            Nyt scenarie
                        </b-button>
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
                                      :ignore-incomplete-team="ignoreIncompleteTeam"
                                      @update:ignoreIncompleteTeam="onIgnoreIncompleteTeamChange"/>
                    <TeamTable :confirm-delete="deleteTeam"
                               :delete-player="deletePlayerFromCategory"
                               :add-player="addPlayer"
                               :update-squad="updateSquad"
                               :move-squad-order-down="moveSquadOrderDown"
                               :move-squad-order-up="moveSquadOrderUp"
                               :player-move="playerMove"
                               :playing-to-high="playingToHighList"
                               :playing-to-high-in-squad="playingToHighSquadList"
                               :squads="teamRound.squads"
                               :teams-base-validations="validateBasicSquads"
                               :version="new Date(version)"
                               :loading="saving"
                    />
                    <hr>
                    <AddTeamsButtons :team-round-id="teamRoundId" :team-round-date="gameDate" :season-id="teamRound?.season?.id"
                                     :clubhouse-id="clubhouseId" :existing-squad-count="teamRound.squads.length"
                                     :used-team-ids="usedTeamIds"/>
                    <p v-if="teamRound.squads.length === 0" class="help mt-3" dusk="setup-guide-link">
                        <router-link :to="{name: 'help-guide', params: {slug: 'opret-og-klargoer-en-holdrunde'}}">
                            Sådan tilføjer du hold og klargør holdrunden
                        </router-link>
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import gql from "graphql-tag"
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
import {emit as emitAppEvent} from '@/store/events'
import TeamRoundSettingsModal from "./TeamRoundSettingsModal.vue";
import ValidateTeams from "./ValidateTeams.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import clubhouse from "../../../queries/clubhouse.gql";
import AddMemberModal from "@/views/team-fight/AddMemberModal.vue";
import ExportCsvModal from "./ExportCsvModal.vue";

export default {
    name: "TeamFight",
    components: {
        HeroBar,
        TitleBar,
        AddTeamsButtons,
        ShareLinkModal,
        PlayersListSearch,
        ValidationStatus,
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
            if (this.selectedScenarioId !== null && this.selectedScenarioId !== undefined) {
                const found = list.find(s => String(s.id) === String(this.selectedScenarioId));
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
            teamCount: 1,
            players: [],
            saving: false,
            updating: false,
            selectedScenarioId: null,
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
                    id: this.teamRoundId,
                    scenarioId: this.selectedScenarioId
                }
            },
            result({data}) {
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
            this.$apollo.queries.teamRound.refetch();
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
        selectScenario(scenario) {
            this.selectedScenarioId = scenario ? scenario.id : null;
        },
        teamRoundRefetchQueries() {
            return [
                {
                    query: TeamRoundQuery,
                    variables: {
                        id: this.teamRoundId,
                        scenarioId: this.selectedScenarioId
                    }
                }
            ];
        },
        async createScenario(name) {
            if (!name || !name.trim()) {
                return;
            }
            this.updating = true;
            try {
                const sourceScenarioId = this.currentScenario?.id ? String(this.currentScenario.id) : null;
                const response = await this.$apollo.mutate({
                    mutation: gql`
                        mutation CreateScenario($teamRoundId: ID!, $name: String!, $sourceScenarioId: ID) {
                            createScenario(teamRoundId: $teamRoundId, name: $name, sourceScenarioId: $sourceScenarioId) {
                                id
                                name
                                isOfficial
                            }
                        }
                    `,
                    variables: {
                        teamRoundId: this.teamRoundId,
                        name: name.trim(),
                        sourceScenarioId: sourceScenarioId
                    },
                    refetchQueries: this.teamRoundRefetchQueries()
                });
                this.selectedScenarioId = response.data.createScenario.id;
                this.$buefy.toast.open({
                    message: `Scenariet "${response.data.createScenario.name}" blev oprettet.`,
                    type: 'is-success'
                });
            } catch (e) {
                this.$buefy.toast.open({
                    message: 'Kunne ikke oprette scenariet: ' + (e.message || e),
                    type: 'is-danger'
                });
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
            this.updating = true;
            try {
                const response = await this.$apollo.mutate({
                    mutation: gql`
                        mutation PromoteScenario($scenarioId: ID!) {
                            promoteScenario(scenarioId: $scenarioId) {
                                id
                                name
                                officialScenario {
                                    id
                                    name
                                    isOfficial
                                }
                                scenarios {
                                    id
                                    name
                                    isOfficial
                                }
                            }
                        }
                    `,
                    variables: {
                        scenarioId: String(this.currentScenario.id)
                    },
                    refetchQueries: this.teamRoundRefetchQueries()
                });
                this.$buefy.toast.open({
                    message: `"${this.currentScenario.name}" er nu den officielle holdopstilling.`,
                    type: 'is-success'
                });
            } catch (e) {
                this.$buefy.toast.open({
                    message: 'Kunne ikke gøre scenariet officielt: ' + (e.message || e),
                    type: 'is-danger'
                });
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
                onConfirm: (name) => this.renameScenario(scenario, name)
            });
        },
        async renameScenario(scenario, name) {
            if (!scenario || !scenario.id || !name || !name.trim()) {
                return;
            }
            this.updating = true;
            try {
                const response = await this.$apollo.mutate({
                    mutation: gql`
                        mutation RenameScenario($scenarioId: ID!, $name: String!) {
                            renameScenario(scenarioId: $scenarioId, name: $name) {
                                id
                                name
                                isOfficial
                            }
                        }
                    `,
                    variables: {
                        scenarioId: String(scenario.id),
                        name: name.trim()
                    },
                    refetchQueries: this.teamRoundRefetchQueries()
                });
                this.$buefy.toast.open({
                    message: `Scenariet blev omdøbt til "${response.data.renameScenario.name}".`,
                    type: 'is-success'
                });
            } catch (e) {
                this.$buefy.toast.open({
                    message: 'Kunne ikke omdøbe scenariet: ' + (e.message || e),
                    type: 'is-danger'
                });
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
        async deleteScenario(scenario) {
            if (!scenario || !scenario.id) {
                return;
            }
            this.updating = true;
            try {
                if (String(this.selectedScenarioId) === String(scenario.id)) {
                    this.selectedScenarioId = null;
                }
                await this.$apollo.mutate({
                    mutation: gql`
                        mutation DeleteScenario($scenarioId: ID!) {
                            deleteScenario(scenarioId: $scenarioId)
                        }
                    `,
                    variables: {
                        scenarioId: String(scenario.id)
                    },
                    refetchQueries: this.teamRoundRefetchQueries()
                });
                this.$buefy.toast.open({
                    message: `Udkastet "${scenario.name}" blev slettet.`,
                    type: 'is-success'
                });
            } catch (e) {
                this.$buefy.toast.open({
                    message: 'Kunne ikke slette scenariet: ' + (e.message || e),
                    type: 'is-danger'
                });
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
        playerMove(event, player, sourceSquad, sourceCategory, targetSquad, targetCategory) {
            this.deletePlayerFromCategory(sourceSquad, sourceCategory, player).then(() => {
                this.addPlayerToCategory(targetSquad, targetCategory, player)
            })
        },
        updateSquad(squad) {
            this.$apollo.mutate({
                mutation: gql`
                    mutation updateSquad($input: UpdateSquadInput!){
                        updateSquad(input: $input){
                            id
                            playerLimit
                            order
                        }
                    }
                `,
                variables: {
                    input: {
                        id: squad.id,
                        playerLimit: squad.playerLimit,
                        order: squad.order
                    }
                }
            })
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
            this.addPlayerToCategory(squad, category, player)
                .then(({data}) => {
                    setTimeout(() => {
                        this.focusNext(data.addSquadMemberByRefId)
                    }, 100);
                })
        },
        addPlayerToCategory(squad, category, player) {
            this.saving = true
            return this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation addSquadMemberByRefId($input: AddSquadMemberByRefIdInput!){
                            addSquadMemberByRefId(input: $input){
                                id
                                refId
                                name
                                gender
                                points {
                                    id
                                    category
                                    points
                                    position
                                    vintage
                                    version
                                }
                            }
                        }
                    `,
                    variables: {
                        input: {
                            categoryId: parseInt(category.id),
                            refId: player.refId,
                            version: squad.version
                                ? squad.version
                                : this.version
                        }
                    },
                    refetchQueries: this.teamRoundRefetchQueries(),
                    awaitRefetchQueries: true
                })
                .then((data) => {
                    emitAppEvent('player-added-to-category', data.data.addSquadMemberByRefId)
                    return data
                })
                .catch(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            message: `Kunne ikke tilføje spiller til holdet :(`
                        })
                })
                .finally(() => {
                    this.saving = false
                })
        },
        deletePlayerFromCategory(squad, category, player) {
            this.saving = true
            return this.$apollo
                .mutate({
                    mutation: gql`
                        mutation deleteSquadMember($id: ID!){
                            deleteSquadMember(id: $id){
                                id
                            }
                        }
                    `,
                    variables: {
                        id: player.id
                    },
                    refetchQueries: this.teamRoundRefetchQueries(),
                    awaitRefetchQueries: true
                })
                .then(({data}) => {
                    emitAppEvent('player-deleted-from-category', data.deleteSquadMember)
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Kunne ikke fjerne spilleren fra holdet :(`
                        })
                })
                .finally(() => {
                    this.saving = false
                })
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
                        mutation validateCrossSquads($input: [ValidateTeam!]!){
                            validateCrossSquads(input: $input){
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
                    variables: {
                        input: wrapInTeamAndSquads(this.teamRound.squads)
                    }
                })
                .then(({data}) => {
                    this.playingToHighList = data.validateCrossSquads;
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
        validate() {
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
        deleteTeam(targetSquad) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette hold ' + (this.teamRound.squads.indexOf(targetSquad) + 1) + '?',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation deleteSquad($id: ID!){
                                        deleteSquad(id: $id){
                                            id
                                        }
                                    }
                                `,
                                variables: {
                                    id: targetSquad.id
                                },
                                refetchQueries: this.teamRoundRefetchQueries()
                            })
                    }
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
        addPlayerToNextCategory(player) {
            let foundPlace = false;
            let addPlayerPromise;
            outside:
                for (const [index, squad] of this.teamRound.squads.entries()) {
                    for (const category of squad.categories) {
                        if (isWomenDouble(category) && category.players.length < 2 && player.gender === 'WOMEN') {
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player).finally(() => {
                                this.addedPlayerNotification(index, category.name)
                            })
                            foundPlace = true;
                            break outside;
                        } else if (isMensDouble(category) && category.players.length < 2 && player.gender === 'MEN') {
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player).finally(() => {
                                this.addedPlayerNotification(index, category.name)
                            })
                            foundPlace = true;
                            break outside;
                        } else if (isMixDouble(category) && category.players.length < 2) {
                            if (category.players.length === 0) {
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player).finally(() => {
                                    this.addedPlayerNotification(index, category.name)
                                })
                                foundPlace = true;
                                break outside;
                            } else if (containsWomen(category) && player.gender === 'MEN') {
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player).finally(() => {
                                    this.addedPlayerNotification(index, category.name)
                                })
                                foundPlace = true;
                                break outside;
                            } else if (containsMen(category) && player.gender === 'WOMEN') {
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player).finally(() => {
                                    this.addedPlayerNotification(index, category.name)
                                })
                                foundPlace = true;
                                break outside;
                            }
                        } else if (isMensSingle(category) && category.players.length < 1 && player.gender === 'MEN') {
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player).finally(() => {
                                this.addedPlayerNotification(index, category.name)
                            })
                            foundPlace = true;
                            break outside;
                        } else if (isWomensSingle(category) && category.players.length < 1 && player.gender === 'WOMEN') {
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player).finally(() => {
                                this.addedPlayerNotification(index, category.name)
                            })
                            foundPlace = true;
                            break outside;
                        }
                    }
                }
            if (foundPlace === false) {
                this.$buefy.snackbar.open(
                    {
                        duration: 3000,
                        type: 'is-danger',
                        queue: false,
                        message: `Kunne ikke finde en ledig plads på nogle hold`
                    })
            } else {
                return addPlayerPromise
            }
        },
        moveSquadOrderUp(squad) {
            this.saving = true
            this.$apollo.mutate({
                mutation: gql`
                    mutation moveSquadOrderUp($input: ID!){
                        moveSquadOrderUp(id: $input){
                            id
                            order
                        }
                    }
                `,
                variables: {
                    input: squad.id
                },
                refetchQueries: this.teamRoundRefetchQueries()
            })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Kunne ikke ændre sortering`
                        })
                })
                .finally(() => {
                    this.saving = false
                })
        },
        moveSquadOrderDown(squad) {
            this.saving = true
            this.$apollo.mutate({
                mutation: gql`
                    mutation moveSquadOrderDown($input: ID!){
                        moveSquadOrderDown(id: $input){
                            id
                            order
                        }
                    }
                `,
                variables: {
                    input: squad.id
                },
                refetchQueries: this.teamRoundRefetchQueries()
            })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Kunne ikke ændre sortering`
                        })
                })
                .finally(() => {
                    this.saving = false
                })
        },
    }
}
</script>
