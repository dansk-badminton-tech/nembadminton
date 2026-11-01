<template>
    <!-- PROTOTYPE variant B1: checklist B plus a switch column. A Check switched off stays in its row, muted, as "Slået fra".
         Checks that don't apply fold into one closing row and have no switch. -->
    <div class="box p-0">
        <table class="table is-fullwidth is-narrow mb-0">
            <thead>
            <tr class="is-size-7">
                <th></th>
                <th>Tjek</th>
                <th>Resultat</th>
                <th></th>
                <th class="has-text-right">Til</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="check in rows" :key="check.key" :class="{'has-text-grey': check.status === 'off'}">
                <td style="width: 2.5rem"><b-icon :icon="status(check).icon" :type="iconType(check)"></b-icon></td>
                <td style="white-space: nowrap">
                    <strong :class="{'has-text-grey': check.status === 'off'}">{{ check.name }}</strong>
                    <span v-if="check.perPlayer" class="tag is-white is-small">pr. spiller</span><br>
                    <span class="has-text-grey is-size-7">{{ check.rule }}</span>
                </td>
                <td style="white-space: nowrap"><span class="tag" :class="status(check).tag">{{ status(check).text }}</span></td>
                <td class="is-size-7">
                    <template v-if="check.reason">{{ check.reason }}</template>
                    <ul v-else-if="check.details.length"><li v-for="line in check.details" :key="line">{{ line }}</li></ul>
                </td>
                <td class="has-text-right">
                    <b-switch v-if="check.switchable" size="is-small" :model-value="switches[check.key] !== false"
                              :aria-label="`Kontrollér ${check.name}`" @update:modelValue="$emit('toggle', check.key, $event)"></b-switch>
                </td>
            </tr>
            <tr>
                <td colspan="5" class="has-background-white-ter">
                    <a class="is-size-7 has-text-grey-dark" @click="showInactive = !showInactive">
                        <b-icon :icon="showInactive ? 'menu-down' : 'menu-right'" size="is-small"></b-icon>
                        {{ notApplicable.length }} tjek gælder ikke for denne opstilling
                    </a>
                </td>
            </tr>
            <template v-if="showInactive">
                <tr v-for="check in notApplicable" :key="check.key" class="has-text-grey">
                    <td><b-icon icon="minus" size="is-small"></b-icon></td>
                    <td>{{ check.name }}<br><span class="is-size-7">{{ check.rule }}</span></td>
                    <td><span class="tag is-white">Gælder ikke</span></td>
                    <td class="is-size-7" colspan="2">{{ check.reason }}</td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantB1',
    variantName: 'Tjekliste med kontakter',
    props: {checks: Array, switches: Object},
    emits: ['toggle'],
    data() {
        return {showInactive: false}
    },
    computed: {
        rows() {
            return this.checks.filter(check => check.status !== 'not-applicable')
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
