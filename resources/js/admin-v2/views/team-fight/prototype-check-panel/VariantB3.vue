<template>
    <!-- PROTOTYPE variant B3: compact checklist. One line per Check with a short summary; click a row to unfold its details.
         Rows with a problem start unfolded. Checks that don't apply stay visible at the bottom, greyed, reason inline.
         Per-player Checks under their own "Spillere" header. No switches. -->
    <div class="box p-0">
        <table class="table is-fullwidth is-narrow is-hoverable mb-0">
            <template v-for="group in groups" :key="group.key">
                <tbody>
                <tr v-if="group.title">
                    <td colspan="4" class="is-size-7 has-text-weight-semibold has-background-white-ter">{{ group.title }}</td>
                </tr>
                <template v-for="check in group.checks" :key="check.key">
                    <tr :class="{'has-text-grey-light': check.status === 'not-applicable'}" style="cursor: pointer" @click="toggleOpen(check)">
                        <td style="width: 2.5rem"><b-icon :icon="status(check).icon" :type="iconType(check)" size="is-small"></b-icon></td>
                        <td style="white-space: nowrap">{{ check.name }} <span class="is-size-7 has-text-grey">{{ check.rule }}</span></td>
                        <td style="white-space: nowrap"><span class="tag is-small" :class="status(check).tag">{{ status(check).text }}</span></td>
                        <td class="is-size-7 has-text-grey">
                            {{ summary(check) }}
                            <b-icon v-if="hasMore(check)" :icon="isOpen(check) ? 'chevron-up' : 'chevron-down'" size="is-small" class="is-pulled-right"></b-icon>
                        </td>
                    </tr>
                    <tr v-if="isOpen(check)">
                        <td></td>
                        <td colspan="3" class="is-size-7 pt-0">
                            <ul><li v-for="line in check.details" :key="line">{{ line }}</li></ul>
                        </td>
                    </tr>
                </template>
                </tbody>
            </template>
        </table>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantB3',
    variantName: 'Kompakt tjekliste med fold-ud',
    hasSwitches: false,
    props: {checks: Array, switches: Object},
    data() {
        return {open: {}}
    },
    computed: {
        groups() {
            const lineup = this.checks.filter(check => !check.perPlayer)
            return [
                {key: 'lineup', checks: [...lineup.filter(c => c.status !== 'not-applicable'), ...lineup.filter(c => c.status === 'not-applicable')]},
                {key: 'player', title: 'Spillere', checks: this.checks.filter(check => check.perPlayer)},
            ]
        }
    },
    methods: {
        status(check) {
            return STATUS[check.status]
        },
        iconType(check) {
            return {ok: 'is-success', error: 'is-danger', warning: 'is-warning', 'cannot-verify': 'is-warning'}[check.status] ?? 'is-grey'
        },
        summary(check) {
            if (check.reason) return check.reason
            const count = check.details.length
            if (!count) return ''
            return count === 1 ? check.details[0] : `${count} fund`
        },
        hasMore(check) {
            return !check.reason && check.details.length > 1
        },
        isOpen(check) {
            return this.hasMore(check) && (this.open[check.key] ?? STATUS[check.status].group === 'action')
        },
        toggleOpen(check) {
            if (this.hasMore(check)) this.open = {...this.open, [check.key]: !this.isOpen(check)}
        }
    }
}
</script>
