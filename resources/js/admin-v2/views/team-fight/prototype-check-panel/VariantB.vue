<template>
    <!-- PROTOTYPE variant B: a checklist, one row per Check with its result written out. No switches: every Check that
         applies runs. Checks that don't apply fold into one closing row. Per-player Checks get their own block. -->
    <div class="box p-0">
        <table class="table is-fullwidth is-narrow mb-0">
            <tbody>
            <tr v-for="check in lineupChecks" :key="check.key">
                <td style="width: 2.5rem"><b-icon :icon="status(check).icon" :type="iconType(check)"></b-icon></td>
                <td style="white-space: nowrap"><strong>{{ check.name }}</strong><br><span class="has-text-grey is-size-7">{{ check.rule }}</span></td>
                <td style="white-space: nowrap"><span class="tag" :class="status(check).tag">{{ status(check).text }}</span></td>
                <td class="is-size-7">
                    <template v-if="check.reason">{{ check.reason }}</template>
                    <ul v-else-if="check.details.length"><li v-for="line in check.details" :key="line">{{ line }}</li></ul>
                </td>
            </tr>
            <tr>
                <td colspan="4" class="has-background-white-ter">
                    <a class="is-size-7 has-text-grey-dark" @click="showInactive = !showInactive">
                        <b-icon :icon="showInactive ? 'menu-down' : 'menu-right'" size="is-small"></b-icon>
                        {{ notApplicable.length }} tjek gælder ikke for denne opstilling
                    </a>
                </td>
            </tr>
            <template v-if="showInactive">
                <tr v-for="check in notApplicable" :key="check.key" class="has-text-grey">
                    <td><b-icon icon="minus" size="is-small"></b-icon></td>
                    <td>{{ check.name }} <span class="is-size-7">{{ check.rule }}</span></td>
                    <td><span class="tag is-white">Gælder ikke</span></td>
                    <td class="is-size-7">{{ check.reason }}</td>
                </tr>
            </template>
            </tbody>
        </table>
        <div class="p-3 has-background-light">
            <p class="is-size-7 has-text-weight-semibold mb-1">Spillere</p>
            <div class="tags">
                <span v-for="check in playerChecks" :key="check.key" class="tag" :class="status(check).tag">
                    <b-icon :icon="status(check).icon" size="is-small" class="mr-1"></b-icon>
                    {{ check.name }}: {{ check.details.length ? `${check.details.length} spiller` : status(check).text }}
                </span>
            </div>
        </div>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantB',
    variantName: 'Tjekliste uden kontakter',
    hasSwitches: false,
    props: {checks: Array, switches: Object},
    data() {
        return {showInactive: false}
    },
    computed: {
        lineupChecks() {
            return this.checks.filter(check => !check.perPlayer && check.status !== 'not-applicable')
        },
        notApplicable() {
            return this.checks.filter(check => check.status === 'not-applicable')
        },
        playerChecks() {
            return this.checks.filter(check => check.perPlayer)
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
