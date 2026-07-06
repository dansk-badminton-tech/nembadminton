<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <section class="section is-main-section">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column is-8">
                        <div class="card">
                            <header class="card-header is-flex is-justify-content-space-between is-align-items-center pr-4">
                                <p class="card-header-title">
                                    <b-icon icon="calendar-clock" class="mr-2"></b-icon>
                                    Dine holdrunder
                                </p>
                                <b-checkbox v-model="hidePlayed" size="is-small">
                                    Skjul spillede
                                </b-checkbox>
                            </header>
                            <div class="card-content relative-position">
                                <b-loading :is-full-page="false" v-model="loading" :can-cancel="false"></b-loading>

                                <div v-if="!loading && (!teamRounds || teamRounds.length === 0)" class="has-text-centered py-6 has-text-grey">
                                    <b-icon icon="calendar-blank" size="is-large" class="mb-3"></b-icon>
                                    <p class="is-size-5">Ingen holdrunder fundet</p>
                                    <p class="is-size-7">Du er ikke sat på hold i dette klubhus endnu.</p>
                                </div>

                                <div v-else-if="!loading && filteredTeamRounds.length === 0" class="has-text-centered py-6 has-text-grey">
                                    <b-icon icon="calendar-check" size="is-large" class="mb-3"></b-icon>
                                    <p class="is-size-5">Ingen kommende holdrunder</p>
                                    <p class="is-size-7">Alle dine holdrunder er spillet.</p>
                                </div>

                                <div v-else class="team-fights-list">
                                    <div
                                        v-for="round in filteredTeamRounds"
                                        :key="round.id"
                                        class="team-fight-item mb-4 p-4"
                                        :class="isUpcoming(round.gameDate) ? 'is-upcoming' : 'is-past'"
                                    >
                                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                                            <div>
                                                <div class="is-flex is-align-items-center mb-1">
                                                    <b-tag
                                                        :type="isUpcoming(round.gameDate) ? 'is-info' : 'is-light'"
                                                        size="is-small"
                                                        class="mr-2"
                                                    >
                                                        {{ isUpcoming(round.gameDate) ? 'Kommende' : 'Spillet' }}
                                                    </b-tag>
                                                    <span class="title is-6 mb-0">
                                                        {{ round.name === null ? 'Runde ' + round.round : round.name }}
                                                    </span>
                                                </div>
                                                <p class="subtitle is-7 has-text-grey mb-0">
                                                    <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                                                    {{ formatDate(round.gameDate) }}
                                                </p>
                                            </div>
                                            <b-button
                                                tag="router-link"
                                                :to="{ name: 'team-fight-public-view', params: { teamUUID: round.id } }"
                                                type="is-link"
                                                outlined
                                                size="is-small"
                                                icon-right="eye"
                                            >
                                                Vis holdopstilling
                                            </b-button>
                                        </div>
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
import HeroBar from "@/components/HeroBar.vue";
import TilesBlock from "@/components/TilesBlock.vue";
import CardWidget from "@/components/CardWidget.vue";
import CategoryPoints from "@/views/dashboard/CategoryPoints.vue";
import gql from "graphql-tag";

export default {
    name: "PlayerDashboard",
    components: {CategoryPoints, HeroBar, TitleBar, TilesBlock, CardWidget},
    inject: ["clubhouseId"],
    data() {
        return {
            titleStack: ['Spillerportal', 'Dashboard'],
            loading: false,
            teamRounds: [],
            hidePlayed: true
        }
    },
    computed: {
        filteredTeamRounds() {
            if (this.hidePlayed) {
                return this.teamRounds.filter(round => this.isUpcoming(round.gameDate));
            }
            return this.teamRounds;
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('da-DK', {
                weekday: 'long',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        },
        isUpcoming(dateString) {
            if (!dateString) return false;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const gameDate = new Date(dateString);
            return gameDate >= today;
        }
    },
    apollo: {
        teamRounds: {
            query: gql`
                query TeamRounds($clubhouseId: ID!, $first: Int!, $page: Int, $order: [QueryTeamRoundsOrderOrderByClause!]) {
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
                        }
                    }
                }
            `,
            update(data) {
                return data.teamRounds.data;
            },
            variables() {
                return {
                    first: 50,
                    page: 1,
                    order: [{ column: 'GAME_DATE', order: 'DESC' }],
                    clubhouseId: this.clubhouseId
                }
            },
            fetchPolicy: 'network-only',
            watchLoading(isLoading) {
                this.loading = isLoading;
            },
            error(error) {
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

.team-fight-item {
    border-radius: 6px;
    border: 1px solid #dbdbdb;
    background-color: #ffffff;
    transition: all 0.2s ease;
}

.team-fight-item.is-upcoming {
    border-left: 4px solid #3e8ed0;
}

.team-fight-item.is-past {
    border-left: 4px solid #b5b5b5;
    background-color: #fafafa;
    opacity: 0.85;
}

.team-fight-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}
</style>
