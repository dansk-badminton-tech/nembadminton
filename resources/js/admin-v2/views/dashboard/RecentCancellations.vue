<template>
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <b-icon icon="account-cancel" size="is-small"></b-icon>
                <span class="ml-2">Seneste afbud</span>
                <span class="tag is-info ml-2" v-if="total">{{ total }}</span>
            </div>
            <div class="card-header-icon">
                <b-button
                    tag="router-link"
                    to="/cancellations/redirect"
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
                :data="cancellations"
                :narrowed="true"
                :loading="loading">
                <b-table-column field="createdAt" label="Oprettet" v-slot="props">
                    <b-icon icon="clock" size="is-small" type="is-grey"></b-icon>
                    <span class="ml-1">{{ props.row.createdAt }}</span>
                </b-table-column>

                <b-table-column field="member.name" label="Navn" v-slot="props">
                    <b-icon icon="account" size="is-small" type="is-grey"></b-icon>
                    <span class="ml-1">{{ props.row.member.name }}</span>
                </b-table-column>

                <b-table-column field="dates" label="Afbudsdatoer" v-slot="props">
                    <b-icon icon="calendar" size="is-small" type="is-grey"></b-icon>
                    <span class="ml-1">{{ props.row.dates.map(d => d.date).join(", ") }}</span>
                </b-table-column>

                <template v-slot:empty>
                    <div class="has-text-centered py-5">
                        <b-icon icon="calendar-remove" size="is-large" type="is-grey-light"></b-icon>
                        <p class="subtitle is-6 has-text-grey mt-3">
                            Der er endnu ikke registreret nogen afbud.
                        </p>
                    </div>
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
import cancellationCollectorQuery from "../../../queries/cancellationCollector.gql";

export default {
    name: "RecentCancellations",
    props: {
        collectorId: {
            type: String,
            default: null
        }
    },
    data() {
        return {
            cancellationCollector: null,
            orderBy: [{
                column: 'CREATED_AT',
                order: 'DESC'
            }]
        }
    },
    apollo: {
        cancellationCollector: {
            query: cancellationCollectorQuery,
            variables() {
                return {
                    id: this.collectorId,
                    hasDates: null,
                    orderBy: this.orderBy,
                    page: 1,
                    first: 5
                }
            },
            skip() {
                return !this.collectorId
            }
        }
    },
    computed: {
        loading() {
            return this.$apollo.queries.cancellationCollector.loading
        },
        cancellations() {
            return this.cancellationCollector?.cancellations?.data || []
        },
        total() {
            return this.cancellationCollector?.cancellations?.paginatorInfo?.total || 0
        }
    }
}
</script>
