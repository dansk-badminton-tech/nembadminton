<template>
    <div class="field is-grouped is-grouped-multiline">
        <div class="control" v-show="!hideIncompleteTeam">
            <div class="tags has-addons">
                <b-tooltip
                    :active="!loadingIncompleteTeam"
                    :label="incompleteTeamTip">
                    <b-taglist attached>
                        <b-tag type="is-light" size="is-medium">Fuldendt hold</b-tag>
                        <b-tag v-if="loadingIncompleteTeam"><b-icon icon="loading" custom-class="mdi-spin"/></b-tag>
                        <b-tag v-else :type="tagTypeIncompleteTeam" size="is-medium">{{ incompleteTeamText }}</b-tag>
                    </b-taglist>
                </b-tooltip>
            </div>
        </div>
        <div class="control">
            <b-tooltip
                :active="!loadingLevel"
                :label="invalidLevelTip">
                <b-taglist attached>
                    <b-tag type="is-light" size="is-medium">Spiller på et forkert hold</b-tag>
                    <b-tag v-if="loadingLevel"><b-icon icon="loading" custom-class="mdi-spin"/></b-tag>
                    <b-tag v-else :type="tagTypeLevel" size="is-medium">{{ invalidLevelText }}</b-tag>
                </b-taglist>
            </b-tooltip>
        </div>
        <div class="control">
            <b-tooltip
                :label="invalidCategoryTip"
                :active="!loadingCategory"
            >
                <b-taglist attached>
                    <b-tag type="is-light" size="is-medium">Spiller for højt i kategorien</b-tag>
                    <b-tag v-if="loadingCategory"><b-icon icon="loading" custom-class="mdi-spin"/></b-tag>
                    <b-tag v-else :type="tagTypeCategory" size="is-medium">{{ invalidCategoryText }}</b-tag>
                </b-taglist>
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
        loadingIncompleteTeam: Boolean,
        loadingCategory: Boolean,
        loadingLevel: Boolean,
        hideIncompleteTeam: {
            type: Boolean,
            default(){
                return false
            }
        }
    },
    computed: {
        tagTypeIncompleteTeam(){
            if(this.incompleteTeam === null){
                return 'is-light'
            }else if(this.incompleteTeam === true){
                return 'is-danger'
            }else{
                return 'is-success'
            }
        },
        tagTypeLevel(){
            if(this.invalidLevel === null){
                return 'is-light'
            }else if(this.invalidLevel === true){
                return 'is-danger'
            }else{
                return 'is-success'
            }
        },
        tagTypeCategory(){
            if(this.invalidCategory === null){
                return 'is-light'
            }else if(this.invalidCategory === true){
                return 'is-danger'
            }else{
                return 'is-success'
            }
        },
        incompleteTeamTip() {
            return (this.incompleteTeam === null ? '-' : (this.incompleteTeam ? 'Der findes et eller flere ugyldig hold. Skal være OK før de andre tjeks kører' : 'Alle kategorier er udfyldt korrekt.'))
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
