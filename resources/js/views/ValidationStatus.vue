<template>
    <div class="field is-grouped is-grouped-multiline">
        <div class="control">
            <div class="tags has-addons">
                <b-tooltip
                    label="Ugyldig kategori. Fx Kun en dame er sat i 1. DD, Ingen spiller i 1. HS osv">
                    <span class="tag is-light is-medium">Fuldendt hold</span>
                    <span
                        v-bind:class="{'is-light': this.incompleteTeam === null, 'is-danger': this.incompleteTeam === true, 'is-success': !!this.incompleteTeam === false}"
                        class="tag is-medium">{{ incompleteTeamText }}</span>
                </b-tooltip>
            </div>
        </div>
        <div class="control">
            <b-tooltip
                label="§ 38. stk. 4. Spiller på et forkert hold (NIVEAU-ranglisten)">
                <div class="tags has-addons">
                    <span class="tag is-light is-medium">Styrkeorden niveau-ranglisten (på tværs af hold)</span>
                    <span
                        v-bind:class="{'is-light': this.invalidLevel === null, 'is-danger': this.invalidLevel === true, 'is-success': !!this.invalidLevel === false}"
                        class="tag is-medium">{{ invalidLevelText }}</span>
                </div>
            </b-tooltip>
        </div>
        <div class="control">
            <b-tooltip
                label="§ 38. stk. 1-3. Spiller for højt i kategorien (KATEGORI-ranglisten)">
                <div class="tags has-addons">
                    <span class="tag is-light is-medium">Styrkeorden kategori-ranglisten (på holdet)</span>
                    <span
                        v-bind:class="{'is-light': this.invalidCategory === null, 'is-danger': this.invalidCategory === true, 'is-success': !!this.invalidCategory === false}"
                        class="tag is-medium">{{ invalidCategoryText }}</span>
                </div>
            </b-tooltip>
        </div>
    </div>
</template>
<script>
export default {
    name: 'ValidationStatus',
    props: ['incompleteTeam', 'invalidCategory', 'invalidLevel'],
    computed: {
        incompleteTeamText() {
            return (this.incompleteTeam === null ? '-' : (this.incompleteTeam ? 'Fejl' : 'OK'))
        },
        invalidCategoryText() {
            return (this.invalidCategory === null ? '-' : (this.invalidCategory ? 'Fejl' : 'OK'))
        },
        invalidLevelText() {
            return (this.invalidLevel === null ? '-' : (this.invalidLevel ? 'Fejl' : 'OK'))
        },
    }
}
</script>
