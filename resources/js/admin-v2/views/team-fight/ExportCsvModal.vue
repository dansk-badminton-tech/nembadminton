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
                <b-checkbox v-model="excludeCategories">
                    Ekskluder kategorier
                </b-checkbox>
            </b-field>
            <a href="/app/help/guides/del-den-officielle-holdopstilling" target="_blank">Sådan deler du den officielle holdopstilling</a>
        </section>
        <footer class="modal-card-foot">
            <b-button label="Annuller" @click="$emit('close')"/>
            <b-button label="Download CSV" type="is-primary" icon-left="download" @click="exportCSV"/>
        </footer>
    </div>
</template>

<script>
export default {
    name: "ExportCsvModal",
    emits: ['close', 'export'],
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
</style>
