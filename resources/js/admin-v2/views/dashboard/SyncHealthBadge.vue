<template>
    <div class="card">
        <div class="card-content">
            <div class="level is-mobile mb-2">
                <div class="level-left">
                    <div class="level-item">
                        <b-icon :icon="overall.icon" :type="overall.type" size="is-medium"></b-icon>
                    </div>
                    <div class="level-item">
                        <div>
                            <p class="heading">Synkronisering med badmintonplayer.dk</p>
                            <p class="is-size-5 has-text-weight-semibold">{{ overall.label }}</p>
                        </div>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <b-button
                            tag="router-link"
                            :to="'/c-' + clubhouseId + '/club-house'"
                            type="is-text"
                            size="is-small"
                            icon-right="arrow-right">
                            Se hændelseslog
                        </b-button>
                    </div>
                </div>
            </div>

            <table class="table is-fullwidth is-narrow mb-0" v-if="componentStatuses.length">
                <tbody>
                    <tr v-for="c in componentStatuses" :key="c.component">
                        <td class="is-narrow">
                            <b-icon :icon="c.status.icon" :type="c.status.type" size="is-small"></b-icon>
                        </td>
                        <td class="has-text-weight-medium">
                            <b-tooltip
                                v-if="c.description"
                                :label="c.description"
                                type="is-dark"
                                position="is-right"
                                multilined
                                dashed>
                                {{ c.component }}
                            </b-tooltip>
                            <span v-else>{{ c.component }}</span>
                        </td>
                        <td class="has-text-grey">{{ c.status.label }}</td>
                        <td class="has-text-grey has-text-right is-size-7">Sidst opdateret: {{ c.lastSyncedAt }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import gql from "graphql-tag";
import moment from "moment";

const ERROR_PATTERN = /fejl|error|exception|mislyk/;
const STALE_HOURS = 48;

// Light, human-readable descriptions for the sync components written to the log.
const COMPONENT_DESCRIPTIONS = {
    'member-importer': 'Henter klubbens medlemmer fra badmintonplayer.dk.',
    'points-importer': 'Opdaterer spillernes ranglistepoint fra badmintonplayer.dk.',
    'system': 'Generelle systemhændelser.'
};

export default {
    name: "SyncHealthBadge",
    inject: ['clubhouseId'],
    data() {
        return {
            logs: {
                data: []
            }
        }
    },
    apollo: {
        logs: {
            query: gql`
                query syncStatusLogs($page: Int!, $first: Int!, $clubhouse: Int!){
                    logs(page: $page, first: $first, clubhouse: $clubhouse){
                        data{
                            id
                            log
                            component
                            createdAt
                        }
                    }
                }
            `,
            // Logs come back newest-first, so the first entry seen per component is its latest.
            // Pull a wider window than the default 10 so every component is represented.
            // Poll slowly here (vs. the 2s poll in the full ActivityLog) — this is only a badge.
            pollInterval: 60000,
            variables() {
                return {
                    page: 1,
                    first: 50,
                    clubhouse: parseInt(this.clubhouseId)
                }
            },
            skip() {
                return this.clubhouseId === null || this.clubhouseId === undefined
            }
        }
    },
    computed: {
        // Latest log entry per component (logs are ordered newest-first server-side).
        componentStatuses() {
            const latestByComponent = new Map()
            for (const entry of this.logs?.data || []) {
                if (!latestByComponent.has(entry.component)) {
                    latestByComponent.set(entry.component, entry)
                }
            }
            return [...latestByComponent.values()].map(entry => ({
                component: entry.component,
                description: COMPONENT_DESCRIPTIONS[entry.component] || null,
                lastSyncedAt: entry.createdAt,
                status: this.statusFor(entry)
            }))
        },
        // Aggregate: worst status across components wins.
        overall() {
            const statuses = this.componentStatuses
            if (!statuses.length) {
                return {type: 'is-grey', icon: 'sync-off', label: 'Ingen synkronisering endnu'}
            }
            if (statuses.some(c => c.status.key === 'error')) {
                return {type: 'is-danger', icon: 'alert-circle', label: 'Synkroniseringsfejl'}
            }
            if (statuses.some(c => c.status.key === 'stale')) {
                return {type: 'is-warning', icon: 'sync-alert', label: 'Ikke synkroniseret for nylig'}
            }
            return {type: 'is-success', icon: 'check-circle', label: 'Synkroniseret'}
        }
    },
    methods: {
        statusFor(entry) {
            const hasError = ERROR_PATTERN.test((entry.log || '').toLowerCase())
            if (hasError) {
                return {key: 'error', type: 'is-danger', icon: 'alert-circle', label: 'Synkroniseringsfejl'}
            }
            const isStale = moment().diff(moment(entry.createdAt), 'hours') >= STALE_HOURS
            if (isStale) {
                return {key: 'stale', type: 'is-warning', icon: 'sync-alert', label: 'Ikke synkroniseret for nylig'}
            }
            return {key: 'ok', type: 'is-success', icon: 'check-circle', label: 'Synkroniseret'}
        }
    }
}
</script>
