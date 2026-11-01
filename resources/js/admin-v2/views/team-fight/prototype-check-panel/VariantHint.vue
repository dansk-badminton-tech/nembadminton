<template>
    <!-- PROTOTYPE round 4: F1 (B2 sections, chevron fold-out) where every row folds and starts folded; details only on
         click, since the player markings in the team table stay. Unfolded: what the Check tests, then its reason or findings.
         Only the hint that rows can be clicked differs: caption (H1), "Detaljer" label (H2), "Vis detaljer" on hover (H3). -->
    <div class="box p-0">
        <p v-if="hint === 'caption'" class="is-size-7 has-text-grey px-3 py-2">
            <b-icon icon="information-outline" size="is-small"></b-icon> Klik på et tjek for at se detaljer.
        </p>
        <table class="table is-fullwidth is-narrow is-hoverable mb-0">
            <template v-for="section in sections" :key="section.key">
                <tbody v-if="section.checks.length">
                <tr class="has-background-white-ter">
                    <td :colspan="columns" class="is-size-7 has-text-weight-semibold" :class="section.class">
                        {{ section.title }} ({{ section.checks.length }})
                    </td>
                </tr>
                <template v-for="check in section.checks" :key="check.key">
                    <tr class="proto-check-row" style="cursor: pointer" role="button" :aria-expanded="isOpen(check)"
                        :title="hint === 'hover' ? 'Vis detaljer' : null" @click="toggle(check)">
                        <td v-if="hint !== 'label'" style="width: 1.5rem" class="pr-0">
                            <b-icon :icon="isOpen(check) ? 'chevron-down' : 'chevron-right'" size="is-small"></b-icon>
                        </td>
                        <td style="width: 2rem"><b-icon :icon="status(check).icon" :type="iconType(check)" size="is-small"></b-icon></td>
                        <td style="white-space: nowrap">
                            <strong v-if="section.key === 'action'">{{ check.name }}</strong>
                            <span v-else>{{ check.name }}</span>
                            <span class="has-text-grey is-size-7 ml-1">{{ check.rule }}</span>
                            <span v-if="check.perPlayer" class="tag is-white is-small ml-1">pr. spiller</span>
                        </td>
                        <td class="is-size-7" style="white-space: nowrap">
                            <span class="tag is-small mr-2" :class="status(check).tag">{{ status(check).text }}</span>
                            <span v-if="check.details.length" class="has-text-grey">{{ check.details.length }} fund</span>
                        </td>
                        <td v-if="hint === 'label'" class="has-text-right has-text-grey is-size-7" style="white-space: nowrap">
                            Detaljer <b-icon :icon="isOpen(check) ? 'chevron-up' : 'chevron-down'" size="is-small"></b-icon>
                        </td>
                        <td v-if="hint === 'hover'" class="has-text-right is-size-7" style="white-space: nowrap; width: 6rem">
                            <span class="proto-hover-hint has-text-link">{{ isOpen(check) ? 'Skjul' : 'Vis detaljer' }}</span>
                        </td>
                    </tr>
                    <tr v-if="isOpen(check)">
                        <td :colspan="hint === 'label' ? 1 : 2"></td>
                        <td :colspan="hint === 'caption' ? 2 : 3" class="is-size-7 pt-0">
                            <p class="has-text-grey">{{ check.description }}</p>
                            <p v-if="check.reason" class="mt-1">{{ check.reason }}</p>
                            <ul v-if="check.details.length" class="mt-1"><li v-for="line in check.details" :key="line">{{ line }}</li></ul>
                        </td>
                    </tr>
                </template>
                </tbody>
            </template>
            <tbody>
            <tr style="cursor: pointer" :aria-expanded="showInactive" @click="showInactive = !showInactive">
                <td :colspan="columns" class="has-background-white-ter is-size-7 has-text-grey-dark">
                    <b-icon :icon="showInactive ? 'chevron-down' : 'chevron-right'" size="is-small"></b-icon>
                    {{ notApplicable.length }} tjek gælder ikke for denne opstilling
                </td>
            </tr>
            <template v-if="showInactive">
                <tr v-for="check in notApplicable" :key="check.key" class="has-text-grey is-size-7">
                    <td v-if="hint !== 'label'"></td>
                    <td><b-icon icon="minus" size="is-small"></b-icon></td>
                    <td>{{ check.name }} {{ check.rule }}</td>
                    <td :colspan="hint === 'caption' ? 1 : 2">{{ check.reason }}</td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantHint',
    hasSwitches: false,
    props: {checks: Array, switches: Object, hint: String},
    data() {
        return {showInactive: false, open: {}}
    },
    computed: {
        columns() {
            return this.hint === 'caption' ? 4 : 5
        },
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
        },
        isOpen(check) {
            return this.open[check.key] ?? false
        },
        toggle(check) {
            this.open = {...this.open, [check.key]: !this.isOpen(check)}
        }
    }
}
</script>
<style>
.proto-hover-hint {
    visibility: hidden;
}
.proto-check-row:hover .proto-hover-hint,
.proto-check-row[aria-expanded="true"] .proto-hover-hint {
    visibility: visible;
}
</style>
