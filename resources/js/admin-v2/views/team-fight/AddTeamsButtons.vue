<template>
    <InlineAddSquadForm
        :loading="loading"
        :tiers-loading="$apollo.queries.tiers.loading"
        :selected-match-count="selectedMatchCount"
        :selected-tier-name="selectedTierName"
        :selected-playing-date="selectedPlayingDate"
        :match-count-options="matchCountOptions"
        :tier-options="tierOptions"
        :quick-date-options="quickDateOptions"
        :recommended-ranking-label="recommendedRankingLabel"
        :next-squad-number="nextSquadNumber"
        :custom-category-counts="customCategoryCounts"
        :custom-total-match-count="customTotalMatchCount"
        @select-match-count="onMatchCountChange"
        @select-tier="onTierChange"
        @change-playing-date="onPlayingDateChange"
        @select-quick-date="onQuickDateSelect"
        @update-custom-category-count="onCustomCategoryCountChange"
        @submit-inline="addInlineSquad"/>
</template>

<script>
import gql from "graphql-tag";
import {TeamFightHelper} from "./teams";
import TeamRoundQuery from "../../../queries/teamRound.graphql";
import TournamentTiersQuery from "../../../queries/tournamentTiers.graphql";
import {formatDateTime} from "../../helpers";
import {resolveRecommendedRankingVersion} from "../common/ranking-version";
import {timeToMonth} from "./helper";
import InlineAddSquadForm from "./InlineAddSquadForm.vue";
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
    components: {InlineAddSquadForm},
    props: {
        teamRoundId: String,
        teamRoundDate: Date,
        existingSquadCount: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            loading: false,
            selectedMatchCount: 13,
            selectedTierName: '',
            selectedPlayingDate: null,
            playingDateChanged: false,
            rankingVersions: [],
            tiers: [],
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
        tierOptions() {
            return this.tiers.map((tier) => ({
                id: tier.id,
                label: tier.tierName
            }));
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
        onTierChange(tierName) {
            this.selectedTierName = typeof tierName === 'string' ? tierName : '';
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

            dateTime.setHours(12, 0, 0, 0);
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

            const trimmedTier = this.selectedTierName.trim();
            if (trimmedTier !== '') {
                input.name = `${trimmedTier}`;
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
                        message: `Kunne ikke oprette holdet :(`
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
                this.selectedTierName = '';
            })
        }
    }
}
</script>

<style scoped>

</style>
