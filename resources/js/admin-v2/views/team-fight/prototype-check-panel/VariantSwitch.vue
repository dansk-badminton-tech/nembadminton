<template>
    <!-- PROTOTYPE round 5: H2 (B2 sections; every row folds, starts folded, "Detaljer ▾" at the row end) with the switches
         back. Switched-off Checks get their own "Slået fra" section. Only where the switch lives differs:
         in the row (S1), inside the unfolded row (S2), in a "Tilpas tjek" menu (S3). -->
    <div class="box p-0">
        <div v-if="placement === 'menu'" class="is-flex is-justify-content-flex-end px-3 py-2">
            <b-dropdown position="is-bottom-left" :close-on-click="false" aria-role="menu">
                <template #trigger>
                    <b-button size="is-small" icon-left="tune" icon-right="menu-down">Tilpas tjek</b-button>
                </template>
                <b-dropdown-item custom v-for="check in switchable" :key="check.key">
                    <b-switch size="is-small" :model-value="switches[check.key] !== false" @update:modelValue="$emit('toggle', check.key, $event)">
                        {{ check.name }} <span class="has-text-grey is-size-7">{{ check.rule }}</span>
                    </b-switch>
                </b-dropdown-item>
            </b-dropdown>
        </div>
        <table class="table is-fullwidth is-narrow is-hoverable mb-0">
            <template v-for="section in sections" :key="section.key">
                <tbody v-if="section.checks.length">
                <tr class="has-background-white-ter">
                    <td :colspan="columns" class="is-size-7 has-text-weight-semibold" :class="section.class">
                        {{ section.title }} ({{ section.checks.length }})
                    </td>
                </tr>
                <template v-for="check in section.checks" :key="check.key">
                    <tr style="cursor: pointer" role="button" :aria-expanded="isOpen(check)" @click="toggle(check)"
                        :class="{'has-text-grey': check.status === 'off'}">
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
                        <td v-if="placement === 'row'" class="has-text-right" style="width: 3.5rem" @click.stop>
                            <b-switch v-if="check.switchable" size="is-small" :model-value="switches[check.key] !== false"
                                      :aria-label="`Kontrollér ${check.name}`" @update:modelValue="$emit('toggle', check.key, $event)"></b-switch>
                        </td>
                        <td class="has-text-right has-text-grey is-size-7" style="white-space: nowrap">
                            Detaljer <b-icon :icon="isOpen(check) ? 'chevron-up' : 'chevron-down'" size="is-small"></b-icon>
                        </td>
                    </tr>
                    <tr v-if="isOpen(check)">
                        <td></td>
                        <td :colspan="columns - 1" class="is-size-7 pt-0">
                            <p class="has-text-grey">{{ check.description }}</p>
                            <p v-if="check.status === 'off'" class="mt-1">{{ offReason }}</p>
                            <p v-else-if="check.reason" class="mt-1">{{ check.reason }}</p>
                            <ul v-if="check.details.length" class="mt-1"><li v-for="line in check.details" :key="line">{{ line }}</li></ul>
                            <b-switch v-if="placement === 'details' && check.switchable" size="is-small" class="mt-2"
                                      :model-value="switches[check.key] !== false" @update:modelValue="$emit('toggle', check.key, $event)">
                                Kontrollér dette tjek
                            </b-switch>
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
                    <td><b-icon icon="minus" size="is-small"></b-icon></td>
                    <td>{{ check.name }} {{ check.rule }}</td>
                    <td :colspan="columns - 2">{{ check.reason }}</td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantSwitch',
    props: {checks: Array, switches: Object, placement: String},
    emits: ['toggle'],
    data() {
        return {showInactive: false, open: {}}
    },
    computed: {
        columns() {
            return this.placement === 'row' ? 5 : 4
        },
        offReason() {
            return {
                row: 'Slået fra. Slå til med kontakten i rækken.',
                details: 'Slået fra. Slå til herunder.',
                menu: 'Slået fra. Slå til under Tilpas tjek.',
            }[this.placement]
        },
        sections() {
            const order = ['error', 'warning', 'cannot-verify']
            const applies = this.checks.filter(check => check.status !== 'not-applicable')
            const by = group => applies.filter(check => STATUS[check.status].group === group)
            return [
                {key: 'action', title: 'Kræver handling', class: 'has-text-danger',
                    checks: by('action').sort((a, b) => order.indexOf(a.status) - order.indexOf(b.status))},
                {key: 'waiting', title: 'Venter', class: 'has-text-grey-dark', checks: by('waiting')},
                {key: 'off', title: 'Slået fra', class: 'has-text-grey-dark', checks: applies.filter(check => check.status === 'off')},
                {key: 'ok', title: 'OK', class: 'has-text-success', checks: by('ok')},
            ]
        },
        switchable() {
            return this.checks.filter(check => check.switchable && check.status !== 'not-applicable')
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
