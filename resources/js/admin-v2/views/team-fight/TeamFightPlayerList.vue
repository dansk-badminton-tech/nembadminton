<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <section class="section is-main-section">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column is-10">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                    <b-icon icon="calendar-clock" class="mr-2"></b-icon>
                                    Holdrunder
                                </p>
                            </header>
                            <div class="card-content relative-position">
                                <b-loading :is-full-page="false" v-model="loading" :can-cancel="false"></b-loading>

                                <div v-if="!loading && rounds.length === 0" class="has-text-centered py-6 has-text-grey">
                                    <b-icon icon="calendar-blank" size="is-large" class="mb-3"></b-icon>
                                    <p class="is-size-5">Ingen holdrunder fundet</p>
                                    <p class="is-size-7">Du er ikke sat på hold i dette klubhus endnu.</p>
                                </div>

                                <div v-else>
                                    <div v-for="season in groupedBySeason" :key="season.seasonStartYear" class="season-group mb-5">
                                        <h3 class="title is-5 mb-3">
                                            {{ season.label }}
                                            <b-tag v-if="season.seasonStartYear === currentSeasonYear" type="is-info" size="is-small" class="ml-2">
                                                Nuværende sæson
                                            </b-tag>
                                        </h3>
                                        <TeamRoundPlayerRow
                                            v-for="round in season.rounds"
                                            :key="round.id"
                                            :round="round"
                                        />
                                    </div>

                                    <div class="has-text-centered mt-4" v-if="hasMorePages">
                                        <b-button
                                            :loading="loadingMore"
                                            @click="loadMore"
                                            type="is-light"
                                            icon-left="chevron-down"
                                        >
                                            Indlæs flere holdrunder
                                        </b-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import TitleBar from "@/components/TitleBar.vue";
import TeamRoundPlayerRow from "@/views/team-fight/TeamRoundPlayerRow.vue";
import gql from "graphql-tag";
import {getCurrentSeason, getSeasonStartYear} from "@/helpers";

const TEAM_ROUNDS_QUERY = gql`
    query playerTeamRoundsList($clubhouseId: ID!, $first: Int!, $page: Int!, $order: [QueryTeamRoundsOrderOrderByClause!]) {
        teamRounds(clubhouseId: $clubhouseId, order: $order, first: $first, page: $page) {
            data {
                id
                name
                round
                version
                gameDate
                createdAt
                updatedAt
            }
            paginatorInfo {
                total
                hasMorePages
            }
        }
    }
`;

export default {
    name: "TeamFightPlayerList",
    components: {TitleBar, TeamRoundPlayerRow},
    inject: ['clubhouseId'],
    data() {
        return {
            titleStack: ['Spillerportal', 'Holdrunder'],
            loading: false,
            loadingMore: false,
            teamRoundsResult: null,
            rounds: [],
            page: 1,
            perPage: 20,
            hasMorePages: false,
            currentSeasonYear: getCurrentSeason()
        }
    },
    computed: {
        groupedBySeason() {
            const groups = {};
            this.rounds.forEach(round => {
                if (!round.gameDate) return;
                const seasonStartYear = getSeasonStartYear(new Date(round.gameDate));
                if (!groups[seasonStartYear]) {
                    groups[seasonStartYear] = [];
                }
                groups[seasonStartYear].push(round);
            });
            return Object.keys(groups)
                .map(Number)
                .sort((a, b) => b - a)
                .map(seasonStartYear => ({
                    seasonStartYear,
                    label: seasonStartYear + '/' + (seasonStartYear + 1),
                    rounds: groups[seasonStartYear].slice().sort((a, b) => new Date(a.gameDate) - new Date(b.gameDate))
                }));
        }
    },
    methods: {
        loadMore() {
            if (this.loadingMore || !this.hasMorePages) return;
            this.loadingMore = true;
            const nextPage = this.page + 1;
            this.$apollo.query({
                query: TEAM_ROUNDS_QUERY,
                variables: {
                    clubhouseId: this.clubhouseId,
                    first: this.perPage,
                    page: nextPage,
                    order: [{column: 'GAME_DATE', order: 'DESC'}]
                },
                fetchPolicy: 'network-only'
            }).then(({data}) => {
                this.rounds = [...this.rounds, ...data.teamRounds.data];
                this.hasMorePages = data.teamRounds.paginatorInfo.hasMorePages;
                this.page = nextPage;
            }).catch(() => {
                this.$buefy.snackbar.open({
                    duration: 5000,
                    type: 'is-danger',
                    message: "Kunne ikke hente flere holdrunder"
                });
            }).finally(() => {
                this.loadingMore = false;
            });
        }
    },
    apollo: {
        teamRoundsResult: {
            query: TEAM_ROUNDS_QUERY,
            variables() {
                return {
                    clubhouseId: this.clubhouseId,
                    first: this.perPage,
                    page: 1,
                    order: [{column: 'GAME_DATE', order: 'DESC'}]
                }
            },
            update: data => data.teamRounds,
            fetchPolicy: 'network-only',
            watchLoading(isLoading) {
                this.loading = isLoading;
            },
            result({data}) {
                this.rounds = data.teamRounds.data;
                this.hasMorePages = data.teamRounds.paginatorInfo.hasMorePages;
                this.page = 1;
            },
            error() {
                this.$buefy.snackbar.open({
                    duration: 5000,
                    type: 'is-danger',
                    message: "Kunne ikke hente dine holdrunder"
                });
            }
        }
    }
}
</script>

<style scoped>
.relative-position {
    position: relative;
    min-height: 120px;
}
</style>
