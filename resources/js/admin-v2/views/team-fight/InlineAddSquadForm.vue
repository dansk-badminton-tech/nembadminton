<template>
    <div dusk="add-teams-section" class="add-squad-form">
        <div class="add-squad-form__panel">
            <div class="add-squad-form__panel-row">
                <div class="add-squad-form__section add-squad-form__section--match-count">
                    <p class="label is-small mb-2">Antal kampe</p>
                    <div class="buttons has-addons mb-0">
                        <b-button
                            v-for="matchCount in matchCountOptions"
                            :key="matchCount"
                            size="is-small"
                            :disabled="loading"
                            :type="selectedMatchCount === matchCount ? 'is-link' : 'is-light'"
                            @click="$emit('select-match-count', matchCount)">
                            {{ matchCount }}
                        </b-button>
                        <b-button
                            size="is-small"
                            dusk="custom-match-count-toggle"
                            :disabled="loading"
                            :type="isCustom ? 'is-link' : 'is-light'"
                            @click="$emit('select-match-count', 'custom')">
                            Tilpas
                        </b-button>
                    </div>
                </div>

                <div class="add-squad-form__section">
                    <p class="label is-small mb-2">Spilledato</p>
                    <div class="buttons has-addons are-small mb-2">
                        <b-button
                            v-for="quickDate in quickDateOptions"
                            :key="quickDate.offset"
                            size="is-small"
                            :type="quickDate.type"
                            :disabled="loading"
                            @click="$emit('select-quick-date', quickDate.offset)">
                            {{ quickDate.label }}
                        </b-button>
                        <p class="control add-squad-form__datepicker-control">
                            <b-datetimepicker
                                :value="selectedPlayingDate"
                                :disabled="loading"
                                :mobile-native="false"
                                icon="calendar-today"
                                locale="da-DK"
                                editable
                                position="is-top-left"
                                @input="$emit('change-playing-date', $event)">
                            </b-datetimepicker>
                        </p>
                    </div>
                    <p class="help">{{ recommendedRankingLabel }}</p>
                </div>

                <div v-if="isCustom" class="add-squad-form__custom-row">
                    <p class="label is-small mb-2">Tilpas kampkategorier</p>
                    <div class="add-squad-form__custom-grid">
                        <div
                            v-for="field in customCategoryFields"
                            :key="field.key"
                            class="add-squad-form__custom-field">
                            <p class="label is-small mb-1">{{ field.label }}</p>
                            <b-numberinput
                                :value="customCategoryCounts[field.key]"
                                :min="0"
                                :max="20"
                                :disabled="loading"
                                controls-position="compact"
                                size="is-small"
                                @input="emitCustomCategoryCount(field.key, $event)">
                            </b-numberinput>
                        </div>
                    </div>
                    <p class="help mt-2">
                        I alt {{ customTotalMatchCount }} kampe
                    </p>
                </div>

                <div class="add-squad-form__section add-squad-form__section--tier">
                    <p class="label is-small mb-2">Hold / Navn / Niveau (valgfri)</p>

                    <div
                        v-if="teamSelected"
                        class="add-squad-form__team-chip"
                        dusk="squad-team-chip">
                        <b-icon icon="shield-account" size="is-small" class="mr-2"/>
                        <span class="add-squad-form__team-chip-label">{{ selectedTeamLabel }}</span>
                        <b-button
                            class="add-squad-form__team-chip-clear"
                            type="is-text"
                            size="is-small"
                            icon-right="close"
                            :disabled="loading"
                            dusk="clear-squad-team"
                            @click="$emit('select-team', null)">
                        </b-button>
                    </div>

                    <template v-else>
                        <b-autocomplete
                            v-if="teamOptions.length > 0"
                            :value="selectedTeamLabel"
                            :data="filteredTeamOptions"
                            :loading="teamsLoading"
                            :disabled="loading"
                            class="mb-2"
                            placeholder="Vælg fra hold"
                            field="label"
                            clearable
                            keep-first
                            open-on-focus
                            dusk="squad-team-input"
                            @input="onTeamInput"
                            @select="onTeamSelect">
                        </b-autocomplete>
                        <b-input
                            :value="selectedName"
                            :disabled="loading"
                            class="mb-2"
                            placeholder="Navn (fx Højbjerg 1)"
                            dusk="squad-name-input"
                            @input="onNameInput">
                        </b-input>
                        <b-autocomplete
                            :value="selectedTierName"
                            :data="filteredTierOptions"
                            :loading="tiersLoading"
                            :disabled="loading"
                            placeholder="Niveau (fx 1. division)"
                            field="label"
                            clearable
                            keep-first
                            open-on-focus
                            dusk="squad-tier-input"
                            @input="onTierInput"
                            @select="onTierSelect">
                        </b-autocomplete>
                        <p class="help has-text-grey mb-0">
                            Navn og niveau er valgfrie. Vælg fra et eksisterende hold for at prefillede begge.
                        </p>
                    </template>
                </div>
            </div>

            <div class="add-squad-form__submit-row">
                <b-button
                    dusk="add-13-kamps-hold-button"
                    class="add-squad-form__submit"
                    :loading="loading"
                    :disabled="!submitEnabled"
                    type="is-link"
                    icon-left="plus"
                    @click="$emit('submit-inline')">
                    Tilføj hold
                </b-button>
            </div>
        </div>
    </div>
