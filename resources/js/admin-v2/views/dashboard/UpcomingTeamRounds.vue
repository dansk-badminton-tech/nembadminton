<template>
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <b-icon icon="account-group" size="is-small"></b-icon>
                <span class="ml-2">Kommende holdrunder</span>
                <span class="tag is-info ml-2" v-if="total">{{ total }}</span>
            </div>
            <div class="card-header-icon">
                <b-button
                    tag="router-link"
                    :to="'/c-' + clubhouseId + '/team-fight/dashboard'"
                    type="is-info"
                    outlined
                    size="is-small"
                    icon-right="arrow-right">
                    Se alle
                </b-button>
            </div>
        </div>
        <div class="card-content">
            <b-table
                :data="teamRounds"
                :narrowed="true"
                :loading="loading">
                <b-table-column field="gameDate" label="Dato" v-slot="props">
                    <b-icon icon="calendar" size="is-small" type="is-grey"></b-icon>
                    <span class="ml-1">{{ props.row.gameDate }}</span>
                    <b-tag class="ml-2" type="is-light" v-if="relativeDays(props.row.gameDate)">
                        {{ relativeDays(props.row.gameDate) }}
                    </b-tag>
                </b-table-column>

                <b-table-column field="name" label="Navn" v-slot="props">
                    <b-icon icon="shield-account" size="is-small" type="is-grey"></b-icon>
                    <span class="ml-1">
                        {{ props.row.name === null ? 'Runde ' + props.row.round : props.row.name }}
                    </span>
                </b-table-column>

                <b-table-column field="round" label="Runde" v-slot="props">
                    <span v-if="props.row.round != null">{{ props.row.round }}</span>
                    <span v-else class="has-text-grey">–</span>
                </b-table-column>

                <b-table-column label="" v-slot="props" cell-class="has-text-right">
                    <b-button
                        tag="router-link"
                        :to="{ name: 'team-fight-edit', params: { teamUUID: props.row.id } }"
                        size="is-small"
                        icon-right="arrow-right"
                        aria-label="Åbn holdrunde"
                        title="Åbn holdrunde"/>
                </b-table-column>

                <template v-slot:empty>
                    <div class="has-text-centered py-5">
                        <b-icon icon="calendar-blank" size="is-large" type="is-grey-light"></b-icon>
                        <p class="subtitle is-6 has-text-grey mt-3">
                            Ingen kommende holdrunder.
                        </p>
                    </div>
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
import gql from "graphql-tag";
import moment from "moment";

export default {
    name: "UpcomingTeamRounds",
    inject: ['clubhouseId'],
    data() {
        return {
            teamRoundsResult: {
                data: [],
                paginatorInfo: {total: 0}
            }
        }
    },
    apollo: {
        teamRoundsResult: {
            query: gql`
                query upcomingTeamRounds($clubhouseId: ID!, $first: Int!, $gameDate: DateRange){
                    teamRounds(
                        clubhouseId: $clubhouseId,
                        order: [{column: GAME_DATE, order: ASC}],
                        gameDate: $gameDate,
                        first: $first,
                        page: 1
                    ){
                        data{
                            id
                            name
                            round
                            gameDate
                        }
                        paginatorInfo{
                            total
                        }
                    }
                }
            `,
            update: data => data.teamRounds,
            variables() {
                return {
                    clubhouseId: this.clubhouseId,
                    first: 5,
                    // The schema only offers a closed from..to range, so we bound the upper
                    // end with a far-future date to approximate "from today onwards".
                    gameDate: {
                        from: moment().format('YYYY-MM-DD'),
                        to: '2099-12-31'
                    }
                }
            },
            skip() {
                return this.clubhouseId === null || this.clubhouseId === undefined
            }
        }
    },
    computed: {
        loading() {
            return this.$apollo.queries.teamRoundsResult.loading
        },
        teamRounds() {
            return this.teamRoundsResult?.data || []
        },
        total() {
            return this.teamRoundsResult?.paginatorInfo?.total || 0
        }
    },
    methods: {
        relativeDays(gameDate) {
            if (!gameDate) {
                return null
            }
            const days = moment(gameDate, 'YYYY-MM-DD').startOf('day').diff(moment().startOf('day'), 'days')
            if (days < 0) return null
            if (days === 0) return 'I dag'
            if (days === 1) return 'I morgen'
            return 'Om ' + days + ' dage'
        }
    }
}
</script>
