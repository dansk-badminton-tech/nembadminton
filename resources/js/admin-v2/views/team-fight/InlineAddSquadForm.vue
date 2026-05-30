<template>
    <div dusk="add-teams-section" class="add-squad-form">
        <div class="add-squad-form__bar">
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

            <div class="add-squad-form__summary">
                <span class="add-squad-form__chip">{{ matchCountSummary }}</span>
                <span class="add-squad-form__chip-sep">·</span>
                <span class="add-squad-form__chip">{{ dateSummary }}</span>
                <template v-if="recommendedRankingLabel">
                    <span class="add-squad-form__chip-sep">·</span>
                    <span class="add-squad-form__chip">{{ recommendedRankingLabel }}</span>
                </template>
                <template v-if="trimmedTierName">
                    <span class="add-squad-form__chip-sep">·</span>
                    <span class="add-squad-form__chip">Niveau: {{ trimmedTierName }}</span>
                </template>
            </div>

            <b-button
                dusk="customize-squad-form-toggle"
                class="add-squad-form__toggle"
                type="is-text"
                size="is-small"
                :icon-left="expanded ? 'pencil-off' : 'pencil'"
                :aria-expanded="expanded ? 'true' : 'false'"
                @click="expanded = !expanded">
                {{ expanded ? 'Skjul' : 'Tilpas' }}
            </b-button>
        </div>

        <div v-if="expanded" class="add-squad-form__panel">
            <div class="add-squad-form__panel-row">
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
                    <div v-if="isCustom" class="add-squad-form__custom-grid mt-3">
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
                    <p v-if="isCustom" class="help mt-1">
                        I alt {{ customTotalMatchCount }} kampe
                    </p>
                </div>

                <div class="add-squad-form__section">
                    <p class="label is-small mb-2">Niveau (valgfri)</p>
                    <b-autocomplete
                        :value="selectedTierName"
                        :data="filteredTierOptions"
                        :loading="tiersLoading"
                        :disabled="loading"
                        placeholder="Fx 1. division"
                        field="label"
                        clearable
                        keep-first
                        open-on-focus
                        dusk="squad-tier-input"
                        @input="onTierInput"
                        @select="onTierSelect">
                    </b-autocomplete>
                    <p class="help has-text-grey mb-0">
                        Bruges som navn på holdet (fx "1. division").
                    </p>
                </div>
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
        selectedMatchCount: {
            type: [Number, String],
            default: null
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
            expanded: false,
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
        matchCountSummary() {
            if (this.isCustom) {
                return `${this.customTotalMatchCount} kampe (tilpas)`;
            }
            if (this.selectedMatchCount === null) {
                return 'Vælg antal kampe';
            }
            return `${this.selectedMatchCount} kampe`;
        },
        dateSummary() {
            if (!this.selectedPlayingDate) {
                return 'Ingen dato valgt';
            }
            return this.selectedPlayingDate.toLocaleDateString('da-DK', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
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
        }
    },
    watch: {
        selectedTierName: {
            immediate: true,
            handler(value) {
                if (value && value.trim() !== '') {
                    this.expanded = true;
                }
            }
        },
        selectedMatchCount: {
            immediate: true,
            handler(value) {
                if (value === 'custom') {
                    this.expanded = true;
                }
            }
        }
    },
    methods: {
        onTierInput(value) {
            this.$emit('select-tier', typeof value === 'string' ? value : '');
        },
        onTierSelect(option) {
            if (option && typeof option.label === 'string') {
                this.$emit('select-tier', option.label);
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

.add-squad-form__bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}

.add-squad-form__submit {
    flex: 0 0 auto;
}

.add-squad-form__summary {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
    color: #4a4a4a;
    font-size: 0.9rem;
}

.add-squad-form__chip {
    white-space: nowrap;
}

.add-squad-form__chip-sep {
    color: #b5b5b5;
}

.add-squad-form__toggle {
    margin-left: auto;
}

.add-squad-form__panel {
    margin-top: 0.75rem;
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
}

.add-squad-form__section--match-count {
    min-width: 280px;
}

.add-squad-form__datepicker-control {
    min-width: 210px;
}

.add-squad-form__custom-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(64px, 1fr));
    gap: 0.5rem;
}

.add-squad-form__custom-field .label {
    text-align: center;
    color: #4a4a4a;
}

@media (max-width: 1023px) {
    .add-squad-form__toggle {
        margin-left: 0;
    }

    .add-squad-form__custom-grid {
        grid-template-columns: repeat(3, minmax(64px, 1fr));
    }
}
</style>
