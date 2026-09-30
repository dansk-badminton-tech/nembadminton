<template>
    <div>
        <InlineAddSquadForm
            :loading="loading"
            :tiers-loading="$apollo.queries.tiers.loading"
            :teams-loading="$apollo.queries.teams.loading"
            :teams-failed="teamsFailed"
            :selected-match-count="selectedMatchCount"
            :selected-name="selectedName"
            :selected-tier-name="selectedTierName"
            :selected-team-id="selectedTeamId"
            :manual-entry="manualEntry"
            :selected-playing-date="selectedPlayingDate"
            :match-count-options="matchCountOptions"
            :tier-options="tierOptions"
            :team-options="teamOptions"
            :quick-date-options="quickDateOptions"
            :recommended-ranking-label="recommendedRankingLabel"
            :next-squad-number="nextSquadNumber"
            :custom-category-counts="customCategoryCounts"
            :custom-total-match-count="customTotalMatchCount"
            @select-match-count="onMatchCountChange"
            @select-name="onNameChange"
            @select-tier="onTierChange"
            @select-team="onTeamChange"
            @start-manual-entry="onStartManualEntry"
            @cancel-manual-entry="onCancelManualEntry"
            @create-team="teamFormOpen = true"
            @change-playing-date="onPlayingDateChange"
            @select-quick-date="onQuickDateSelect"
            @update-custom-category-count="onCustomCategoryCountChange"
            @submit-inline="addInlineSquad"/>

        <b-modal v-model="teamFormOpen" :width="560" has-modal-card :can-cancel="['x', 'escape']">
            <TeamForm
                :clubhouse-id="clubhouseId"
                :season-id="seasonNumber"
                @cancel="teamFormOpen = false"
                @saved="onTeamCreated"/>
        </b-modal>
    </div>
</template>

<script>
import gql from "graphql-tag";
import {TeamFightHelper} from "./teams";
import TeamRoundQuery from "../../../queries/teamRound.graphql";
import TournamentTiersQuery from "../../../queries/tournamentTiers.graphql";
import TeamsForSquadPickerQuery from "../../../queries/teamsForSquadPicker.graphql";
import {formatDateTime} from "../../helpers";
import {resolveRecommendedRankingVersion} from "../common/ranking-version";
import {timeToMonth} from "./helper";
import InlineAddSquadForm from "./InlineAddSquadForm.vue";
import TeamForm from "../team/TeamForm.vue";
import {buildTeamPickerOptions} from "./team-picker";
import {teamTierLabel} from "./team-label";
import {
    isSameDay,
    normalizeDateToDay,
    resolveDateByOffset
} from "./add-squad-metadata";

const CUSTOM_MATCH_COUNT = 'custom';

const DEFAULT_CUSTOM_COUNTS = Object.freeze({
    mix: 0,
    womenSingles: 0,
    womenDoubles: 0,
    mensSingles: 0,
    mensDoubles: 0
});

