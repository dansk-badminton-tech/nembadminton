<template>
    <div class="field is-grouped is-grouped-multiline">
        <div class="control" v-show="!hideIncompleteTeam">
            <div class="tags has-addons">
                <b-tooltip
                    type="is-info"
                    :label="incompleteTeamTip">
                    <span class="tag is-light is-medium">Fuldendt hold</span>
                    <span
                        v-bind:class="{'is-light': this.incompleteTeam === null, 'is-danger': this.incompleteTeam === true, 'is-success': !!this.incompleteTeam === false}"
                        class="tag is-medium">{{ incompleteTeamText }}</span>
                </b-tooltip>
            </div>
        </div>
        <div class="control">
            <b-tooltip
                type="is-info"
                :label="invalidLevelTip">
                <div class="tags has-addons">
                    <span class="tag is-light is-medium">Spiller på et forkert hold</span>
                    <span
                        v-bind:class="{'is-light': this.invalidLevel === null, 'is-danger': this.invalidLevel === true, 'is-success': !!this.invalidLevel === false}"
                        class="tag is-medium">{{ invalidLevelText }}</span>
                </div>
            </b-tooltip>
        </div>
        <div class="control">
            <b-tooltip
                type="is-info"
                :label="invalidCategoryTip">
                <div class="tags has-addons">
                    <span class="tag is-light is-medium">Spiller for højt i kategorien</span>
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
    props: {
        incompleteTeam: Boolean,
        invalidCategory: Boolean,
        invalidLevel: Boolean,
        hideIncompleteTeam: {
            type: Boolean,
            default(){
                return false
            }
        }
    },
    computed: {
        incompleteTeamTip() {
            return (this.incompleteTeam === null ? 'Deaktiveret af bruger' : (this.incompleteTeam ? 'Der findes et eller flere ugyldig hold. Skal være OK før de andre tjeks kører' : 'Alle kategorier er udfyldt korrekt.'))
        },
        incompleteTeamText() {
            return (this.incompleteTeam === null ? '-' : (this.incompleteTeam ? 'Fejl' : 'OK'))
        },
        invalidCategoryTip() {
            return this.invalidCategory === null ? 'Deaktiveret indtil alle hold er gyldig' : (this.invalidCategory ? 'Bryder § 38. stk. 4-5. i Holdturneringsreglement' : 'Overholder § 38. stk. 4-5. i Holdturneringsreglement')
        },
        invalidCategoryText() {
            return (this.invalidCategory === null ? '-' : (this.invalidCategory ? 'Fejl' : 'OK'))
        },
        invalidLevelTip() {
            return (this.invalidLevel === null ? 'Deaktiveret indtil alle hold er gyldig' : (this.invalidLevel ? 'Bryder § 38. stk. 1-3. i Holdturneringsreglement' : 'Overholder § 38. stk. 1-3. i Holdturneringsreglement'))
        },
        invalidLevelText() {
            return (this.invalidLevel === null ? '-' : (this.invalidLevel ? 'Fejl' : 'OK'))
        }
    }
}
</script>
