<template>
    <!-- PROTOTYPE (map #331 / ticket #332): three check panel variants on the team-round editor, switched by ?variant=A|B|C.
         Scenarios are fixture Lineups (one real Lineup can't show all four "no result" reasons); "Denne holdrunde" uses live data.
         Throwaway: lives on a prototype branch, never merged. -->
    <div class="prototype-frame mb-4">
        <b-field label="Prototype – scenarie" label-position="on-border" class="mb-3">
            <b-select v-model="scenarioKey" size="is-small">
                <option v-for="scenario in scenarios" :key="scenario.key" :value="scenario.key">{{ scenario.name }}</option>
            </b-select>
        </b-field>
        <component :is="variant" :key="`${variantKey}-${scenarioKey}`" :checks="checks" :switches="switches" @toggle="toggle"/>
    </div>
    <PrototypeSwitcher :variants="variantList"/>
</template>
<script>
import VariantA from './VariantA.vue'
import VariantB from './VariantB.vue'
import VariantC from './VariantC.vue'
import PrototypeSwitcher from '../../../components/PrototypeSwitcher.vue'
import {SCENARIOS, applySwitches} from './scenarios.js'

const VARIANTS = {A: VariantA, B: VariantB, C: VariantC}

export default {
    name: 'PrototypeCheckPanel',
    components: {PrototypeSwitcher},
    props: {live: Object},
    data() {
        return {scenarioKey: 'basic', switchState: {}}
    },
    computed: {
        variantKey() {
            return VARIANTS[this.$route.query.variant] ? this.$route.query.variant : 'A'
        },
        variant() {
            return VARIANTS[this.variantKey]
        },
        variantList() {
            return Object.entries(VARIANTS).map(([key, component]) => ({key, name: component.variantName}))
        },
        scenarios() {
            return [...SCENARIOS, this.live].filter(Boolean)
        },
        scenario() {
            return this.scenarios.find(scenario => scenario.key === this.scenarioKey) ?? this.scenarios[0]
        },
        switches() {
            // A variant without switches runs every Check that applies.
            const defaults = Object.fromEntries(this.scenario.checks.filter(check => check.switchable)
                .map(check => [check.key, this.variant.hasSwitches === false ? true : check.defaultOn]))
            return this.variant.hasSwitches === false ? defaults : {...defaults, ...this.switchState[this.scenarioKey]}
        },
        checks() {
            return applySwitches(this.scenario.checks, this.switches)
        }
    },
    methods: {
        toggle(key, value) {
            this.switchState = {...this.switchState, [this.scenarioKey]: {...this.switchState[this.scenarioKey], [key]: value}}
        }
    }
}
</script>
<style scoped>
.prototype-frame {
    border: 2px dashed #b86bff;
    border-radius: 6px;
    padding: 1.25rem 0.75rem 0.75rem;
}
</style>