export default {
    name: "AddTeamsButtons",
    components: {InlineAddSquadForm, TeamForm},
    props: {
        teamRoundId: String,
        teamRoundDate: Date,
        clubhouseId: {
            type: [String, Number],
            default: null
        },
        existingSquadCount: {
            type: Number,
            default: 0
        },
        seasonId: {
            type: [String, Number],
            default: null
        },
        usedTeamIds: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            loading: false,
            selectedMatchCount: 13,
            selectedName: '',
            selectedTierName: '',
            selectedTeamId: null,
            manualEntry: false,
            teamFormOpen: false,
            selectedPlayingDate: null,
            playingDateChanged: false,
            rankingVersions: [],
            tiers: [],
            teams: [],
            teamsFailed: false,
            customCategoryCounts: {...DEFAULT_CUSTOM_COUNTS}
        }
    },
    computed: {
        matchCountOptions() {
            return TeamFightHelper
                .getSupportedMatchCounts()
                .sort((first, second) => first - second);
        },
        normalizedTeamRoundDate() {
            return normalizeDateToDay(this.teamRoundDate);
        },
        recommendedVersion() {
            return resolveRecommendedRankingVersion(this.rankingVersions, this.selectedPlayingDate);
        },
        recommendedRankingLabel() {
            if (this.recommendedVersion === null) {
                return 'Rangliste: Bruger holdrundens rangliste';
            }

            return `Rangliste: ${timeToMonth(this.recommendedVersion)}`;
        },
        seasonNumber() {
            return this.seasonId === null || this.seasonId === undefined
                ? null
                : Number.parseInt(this.seasonId, 10);
        },
        tierOptions() {
            return this.tiers.map((tier) => ({
                id: tier.id,
                label: tier.tierName
            }));
        },
        teamOptions() {
            return buildTeamPickerOptions(this.teams, this.usedTeamIds);
        },
        nextSquadNumber() {
            return this.existingSquadCount + 1;
        },
        customTotalMatchCount() {
            const counts = this.customCategoryCounts;
            return counts.mix
                 + counts.womenSingles
                 + counts.womenDoubles
                 + counts.mensSingles
                 + counts.mensDoubles;
        },
        isCustomMatchCount() {
            return this.selectedMatchCount === CUSTOM_MATCH_COUNT;
        },
        canSubmit() {
            if (this.isCustomMatchCount) {
                return this.customTotalMatchCount > 0;
            }
            return this.selectedMatchCount !== null;
        },
        quickDateOptions() {
            return [-1, 0, 1].map((offset) => ({
                offset,
                label: this.formatQuickDateLabel(offset),
                type: this.quickDateButtonType(offset)
            }));
        }
    },
    watch: {
        teamRoundDate: {
            immediate: true,
            handler() {
                if (this.playingDateChanged && this.selectedPlayingDate !== null) {
                    return;
                }

                this.selectedPlayingDate = this.resolveDateByOffset(0);
            }
        }
    },
    apollo: {
        rankingVersions: {
            query: gql`
                query {
                    rankingVersions
                }
            `
        },
        tiers: {
            query: TournamentTiersQuery,
            update: data => data.tournamentTiers,
            error() {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-warning',
                        message: 'Kunne ikke hente turneringstiers'
                    });
            }
        },
        teams: {
            query: TeamsForSquadPickerQuery,
            variables() {
                return {
                    clubhouseId: this.clubhouseId,
                    seasonId: this.seasonNumber
                };
            },
            skip() {
                return this.clubhouseId === null || this.clubhouseId === undefined;
            },
            update: data => data.teams.data,
            result() {
                this.teamsFailed = false;
            },
            error() {
                // Silent — team picker is optional UI; hide the "no teams" hint and keep "Uden hold"
                this.teamsFailed = true;
            },
            fetchPolicy: 'network-only'
        }
    },
    methods: {
        resolveBaseDate() {
            if (this.normalizedTeamRoundDate !== null) {
                return this.normalizedTeamRoundDate;
            }

            const today = new Date();
            return new Date(today.getFullYear(), today.getMonth(), today.getDate());
        },
        resolveDateByOffset(offset) {
            return resolveDateByOffset(this.resolveBaseDate(), offset);
        },
        isSameDay(firstDate, secondDate) {
            return isSameDay(firstDate, secondDate);
        },
        quickDateButtonType(offset) {
            if (this.selectedPlayingDate === null) {
                return 'is-light';
            }

            return this.isSameDay(this.resolveDateByOffset(offset), this.selectedPlayingDate)
                   ? 'is-link'
                   : 'is-light';
        },
        formatQuickDateLabel(offset) {
            const date = this.resolveDateByOffset(offset);
            if (date === null) {
                return '';
            }

            return date.toLocaleDateString('da-DK', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },
        setPlayingDateByOffset(offset) {
            this.playingDateChanged = true;
            this.selectedPlayingDate = this.resolveDateByOffset(offset);
        },
        markPlayingDateAsManual(date) {
            this.playingDateChanged = true;
            this.selectedPlayingDate = date;
        },
        onMatchCountChange(matchCount) {
            if (matchCount === CUSTOM_MATCH_COUNT) {
                this.selectedMatchCount = CUSTOM_MATCH_COUNT;
                return;
            }

            const parsedMatchCount = Number.parseInt(matchCount, 10);
            this.selectedMatchCount = Number.isNaN(parsedMatchCount)
                ? null
                : parsedMatchCount;
        },
        onCustomCategoryCountChange({field, value}) {
            if (!Object.prototype.hasOwnProperty.call(this.customCategoryCounts, field)) {
                return;
            }
            const parsed = Number.parseInt(value, 10);
            const safeValue = Number.isNaN(parsed) || parsed < 0 ? 0 : parsed;
            this.customCategoryCounts = {
                ...this.customCategoryCounts,
                [field]: safeValue
            };
        },
        onNameChange(name) {
            this.selectedName = typeof name === 'string' ? name : '';
        },
        onTierChange(tierName) {
            this.selectedTierName = typeof tierName === 'string' ? tierName : '';
        },
        onTeamChange(team) {
            if (team === null || team === undefined) {
                this.selectedTeamId = null;
                this.selectedName = '';
                this.selectedTierName = '';
                return;
            }
            this.selectedTeamId = team.id;
            this.selectedName = team.name || '';
            this.selectedTierName = teamTierLabel(team);
            this.manualEntry = false;
        },
        onTeamCreated(team) {
            this.teamFormOpen = false;
            this.$apollo.queries.teams.refetch();
            // A Team created for another season doesn't belong in this round's picker.
            if (this.seasonNumber === null || team.season?.id === this.seasonNumber) {
                this.onTeamChange(team);
            }
        },
        onStartManualEntry() {
            this.onTeamChange(null);
            this.manualEntry = true;
        },
        onCancelManualEntry() {
            this.onTeamChange(null);
            this.manualEntry = false;
        },
        onPlayingDateChange(date) {
            this.markPlayingDateAsManual(date);
        },
        onQuickDateSelect(offset) {
            this.setPlayingDateByOffset(offset);
        },
        resolvePlayingDatetime() {
            if (this.selectedPlayingDate === null) {
                return null;
            }

            const dateTime = new Date(this.selectedPlayingDate);
            if (Number.isNaN(dateTime.getTime())) {
                return null;
            }

            dateTime.setHours(0, 0, 0, 0);
            return formatDateTime(dateTime);
        },
        buildSquadMetadataInput() {
            const input = {};
            const playingDatetime = this.resolvePlayingDatetime();
            if (playingDatetime !== null) {
                input.playingDatetime = playingDatetime;
            }

            if (this.recommendedVersion !== null) {
                input.version = this.recommendedVersion;
            }

            const trimmedName = this.selectedName.trim();
            if (trimmedName !== '') {
                input.name = trimmedName;
            }

            if (this.selectedTeamId !== null) {
                // The squad shows the team's tier, so its own tier is left unset.
                input.teamId = this.selectedTeamId;
            } else {
                const trimmedTier = this.selectedTierName.trim();
                if (trimmedTier !== '') {
                    input.tier = trimmedTier;
                }
            }

            return input;
        },
        buildSquadPayload() {
            if (this.isCustomMatchCount) {
                const counts = this.customCategoryCounts;
                return TeamFightHelper.generateSquad(
                    counts.mix,
                    counts.womenSingles,
                    counts.womenDoubles,
                    counts.mensSingles,
                    counts.mensDoubles
                );
            }

            return TeamFightHelper.generateSquadByMatchCount(this.selectedMatchCount);
        },
        addSquad(team, metadata = {}){
            this.loading = true
            return this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation createSquad($input: CreateSquadInput!){
                            createSquad(input: $input){
                                id
                                playerLimit
                                order
                                name
                                categories {
                                    id
                                    category
                                    name
                                }
                            }
                        }
                    `,
                    variables: {
                        input: {
                            ...{
                                teamRound: {
                                    connect: this.teamRoundId
                                }
                            },
                            ...team,
                            ...metadata
                        }
                    },
                    refetchQueries: [
                        {query: TeamRoundQuery, variables: {id: this.teamRoundId}}
                    ],
                }).then(() => {
                this.$emit('team-added')
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-danger',
                        message: `Kunne ikke tilføje holdet til holdopstillingen :(`
                    })
            }).finally(() => {
                this.loading = false
            })
        },
        addInlineSquad() {
            if (!this.canSubmit) {
                return;
            }
            const squad = this.buildSquadPayload();
            const metadata = this.buildSquadMetadataInput();
            this.addSquad(squad, metadata).then(() => {
                this.selectedName = '';
                this.selectedTierName = '';
                this.selectedTeamId = null;
            })
        }
    }
}
</script>

<style scoped>

</style>
