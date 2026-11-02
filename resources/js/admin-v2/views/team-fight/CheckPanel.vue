<template>
    <!-- The team-round editor's check panel (#339, prototype S2): every Check with its Check Status, in sections. Every
         row folds and starts folded; the Checks that don't apply fold into one closing row. -->
    <div class="box p-0 mb-4" dusk="check-panel">
        <table class="table is-fullwidth is-narrow is-hoverable mb-0">
            <template v-for="section in panel.sections" :key="section.key">
                <tbody v-if="section.rows.length" :dusk="`check-section-${section.key}`">
                <tr v-if="section.title" class="has-background-white-ter">
                    <td colspan="4" class="is-size-7 has-text-weight-semibold" :class="SECTION_CLASS[section.key]">
                        {{ section.title }} ({{ section.rows.length }})
                    </td>
                </tr>
                <template v-for="row in section.rows" :key="row.key">
                    <tr class="is-clickable" role="button" :aria-expanded="isOpen(row)" :dusk="`check-row-${row.key}`"
                        :class="{'has-text-grey': row.status === 'switched-off'}" @click="toggle(row)">
                        <td style="width: 2rem">
                            <b-icon :icon="look(row).icon" :type="look(row).type" size="is-small"
                                    :custom-class="row.state === 'loading' || row.refreshing ? 'mdi-spin' : ''"></b-icon>
                        </td>
                        <td>
                            <strong v-if="section.key === 'action'">{{ row.name }}</strong>
                            <span v-else>{{ row.name }}</span>
                            <span v-if="row.rule" class="has-text-grey is-size-7 ml-1">{{ row.rule }}</span>
                            <span v-if="row.perPlayer" class="tag is-white is-small ml-1">pr. spiller</span>
                        </td>
                        <td class="is-size-7" style="white-space: nowrap">
                            <template v-if="row.state === 'loading'">
                                <span class="has-text-grey" :dusk="`check-status-${row.key}`">Kontrollerer …</span>
                            </template>
                            <template v-else-if="row.state === 'failed'">
                                <span class="has-text-danger mr-2" :dusk="`check-status-${row.key}`">Kontrollen fejlede</span>
                                <b-button size="is-small" icon-left="refresh" :dusk="`check-retry-${row.key}`"
                                          @click.stop="$emit('retry', row.key)">Prøv igen</b-button>
                            </template>
                            <template v-else>
                                <span class="tag is-small mr-2" :class="look(row).tag" :dusk="`check-status-${row.key}`">{{ row.label }}</span>
                                <span v-if="row.findings.length" class="has-text-grey">{{ row.findings.length }} fund</span>
                            </template>
                        </td>
                        <td class="has-text-right has-text-grey is-size-7" style="white-space: nowrap">
                            Detaljer <b-icon :icon="isOpen(row) ? 'chevron-up' : 'chevron-down'" size="is-small"></b-icon>
                        </td>
                    </tr>
                    <tr v-if="isOpen(row)" :dusk="`check-details-${row.key}`">
                        <td></td>
                        <td colspan="3" class="is-size-7 pt-0">
                            <p class="has-text-grey">{{ row.description }}</p>
                            <p v-if="row.status === 'switched-off'" class="mt-1">Slået fra. Slå til herunder.</p>
                            <p v-else-if="row.state === 'failed'" class="mt-1">Tjekket kunne ikke køre. Prøv igen.</p>
                            <p v-else-if="row.reason" class="mt-1">{{ row.reason }}</p>
                            <ul v-if="row.findings.length" class="mt-1">
                                <li v-for="finding in row.findings" :key="`${finding.squadIndex}-${finding.refId}-${finding.message}`"
                                    :dusk="finding.refId ? `check-finding-${row.key}-${finding.squadIndex ?? 'x'}-${finding.refId}` : null"
                                    :class="finding.status === 'cannot-verify' ? '' : look({status: finding.status}).text">
                                    {{ finding.message }}
                                    <ul v-if="finding.uncountedRounds?.length">
                                        <li v-for="round in finding.uncountedRounds" :key="round.teamRoundId">
                                            <router-link :to="{name: 'team-fight-edit', params: {clubhouseId: $route.params.clubhouseId, teamUUID: round.teamRoundId}}"
                                                         target="_blank" rel="noopener" :dusk="`check-correction-${row.key}-${round.teamRoundId}`">{{ round.name }}<template v-if="round.round !== null"> (runde {{ round.round }})</template></router-link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                            <p v-if="NOTES[row.key] && row.findings.length" class="mt-1">{{ NOTES[row.key] }}</p>
                            <p v-if="GUIDES[row.key] && row.status !== 'switched-off'" class="mt-1">
                                <router-link :to="{name: 'help-guide', params: {slug: GUIDES[row.key].slug}, hash: GUIDES[row.key].hash}"
                                             target="_blank" rel="noopener" :dusk="`check-guide-${row.key}`">Læs mere</router-link>
                            </p>
                            <div class="is-flex is-align-items-center mt-2" style="gap: 1rem;">
                                <b-button v-if="READS_EARLIER_ROUNDS.includes(row.key) && row.status !== 'switched-off'"
                                          size="is-small" icon-left="refresh" :loading="row.refreshing"
                                          :dusk="`check-revalidate-${row.key}`" @click="$emit('revalidate')">
                                    Kontrollér igen
                                </b-button>
                                <b-switch v-if="row.switchable" size="is-small" :dusk="`check-switch-${row.key}`"
                                          :model-value="row.status !== 'switched-off'"
                                          @update:modelValue="$emit('toggle', row.key, $event)">
                                    Kontrollér dette tjek
                                </b-switch>
                            </div>
                        </td>
                    </tr>
                </template>
                </tbody>
            </template>
            <tbody v-if="panel.notApplicable.length">
            <tr class="is-clickable" role="button" :aria-expanded="showNotApplicable" dusk="check-not-applicable"
                @click="showNotApplicable = !showNotApplicable">
                <td colspan="4" class="has-background-white-ter is-size-7 has-text-grey-dark">
                    <b-icon :icon="showNotApplicable ? 'chevron-down' : 'chevron-right'" size="is-small"></b-icon>
                    {{ panel.notApplicable.length }} tjek gælder ikke for denne opstilling
                </td>
            </tr>
            <template v-if="showNotApplicable">
                <tr v-for="row in panel.notApplicable" :key="row.key" class="has-text-grey is-size-7"
                    :dusk="`check-not-applicable-${row.key}`">
                    <td><b-icon icon="minus" size="is-small"></b-icon></td>
                    <td>{{ row.name }} <span v-if="row.rule">{{ row.rule }}</span></td>
                    <td colspan="2">{{ row.reason }}</td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>

