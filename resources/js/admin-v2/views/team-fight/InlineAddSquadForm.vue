<template>
    <div dusk="add-teams-section" class="add-squad-form">
        <div class="add-squad-form__panel">
            <div class="add-squad-form__team-section" dusk="squad-team-section">
                <p class="label is-small mb-2">Hold</p>

                <p v-if="noTeams" class="add-squad-form__team-empty is-size-7 mb-2" dusk="squad-team-empty">
                    Opret jeres hold først, så udfyldes navn og niveau automatisk.
                </p>

                <div v-if="!teamsLoading" class="add-squad-form__team-options">
                    <button
                        v-for="option in teamOptions"
                        :key="option.id"
                        type="button"
                        class="button is-link add-squad-form__team-card"
                        :class="{'is-outlined': !isSelectedTeam(option)}"
                        :dusk="'squad-team-option-' + option.id"
                        :disabled="loading"
                        :aria-pressed="isSelectedTeam(option) ? 'true' : 'false'"
                        :title="isSelectedTeam(option) ? 'Klik igen for at fravælge' : null"
                        @click="onTeamClick(option)">
                        <span class="add-squad-form__team-card-name">
                            <b-icon v-if="isSelectedTeam(option)" icon="check" size="is-small"/>
                            {{ option.label }}
                            <span
                                v-if="option.added"
                                class="add-squad-form__team-added"
                                dusk="squad-team-added">✓ tilføjet</span>
                        </span>
                        <span v-if="option.details" class="add-squad-form__team-card-details">
                            {{ option.details }}
                        </span>
                    </button>
                    <button
                        type="button"
                        class="button is-link is-light add-squad-form__team-card"
                        dusk="squad-create-team-option"
                        :disabled="loading"
                        @click="$emit('create-team')">
                        <span class="add-squad-form__team-card-name">
                            <b-icon icon="plus" size="is-small"/>
                            Nyt hold
                        </span>
                        <span class="add-squad-form__team-card-details">Gemmes under Hold til næste gang</span>
                    </button>
                    <button
                        type="button"
                        class="button is-link add-squad-form__team-card add-squad-form__team-card--manual"
                        :class="{'is-outlined': !manualEntry}"
                        dusk="squad-manual-entry-option"
                        :disabled="loading"
                        :aria-pressed="manualEntry ? 'true' : 'false'"
                        :title="manualEntry ? 'Klik igen for at fravælge' : null"
                        @click="onManualEntryClick">
                        <span class="add-squad-form__team-card-name">
                            <b-icon v-if="manualEntry" icon="check" size="is-small"/>
                            Uden hold
                        </span>
                        <span class="add-squad-form__team-card-details">Kun i denne holdrunde</span>
                    </button>
                </div>
                <p v-if="teamOptions.length > 0 && !manualEntry" class="help has-text-grey mt-0 mb-0">
                    {{ teamSelected
                        ? 'Navn og niveau hentes fra holdet. Klik igen for at fravælge.'
                        : 'Vælg et hold, så udfyldes navn og niveau automatisk.' }}
                </p>

                <div v-if="manualEntry" class="add-squad-form__manual-fields" dusk="squad-manual-fields">
                    <b-input
                        :model-value="selectedName"
                        :disabled="loading"
                        class="mb-2"
                        placeholder="Navn (fx Højbjerg 1)"
                        dusk="squad-name-input"
                        @update:modelValue="onNameInput">
                    </b-input>
                    <b-autocomplete
                        :model-value="selectedTierName"
                        :data="filteredTierOptions"
                        :loading="tiersLoading"
                        :disabled="loading"
                        placeholder="Niveau (fx 1. division)"
                        field="label"
                        clearable
                        keep-first
                        open-on-focus
                        dusk="squad-tier-input"
                        @update:modelValue="onTierInput"
                        @select="onTierSelect">
                    </b-autocomplete>
                    <p class="help has-text-grey mb-0">
                        Navn og niveau er valgfrie.
                    </p>
                </div>
            </div>

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
                                :model-value="selectedPlayingDate"
                                :disabled="loading"
                                :mobile-native="false"
                                icon="calendar-today"
                                locale="da-DK"
                                editable
                                position="is-top-left"
                                @update:modelValue="$emit('change-playing-date', $event)">
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
                                :dusk="'custom-category-count-' + field.key"
                                :model-value="customCategoryCounts[field.key]"
                                :min="0"
                                :max="20"
                                :disabled="loading"
                                controls-position="compact"
                                size="is-small"
                                @update:modelValue="emitCustomCategoryCount(field.key, $event)">
                            </b-numberinput>
                        </div>
                    </div>
                    <p class="help mt-2">
                        I alt {{ customTotalMatchCount }} kampe
                    </p>
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
                    Tilføj holdopstilling
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
        teamsFailed: {
            type: Boolean,
            default: false
        },
        teamOptions: {
            type: Array,
            default: () => []
        },
        selectedTeamId: {
            type: [String, Number],
            default: null
        },
        manualEntry: {
            type: Boolean,
            default: false
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
            return this.selectedTeamId !== null && this.selectedTeamId !== undefined;
        },
        noTeams() {
            return !this.teamsLoading && !this.teamsFailed && this.teamOptions.length === 0;
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
        isSelectedTeam(option) {
            return this.selectedTeamId !== null && String(option.id) === String(this.selectedTeamId);
        },
        onTeamClick(option) {
            this.$emit('select-team', this.isSelectedTeam(option) ? null : option.team);
        },
        onManualEntryClick() {
            this.$emit(this.manualEntry ? 'cancel-manual-entry' : 'start-manual-entry');
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

.add-squad-form__team-section {
    margin-bottom: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px dashed #dbdbdb;
}

.add-squad-form__team-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.4rem;
}

/* Two-line card: Bulma's fixed button height only fits one line. */
.button.add-squad-form__team-card {
    height: auto;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.1rem;
    padding: 0.4rem 0.75rem;
    white-space: normal;
    text-align: left;
    line-height: 1.25;
}

.add-squad-form__team-card-name {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.button.is-outlined.add-squad-form__team-card--manual {
    border-style: dashed;
}

.add-squad-form__team-card-details {
    font-size: 0.75rem;
    opacity: 0.8;
}

.add-squad-form__team-added {
    margin-left: 0.4rem;
    padding: 0 0.4rem;
    border-radius: 999px;
    background: rgba(72, 199, 142, 0.18);
    color: #257953;
    font-size: 0.7rem;
    font-weight: 600;
    line-height: 1.5;
}

.button.is-link:not(.is-outlined) .add-squad-form__team-added {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}

.add-squad-form__team-empty {
    color: #4a4a4a;
}

.button.is-light.add-squad-form__team-card {
    border-color: transparent;
}

.add-squad-form__manual-fields {
    max-width: 420px;
    margin-top: 0.5rem;
    padding: 0.6rem 0.75rem 0.75rem;
    border: 1px solid #dbdbdb;
    border-radius: 6px;
    background: #fff;
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
