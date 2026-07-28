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
                                    Dine kommende holdrunder
                                </p>
                                <b-button
                                    tag="router-link"
                                    :to="{ name: 'team-fight-player-list' }"
                                    type="is-link"
                                    outlined
                                    size="is-small"
                                    icon-right="arrow-right"
                                >
                                    Se alle holdrunder
                                </b-button>
                            </header>
                            <div class="card-content relative-position">
                                <b-loading :is-full-page="false" v-model="loading" :can-cancel="false"></b-loading>

                                <div v-if="!loading && teamRounds.length === 0" class="has-text-centered py-6 has-text-grey">
                                    <b-icon icon="calendar-blank" size="is-large" class="mb-3"></b-icon>
                                    <p class="is-size-5">Ingen kommende holdrunder</p>
                                    <p class="is-size-7">Se din holdhistorik under "Se alle holdrunder".</p>
                                </div>

                                <div v-else class="team-fights-list">
                                    <TeamRoundPlayerRow
                                        v-for="round in teamRounds"
                                        :key="round.id"
                                        :round="round"
                                    />
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
import TeamRoundPlayerRow from "@/views/team-fight/TeamRoundPlayerRow.vue";
import gql from "graphql-tag";
import moment from "moment";

export default {
    name: "PlayerDashboard",
    components: {CategoryPoints, HeroBar, TitleBar, TilesBlock, CardWidget, TeamRoundPlayerRow},
    inject: ["clubhouseId"],
    data() {
        return {
            titleStack: ['Spillerportal', 'Dashboard'],
            loading: false,
            teamRounds: []
        }
    },
    apollo: {
        teamRounds: {
            query: gql`
                query upcomingTeamRounds($clubhouseId: ID!, $first: Int!, $gameDate: DateRange) {
                    teamRounds(clubhouseId: $clubhouseId, order: [{column: GAME_DATE, order: ASC}], gameDate: $gameDate, first: $first, page: 1) {
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
                    first: 3,
                    // The schema only offers a closed from..to range, so we bound the upper
                    // end with a far-future date to approximate "from today onwards".
                    gameDate: {
                        from: moment().format('YYYY-MM-DD'),
                        to: '2099-12-31'
                    },
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
</style>