<script>
import {checkPanel} from './checks/check-panel.mjs'
import {PAPER_SQUAD_AGGRAVATION, TEAM_LINK_GUIDE} from './paper-squads.js'
import {PLAYOFF_PARTICIPATION_GUIDE} from './playoff-participation.js'

const LOOK = {
    passed: {icon: 'check-circle', type: 'is-success', tag: 'is-success', text: ''},
    failed: {icon: 'alert-circle', type: 'is-danger', tag: 'is-danger', text: 'has-text-danger'},
    warning: {icon: 'alert', type: 'is-warning', tag: 'is-warning', text: ''},
    'cannot-verify': {icon: 'help-circle', type: 'is-warning', tag: 'is-warning is-light', text: ''},
    waiting: {icon: 'timer-sand', type: 'is-grey', tag: 'is-light', text: ''},
    'switched-off': {icon: 'cancel', type: 'is-grey', tag: 'is-light', text: ''}
}

export default {
    name: 'CheckPanel',
    props: {
        // checkStatuses(...) from checks/check-status.mjs
        statuses: {type: Array, required: true},
        // {[request]: 'loading' | 'failed' | 'done'}, keyed like CHECKS[].requests
        requests: {type: Object, default: () => ({})}
    },
    emits: ['toggle', 'retry', 'revalidate'],
    data() {
        return {
            open: {},
            showNotApplicable: false,
            SECTION_CLASS: {action: 'has-text-danger', waiting: 'has-text-grey-dark', off: 'has-text-grey-dark', ok: 'has-text-success'},
            // The Checks that read earlier TeamRounds; "Kontrollér igen" picks up corrections made there.
            READS_EARLIER_ROUNDS: ['paperSquads', 'finalRound', 'qualification', 'playoff'],
            NOTES: {paperSquads: PAPER_SQUAD_AGGRAVATION},
            GUIDES: {
                paperSquads: {slug: TEAM_LINK_GUIDE},
                finalRound: {slug: TEAM_LINK_GUIDE},
                qualification: {slug: 'kontroller-spillerkrav-i-kvalifikation'},
                playoff: PLAYOFF_PARTICIPATION_GUIDE
            }
        }
    },
    computed: {
        panel() {
            return checkPanel({statuses: this.statuses, requests: this.requests})
        }
    },
    methods: {
        look(row) {
            if (row.state === 'loading' || row.refreshing) return {...(LOOK[row.status] ?? {}), icon: 'loading', type: 'is-grey'}
            if (row.state === 'failed') return {icon: 'alert-circle-outline', type: 'is-danger'}
            return LOOK[row.status] ?? LOOK.waiting
        },
        isOpen(row) {
            return this.open[row.key] ?? false
        },
        toggle(row) {
            this.open = {...this.open, [row.key]: !this.isOpen(row)}
        }
    }
}
</script>
