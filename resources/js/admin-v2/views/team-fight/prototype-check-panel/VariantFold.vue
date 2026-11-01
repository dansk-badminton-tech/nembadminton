<template>
    <!-- PROTOTYPE round 3: B2's sections (Kræver handling / Venter / OK / gælder ikke) with B3's fold-out rows.
         Only the cue that a row folds differs: chevron (F1), "Vis alle" link (F2), count button (F3).
         A row folds when it has more than one finding; problems start unfolded. -->
    <div class="box p-0">
        <table class="table is-fullwidth is-narrow mb-0" :class="{'is-hoverable': affordance === 'chevron'}">
            <template v-for="section in sections" :key="section.key">
                <tbody v-if="section.checks.length">
                <tr class="has-background-white-ter">
                    <td :colspan="columns" class="is-size-7 has-text-weight-semibold" :class="section.class">
                        {{ section.title }} ({{ section.checks.length }})
                    </td>
                </tr>
                <template v-for="check in section.checks" :key="check.key">
                    <tr :style="affordance === 'chevron' && foldable(check) ? 'cursor: pointer' : ''"
                        @click="affordance === 'chevron' && toggle(check)">
                        <td v-if="affordance === 'chevron'" style="width: 1.5rem" class="pr-0">
                            <b-icon v-if="foldable(check)" :icon="isOpen(check) ? 'chevron-down' : 'chevron-right'" size="is-small"
                                    :aria-expanded="isOpen(check)" role="button" :aria-label="`Vis fund for ${check.name}`"></b-icon>
                        </td>
                        <td style="width: 2rem"><b-icon :icon="status(check).icon" :type="iconType(check)" size="is-small"></b-icon></td>
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
                                <template v-else-if="!foldable(check)">{{ check.details[0] }}</template>
                                <template v-else-if="affordance === 'chevron' && !isOpen(check)"><span class="has-text-grey">{{ check.details.length }} fund</span></template>
                                <template v-else-if="affordance === 'link'">
                                    {{ check.details[0] }}
                                    <a class="has-text-link ml-1" :aria-expanded="isOpen(check)" @click="toggle(check)">
                                        {{ isOpen(check) ? 'Skjul' : `Vis alle ${check.details.length} fund` }}
                                        <b-icon :icon="isOpen(check) ? 'chevron-up' : 'chevron-down'" size="is-small"></b-icon>
                                    </a>
                                </template>
                            </template>
                        </td>
                        <td v-if="affordance === 'button'" class="has-text-right" style="width: 6.5rem">
                            <b-button v-if="foldable(check)" size="is-small" :icon-right="isOpen(check) ? 'chevron-up' : 'chevron-down'"
                                      :aria-expanded="isOpen(check)" @click="toggle(check)">
                                {{ check.details.length }} fund
                            </b-button>
                        </td>
                    </tr>
                    <tr v-if="foldable(check) && isOpen(check)">
                        <td :colspan="affordance === 'chevron' ? 3 : 2"></td>
                        <td :colspan="affordance === 'button' ? 2 : 1" class="is-size-7 pt-0">
                            <ul>
                                <li v-for="(line, index) in check.details" :key="line"
                                    v-show="affordance !== 'link' || index > 0">{{ line }}</li>
                            </ul>
                        </td>
                    </tr>
                </template>
                </tbody>
            </template>
            <tbody>
            <tr>
                <td :colspan="columns" class="has-background-white-ter">
                    <a class="is-size-7 has-text-grey-dark" :aria-expanded="showInactive" @click="showInactive = !showInactive">
                        <template v-if="affordance === 'link'">
                            {{ notApplicable.length }} tjek gælder ikke for denne opstilling –
                            <span class="has-text-link">{{ showInactive ? 'Skjul' : 'Vis' }}</span>
                        </template>
                        <template v-else>
                            <b-icon :icon="showInactive ? 'chevron-down' : 'chevron-right'" size="is-small"></b-icon>
                            {{ notApplicable.length }} tjek gælder ikke for denne opstilling
                        </template>
                    </a>
                </td>
            </tr>
            <template v-if="showInactive">
                <tr v-for="check in notApplicable" :key="check.key" class="has-text-grey is-size-7">
                    <td v-if="affordance === 'chevron'"></td>
                    <td><b-icon icon="minus" size="is-small"></b-icon></td>
                    <td>{{ check.name }} {{ check.rule }}</td>
                    <td :colspan="affordance === 'button' ? 2 : 1">{{ check.reason }}</td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantFold',
    hasSwitches: false,
    props: {checks: Array, switches: Object, affordance: String},
    data() {
        return {showInactive: false, open: {}}
    },
    computed: {
        columns() {
            return this.affordance === 'link' ? 3 : 4
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
        foldable(check) {
            return !check.reason && check.details.length > 1 && STATUS[check.status].group === 'action'
        },
        isOpen(check) {
            return this.open[check.key] ?? false
        },
        toggle(check) {
            if (this.foldable(check)) this.open = {...this.open, [check.key]: !this.isOpen(check)}
        }
    }
}
</script>
