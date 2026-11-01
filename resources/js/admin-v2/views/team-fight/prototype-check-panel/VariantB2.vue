<template>
    <!-- PROTOTYPE variant B2: checklist sorted by urgency. Problems first with details; then waiting; then OK as one-liners.
         Per-player Checks are ordinary rows, marked "pr. spiller". Checks that don't apply fold away. No switches. -->
    <div class="box p-0">
        <table class="table is-fullwidth is-narrow mb-0">
            <template v-for="section in sections" :key="section.key">
                <tbody v-if="section.checks.length">
                <tr class="has-background-white-ter">
                    <td colspan="3" class="is-size-7 has-text-weight-semibold" :class="section.class">
                        {{ section.title }} ({{ section.checks.length }})
                    </td>
                </tr>
                <tr v-for="check in section.checks" :key="check.key">
                    <td style="width: 2.5rem"><b-icon :icon="status(check).icon" :type="iconType(check)" size="is-small"></b-icon></td>
                    <td style="white-space: nowrap">
                        <strong v-if="section.key === 'action'">{{ check.name }}</strong>
                        <span v-else>{{ check.name }}</span>
                        <span class="has-text-grey is-size-7 ml-1">{{ check.rule }}</span>
                        <span v-if="check.perPlayer" class="tag is-white is-small ml-1">pr. spiller</span>
                    </td>
                    <td class="is-size-7">
                        <span class="tag is-small mr-2" :class="status(check).tag">{{ status(check).text }}</span>
                        <template v-if="section.key !== 'ok'">
                            <template v-if="check.reason">{{ check.reason }}</template>
                            <ul v-else-if="check.details.length" class="mt-1"><li v-for="line in check.details" :key="line">{{ line }}</li></ul>
                        </template>
                    </td>
                </tr>
                </tbody>
            </template>
            <tbody>
            <tr>
                <td colspan="3" class="has-background-white-ter">
                    <a class="is-size-7 has-text-grey-dark" @click="showInactive = !showInactive">
                        <b-icon :icon="showInactive ? 'menu-down' : 'menu-right'" size="is-small"></b-icon>
                        {{ notApplicable.length }} tjek gælder ikke for denne opstilling
                    </a>
                </td>
            </tr>
            <template v-if="showInactive">
                <tr v-for="check in notApplicable" :key="check.key" class="has-text-grey is-size-7">
                    <td><b-icon icon="minus" size="is-small"></b-icon></td>
                    <td>{{ check.name }} {{ check.rule }}</td>
                    <td>{{ check.reason }}</td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantB2',
    variantName: 'Tjekliste sorteret efter handling',
    hasSwitches: false,
    props: {checks: Array, switches: Object},
    data() {
        return {showInactive: false}
    },
    computed: {
        sections() {
            const order = ['error', 'warning', 'cannot-verify']
            const by = group => this.checks.filter(check => check.status !== 'not-applicable' && STATUS[check.status].group === group)
            return [
                {key: 'action', title: 'Kræver handling', class: 'has-text-danger',
                    checks: by('action').sort((a, b) => order.indexOf(a.status) - order.indexOf(b.status))},
                {key: 'waiting', title: 'Venter', class: 'has-text-grey-dark', checks: [...by('waiting'), ...by('inactive')]},
                {key: 'ok', title: 'OK', class: 'has-text-success', checks: by('ok')},
            ]
        },
        notApplicable() {
            return this.checks.filter(check => check.status === 'not-applicable')
        }
    },
    methods: {
        status(check) {
            return STATUS[check.status]
        },
        iconType(check) {
            return {ok: 'is-success', error: 'is-danger', warning: 'is-warning', 'cannot-verify': 'is-warning'}[check.status] ?? 'is-grey'
        }
    }
}
</script>