</template>

<script>
const CUSTOM_CATEGORY_FIELDS = Object.freeze([
    {key: 'mix', label: 'MD'},
    {key: 'womenSingles', label: 'DS'},
    {key: 'womenDoubles', label: 'DD'},
    {key: 'mensSingles', label: 'HS'},
    {key: 'mensDoubles', label: 'HD'}
]);

export default {
    name: "InlineAddSquadForm",
    props: {
        loading: {
            type: Boolean,
            default: false
        },
        tiersLoading: {
            type: Boolean,
            default: false
        },
        teamsLoading: {
            type: Boolean,
            default: false
        },
        teamOptions: {
            type: Array,
            default: () => []
        },
        selectedTeamLabel: {
            type: String,
            default: ''
        },
        selectedMatchCount: {
            type: [Number, String],
            default: null
        },
        selectedName: {
            type: String,
            default: ''
        },
        selectedTierName: {
            type: String,
            default: ''
        },
        selectedPlayingDate: {
            type: Date,
            default: null
        },
        matchCountOptions: {
            type: Array,
            default: () => []
        },
        tierOptions: {
            type: Array,
            default: () => []
        },
        quickDateOptions: {
            type: Array,
            default: () => []
        },
        recommendedRankingLabel: {
            type: String,
            default: ''
        },
        nextSquadNumber: {
            type: Number,
            default: 1
        },
        customCategoryCounts: {
            type: Object,
            default: () => ({mix: 0, womenSingles: 0, womenDoubles: 0, mensSingles: 0, mensDoubles: 0})
        },
        customTotalMatchCount: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            customCategoryFields: CUSTOM_CATEGORY_FIELDS
        }
    },
    computed: {
        isCustom() {
            return this.selectedMatchCount === 'custom';
        },
        submitEnabled() {
            if (this.isCustom) {
                return this.customTotalMatchCount > 0;
            }
            return this.selectedMatchCount !== null;
        },
        teamSelected() {
            return Boolean(this.selectedTeamLabel && this.selectedTeamLabel.trim() !== '');
        },
        trimmedTierName() {
            return (this.selectedTierName || '').trim();
        },
        filteredTierOptions() {
            const query = this.trimmedTierName.toLowerCase();
            if (query === '') {
                return this.tierOptions;
            }
            return this.tierOptions.filter((option) =>
                option.label.toLowerCase().includes(query)
            );
        },
        filteredTeamOptions() {
            const query = (this.selectedTeamLabel || '').trim().toLowerCase();
            if (query === '') {
                return this.teamOptions;
            }
            return this.teamOptions.filter((option) =>
                option.label.toLowerCase().includes(query)
            );
        }
    },
    methods: {
        onNameInput(value) {
            this.$emit('select-name', typeof value === 'string' ? value : '');
        },
        onTierInput(value) {
            this.$emit('select-tier', typeof value === 'string' ? value : '');
        },
        onTierSelect(option) {
            if (option && typeof option.label === 'string') {
                this.$emit('select-tier', option.label);
            }
        },
        onTeamInput(value) {
            if (typeof value === 'string' && value.trim() === '') {
                this.$emit('select-team', null);
            }
        },
        onTeamSelect(option) {
            if (option && option.team) {
                this.$emit('select-team', option.team);
            }
        },
        emitCustomCategoryCount(field, value) {
            this.$emit('update-custom-category-count', {field, value});
        }
    }
}
</script>

<style scoped>
.add-squad-form {
    margin-top: 1rem;
}

.add-squad-form__panel {
    padding: 0.85rem 1rem 0.75rem;
    border: 1px solid #dbdbdb;
    border-radius: 8px;
    background: #fafafa;
}

.add-squad-form__panel-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-start;
}

.add-squad-form__section {
    min-width: 230px;
    flex: 1 1 230px;
}

.add-squad-form__section--match-count {
    min-width: 280px;
}

.add-squad-form__section--tier {
    min-width: 260px;
}

.add-squad-form__datepicker-control {
    min-width: 210px;
}

.add-squad-form__custom-row {
    margin-bottom: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px dashed #dbdbdb;
}

.add-squad-form__custom-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(96px, 1fr));
    gap: 0.75rem;
    max-width: 720px;
}

.add-squad-form__custom-field .label {
    text-align: center;
    color: #4a4a4a;
}

.add-squad-form__team-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.35rem 0.5rem 0.35rem 0.75rem;
    border: 1px solid #b5b5b5;
    border-radius: 999px;
    background: #fff;
    color: #363636;
    font-size: 0.95rem;
    max-width: 100%;
}

.add-squad-form__team-chip-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.add-squad-form__team-chip-clear {
    margin-left: 0.25rem;
    padding: 0;
    color: #7a7a7a;
}

.add-squad-form__submit-row {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.85rem;
}

@media (max-width: 1023px) {
    .add-squad-form__custom-grid {
        grid-template-columns: repeat(3, minmax(80px, 1fr));
    }

    .add-squad-form__submit-row {
        justify-content: stretch;
    }

    .add-squad-form__submit {
        width: 100%;
    }
}
</style>
