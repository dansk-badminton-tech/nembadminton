<template>
    <!-- PROTOTYPE variant A: today's tag row. Active Checks first; inactive ones as a muted tail with the reason in a tooltip. Switches inline. -->
    <div>
        <div class="field is-grouped is-grouped-multiline">
            <div class="control" v-for="check in active" :key="check.key">
                <b-tooltip type="is-info" :label="check.reason || tip(check)" multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">{{ label(check) }}</span>
                        <span class="tag is-medium" :class="status(check).tag">
                            <b-icon :icon="status(check).icon" size="is-small" class="mr-1"></b-icon>
                            {{ status(check).text }}
                        </span>
                        <span v-if="check.switchable" class="tag is-medium is-white">
                            <b-switch size="is-small" :model-value="switches[check.key] !== false" @update:modelValue="$emit('toggle', check.key, $event)"></b-switch>
                        </span>
                    </div>
                </b-tooltip>
            </div>
        </div>
        <div class="field is-grouped is-grouped-multiline is-align-items-center mt-1" v-if="inactive.length">
            <span class="control is-size-7 has-text-grey">Ikke aktive:</span>
            <div class="control" v-for="check in inactive" :key="check.key">
                <b-tooltip type="is-dark" :label="check.reason" multilined>
                    <span class="tag is-light has-text-grey">
                        <b-icon :icon="status(check).icon" size="is-small" class="mr-1"></b-icon>
                        {{ label(check) }} · {{ status(check).text.toLowerCase() }}
                        <b-switch v-if="check.switchable && check.status === 'off'" size="is-small" class="ml-2" :model-value="false" @update:modelValue="$emit('toggle', check.key, $event)"></b-switch>
                    </span>
                </b-tooltip>
            </div>
        </div>
        <article v-if="details.length" class="message is-warning is-small mt-3">
            <div class="message-body">
                <ul>
                    <li v-for="line in details" :key="line">{{ line }}</li>
                </ul>
            </div>
        </article>
    </div>
</template>
<script>
import {STATUS} from './scenarios.js'

export default {
    name: 'VariantA',
    variantName: 'Tags med dæmpet hale',
    props: {checks: Array, switches: Object},
    emits: ['toggle'],
    computed: {
        active() {
            return this.checks.filter(check => STATUS[check.status].group !== 'inactive')
        },
        inactive() {
            return this.checks.filter(check => STATUS[check.status].group === 'inactive')
        },
        details() {
            return this.checks.flatMap(check => check.details)
        }
    },
    methods: {
        status(check) {
            return STATUS[check.status]
        },
        label(check) {
            return check.rule ? `${check.name} (${check.rule})` : check.name
        },
        tip(check) {
            return check.perPlayer ? 'Opsummering af markeringerne ved spillerne i holdtabellen.' : this.label(check)
        }
    }
}
</script>
