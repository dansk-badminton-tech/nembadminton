<template>
    <!-- PROTOTYPE variant C: grouped by what the coach must do, not by Check. One headline, then columns:
         Kræver handling / Venter / OK / Ikke aktive. Switches live in a "Tilpas tjek" menu. -->
    <div class="box">
        <div class="level is-mobile mb-3">
            <div class="level-left">
                <p class="title is-5 mb-0" :class="headline.class">
                    <b-icon :icon="headline.icon" class="mr-1"></b-icon>{{ headline.text }}
                </p>
            </div>
            <div class="level-right">
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
        </div>

        <div v-for="check in groups.action" :key="check.key" class="notification is-light py-2 px-3 mb-2" :class="notificationType(check)">
            <strong>{{ check.name }}</strong> <span class="is-size-7">{{ check.rule }}</span> · {{ STATUS[check.status].text }}
            <p v-if="check.reason" class="is-size-7">{{ check.reason }}</p>
            <ul v-else class="is-size-7"><li v-for="line in check.details" :key="line">{{ line }}</li></ul>
        </div>

        <div class="columns is-size-7 mt-2">
            <div class="column" v-if="groups.waiting.length">
                <p class="has-text-weight-semibold mb-1"><b-icon icon="timer-sand" size="is-small"></b-icon> Venter</p>
                <p v-for="check in groups.waiting" :key="check.key">{{ check.name }}<span v-if="check.reason" class="has-text-grey"> – {{ check.reason }}</span></p>
            </div>
            <div class="column">
                <p class="has-text-weight-semibold mb-1 has-text-success"><b-icon icon="check-circle" size="is-small"></b-icon> OK</p>
                <p>{{ groups.ok.map(check => check.name).join(', ') || '–' }}</p>
            </div>
            <div class="column has-text-grey">
                <p class="has-text-weight-semibold mb-1"><b-icon icon="minus" size="is-small"></b-icon> Ikke aktive</p>
                <p v-for="check in groups.inactive" :key="check.key">
                    <b-tooltip :label="check.reason" multilined type="is-dark">
                        <span class="is-underlined-dotted">{{ check.name }}</span>
                    </b-tooltip>
                    · {{ check.status === 'off' ? 'slået fra' : 'gælder ikke' }}
                </p>
            </div>
        </div>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantC',
    variantName: 'Grupperet efter handling',
    props: {checks: Array, switches: Object},
    emits: ['toggle'],
    data() {
        return {STATUS}
    },
    computed: {
        groups() {
            const groups = {action: [], waiting: [], ok: [], inactive: []}
            this.checks.forEach(check => groups[STATUS[check.status].group].push(check))
            return groups
        },
        switchable() {
            return this.checks.filter(check => check.switchable && check.status !== 'not-applicable')
        },
        headline() {
            const count = status => this.checks.filter(check => check.status === status).length
            const errors = count('error'), warnings = count('warning') + count('cannot-verify')
            const advarsler = `${warnings} ${warnings === 1 ? 'advarsel' : 'advarsler'}`
            if (errors) return {text: warnings ? `${errors} fejl og ${advarsler}` : `${errors} fejl`, class: 'has-text-danger', icon: 'alert-circle'}
            if (warnings) return {text: advarsler, class: 'has-text-warning-dark', icon: 'alert'}
            return {text: 'Opstillingen overholder alle tjek', class: 'has-text-success', icon: 'check-circle'}
        }
    },
    methods: {
        notificationType(check) {
            return check.status === 'error' ? 'is-danger' : 'is-warning'
        }
    }
}
</script>
<style scoped>
.is-underlined-dotted {
    border-bottom: 1px dotted currentColor;
    cursor: help;
}
</style>
