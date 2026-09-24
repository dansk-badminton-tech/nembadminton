<template>
    <div class="validation-status-container">
        <div class="field is-grouped is-grouped-multiline">
            <div class="control" v-show="!hideIncompleteTeam">
                <b-tooltip
                    type="is-info"
                    :label="incompleteTeamTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Hold fuldendt</span>
                        <span
                            dusk="validation-incomplete-team"
                            :class="incompleteStatusClass"
                            class="tag is-medium">
                            <b-icon :icon="incompleteStatusIcon" size="is-small" class="mr-1"></b-icon>
                            {{ incompleteStatusText }}
                        </span>
                        <span class="tag is-medium is-white">
                            <b-switch
                                dusk="toggle-incomplete-team-check"
                                size="is-small"
                                :model-value="!ignoreIncompleteTeam"
                                @update:modelValue="$emit('update:ignoreIncompleteTeam', !$event)">
                            </b-switch>
                        </span>
                    </div>
                </b-tooltip>
            </div>
            <div class="control">
                <b-tooltip
                    type="is-info"
                    :label="invalidLevelTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Niveauvalidering</span>
                        <span
                            dusk="validation-invalid-level"
                            :class="statusClass(invalidLevel)"
                            class="tag is-medium">
                            <b-icon :icon="statusIcon(invalidLevel)" size="is-small" class="mr-1"></b-icon>
                            {{ statusText(invalidLevel) }}
                        </span>
                    </div>
                </b-tooltip>
            </div>
            <div class="control">
                <b-tooltip
                    type="is-info"
                    :label="invalidCategoryTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Kategorivalidering</span>
                        <span
                            dusk="validation-invalid-category"
                            :class="statusClass(invalidCategory)"
                            class="tag is-medium">
                            <b-icon :icon="statusIcon(invalidCategory)" size="is-small" class="mr-1"></b-icon>
                            {{ statusText(invalidCategory) }}
                        </span>
                    </div>
                </b-tooltip>
            </div>
        </div>

        <div v-if="hasErrors" class="mt-3">
            <b-collapse
                aria-id="contentIdForA11y1"
                class="panel is-danger"
                animation="slide"
                v-model="isOpen">
                <template #trigger>
                    <div
                        class="panel-heading"
                        role="button"
                        aria-controls="contentIdForA11y1"
                        :aria-expanded="isOpen">
                        <div class="is-flex is-justify-content-space-between">
                            <span>
                                <b-icon icon="alert-circle" size="is-small" class="mr-1"></b-icon>
                                <span>Detaljer om fejl</span>
                            </span>
                            <b-icon :icon="isOpen ? 'menu-up' : 'menu-down'"></b-icon>
                        </div>
                    </div>
                </template>
                <div class="panel-block">
                    <div class="content is-small">
                        <ul>
                            <li v-for="(error, index) in allErrors" :key="index" class="has-text-danger">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </b-collapse>
        </div>
    </div>
</template>
<script>
import {buildValidationErrors, invalidLevelTip} from './validation-status.js'

export default {
    name: 'ValidationStatus',
    props: {
        incompleteTeam: Boolean,
        invalidCategory: Boolean,
        invalidLevel: Boolean,
        hideIncompleteTeam: {
            type: Boolean,
            default: false
        },
        ignoreIncompleteTeam: {
            type: Boolean,
            default: false
        },
        basicSquads: {
            type: Array,
            default: () => []
        },
        invalidCategoryList: {
            type: Array,
            default: () => []
        },
        invalidLevelList: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            isOpen: false
        }
    },
    methods: {
        statusClass(status) {
            return {
                'is-light': status === null,
                'is-danger': status === true,
                'is-success': status === false
            }
        },
        statusIcon(status) {
            if (status === null) return 'minus-circle-outline'
            return status ? 'alert-circle' : 'check-circle'
        },
        statusText(status) {
            if (status === null) return 'Afventer'
            return status ? 'Fejl' : 'OK'
        }
    },
    computed: {
        incompleteStatusClass() {
            if (this.ignoreIncompleteTeam) return 'is-warning'
            return this.statusClass(this.incompleteTeam)
        },
        incompleteStatusIcon() {
            if (this.ignoreIncompleteTeam) return 'cancel'
            return this.statusIcon(this.incompleteTeam)
        },
        incompleteStatusText() {
            if (this.ignoreIncompleteTeam) return 'Deaktiveret'
            return this.statusText(this.incompleteTeam)
        },
        hasErrors() {
            return this.incompleteTeam === true || this.invalidCategory === true || this.invalidLevel === true;
        },
        allErrors() {
            return buildValidationErrors(this)
        },
        incompleteTeamTip() {
            if (this.ignoreIncompleteTeam) return 'Tjekket er slået fra. Hold valideres uden krav om fuld besætning. Slå til igen via kontakten.'
            if (this.incompleteTeam === null) return 'Afventer validering'
            return this.incompleteTeam ? 'Der findes et eller flere hold der mangler spillere. Dette skal rettes før øvrige tjek kan gennemføres.' : 'Alle hold er fuldt besat.'
        },
        invalidCategoryTip() {
            if (this.invalidCategory === null) return 'Deaktiveret indtil alle hold er fuldt besat'
            return this.invalidCategory ? 'En eller flere spillere spiller for højt i deres kategori (Bryder § 38. stk. 2 og 3).' : 'Kategoriordningen overholdes (§ 38. stk. 2 og 3).'
        },
        invalidLevelTip() {
            return invalidLevelTip(this.invalidLevel)
        }
    }
}
</script>
