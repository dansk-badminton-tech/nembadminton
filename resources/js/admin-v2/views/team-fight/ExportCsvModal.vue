<template>
    <div class="modal-card" style="width: auto; min-width: 320px;">
        <header class="modal-card-head">
            <p class="modal-card-title">Eksporter CSV</p>
            <button
                type="button"
                class="delete"
                @click="$emit('close')"/>
        </header>
        <section class="modal-card-body">
            <b-field>
                <b-checkbox v-model="excludeCategories" dusk="team-round-csv-exclude-categories-checkbox">
                    Ekskluder kategorier
                </b-checkbox>
            </b-field>
            <!-- PROTOTYPE #227: reserves in the CSV -->
            <div v-if="prototypeVariant" class="prototype-box" dusk="prototype-csv-preview">
                <p class="is-size-7 mb-2"><strong>Prototype {{ prototypeVariant }}:</strong> CSV'en får disse linjer efter hvert holds kategorier.
                    Den downloadede fil har dem ikke endnu.</p>
                <p v-if="!reserveLines.length" class="is-size-7 has-text-grey">Ingen reserver markeret.</p>
                <pre v-for="squad in reserveLines" :key="squad.hold" class="is-size-7 p-2 mb-2">"{{ squad.hold }}"
…
{{ squad.lines.join('\n') }}</pre>
            </div>
        </section>
        <footer class="modal-card-foot">
            <b-button label="Annuller" @click="$emit('close')"/>
            <b-button label="Download CSV" type="is-primary" dusk="team-round-csv-download-button" icon-left="download" @click="exportCSV"/>
        </footer>
    </div>
</template>

<script>
import {csvReserveLines} from "./reserve-prototype/reserve-prototype.js";

export default {
    name: "ExportCsvModal",
    emits: ['close', 'export'],
    props: {
        // PROTOTYPE #227
        prototypeVariant: {type: String, default: null},
        squads: {type: Array, default: () => []}
    },
    computed: {
        reserveLines() {
            return csvReserveLines(this.prototypeVariant, this.squads, !this.excludeCategories)
        }
    },
    data() {
        return {
            excludeCategories: false
        }
    },
    methods: {
        exportCSV() {
            this.$emit('export', { includeCategories: !this.excludeCategories });
            this.$emit('close');
        }
    }
}
</script>

<style scoped>
.prototype-box {
    border: 2px dashed #8b5cf6;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    background: #faf5ff;
}
</style>
