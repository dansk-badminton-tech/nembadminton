<template>
    <div class="scenario-variant-c">
        <!-- Compact Scenario Control Bar -->
        <div class="card mb-3">
            <div class="card-content p-3">
                <div class="level is-mobile mb-0">
                    <div class="level-left">
                        <div class="is-flex is-align-items-center" style="gap: 12px; flex-wrap: wrap;">
                            <span class="is-size-7 has-text-weight-bold has-text-grey">SCENARIE:</span>

                            <!-- Scenario Selector Dropdown -->
                            <b-dropdown v-model="selectedId" aria-role="list" @change="onSelectChange">
                                <template #trigger="{ active }">
                                    <button class="button is-small" :class="isCurrentActive ? 'is-success is-light' : 'is-warning is-light'">
                                        <b-icon :icon="isCurrentActive ? 'check-circle' : 'file-document-edit'" size="is-small" class="mr-1"></b-icon>
                                        <span><strong>{{ currentScenario.name }}</strong></span>
                                        <b-icon :icon="active ? 'menu-up' : 'menu-down'"></b-icon>
                                    </button>
                                </template>
                                <b-dropdown-item v-for="scenario in scenarios"
                                                 :key="scenario.id"
                                                 :value="scenario.id"
                                                 aria-role="listitem">
                                    <div class="is-flex is-justify-content-space-between is-align-items-center" style="min-width: 250px;">
                                        <span>{{ scenario.name }}</span>
                                        <b-tag v-if="scenario.id === activeScenarioId" type="is-success" size="is-small">Officiel</b-tag>
                                        <b-tag v-else type="is-warning is-light" size="is-small">Udkast</b-tag>
                                    </div>
                                </b-dropdown-item>
                                <hr class="dropdown-divider">
                                <b-dropdown-item aria-role="listitem" @click="promptNewScenario">
                                    <b-icon icon="plus" size="is-small" class="mr-1"></b-icon>
                                    Opret nyt scenarie...
                                </b-dropdown-item>
                            </b-dropdown>

                            <!-- Status context label -->
                            <span v-if="isCurrentActive" class="is-size-7 has-text-success has-text-weight-semibold">
                                <b-icon icon="earth" size="is-small" class="mr-1" style="vertical-align: middle;"></b-icon>
                                Dette er den officielle opstilling, som spillere ser
                            </span>
                            <span v-else class="is-size-7 has-text-grey">
                                <b-icon icon="eye-off" size="is-small" class="mr-1" style="vertical-align: middle;"></b-icon>
                                Internt udkast — spillere ser fortsat <em>"{{ activeScenario.name }}"</em>
                            </span>
                        </div>
                    </div>

                    <div class="level-right">
                        <div class="buttons mb-0">
                            <!-- Draft Actions -->
                            <template v-if="!isCurrentActive">
                                <b-button type="is-success"
                                          size="is-small"
                                          class="has-text-weight-bold"
                                          icon-left="checkbox-marked-circle-outline"
                                          @click="confirmPromote">
                                    Gør til officiel opstilling
                                </b-button>

                                <b-dropdown position="is-bottom-left" aria-role="list">
                                    <template #trigger>
                                        <button class="button is-small is-light" title="Flere handlinger">
                                            <b-icon icon="dots-vertical" size="is-small"></b-icon>
                                        </button>
                                    </template>
                                    <b-dropdown-item aria-role="listitem" @click="promptRenameScenario">
                                        <b-icon icon="pencil" size="is-small" class="mr-1"></b-icon> Omdøb scenarie
                                    </b-dropdown-item>
                                    <b-dropdown-item aria-role="listitem" @click="duplicateScenario">
                                        <b-icon icon="content-copy" size="is-small" class="mr-1"></b-icon> Dupliker scenarie
                                    </b-dropdown-item>
                                    <hr class="dropdown-divider">
                                    <b-dropdown-item aria-role="listitem" @click="deleteScenario" class="has-text-danger">
                                        <b-icon icon="delete" size="is-small" class="mr-1"></b-icon> Slet udkast
                                    </b-dropdown-item>
                                </b-dropdown>
                            </template>

                            <!-- Official Actions -->
                            <template v-else>
                                <b-button size="is-small"
                                          type="is-info is-light"
                                          icon-left="plus"
                                          @click="promptNewScenario">
                                    Nyt scenarie
                                </b-button>
                                <b-button size="is-small"
                                          type="is-ghost"
                                          icon-left="pencil"
                                          @click="promptRenameScenario"
                                          title="Omdøb officiel opstilling">
                                </b-button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action bar (Share / Notifications) -->
        <div class="level mb-4">
            <div class="level-left">
                <div class="buttons">
                    <b-dropdown aria-role="list" :disabled="!isCurrentActive">
                        <template #trigger="{ active }">
                            <button class="button is-link" :disabled="!isCurrentActive">
                                <span>Del</span>
                                <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                            </button>
                        </template>
                        <b-dropdown-item aria-role="listitem">CSV</b-dropdown-item>
                        <b-dropdown-item aria-role="listitem">Link</b-dropdown-item>
                    </b-dropdown>

                    <b-tooltip v-if="!isCurrentActive"
                               label="Kun den officielle opstilling kan sendes til spillere. Gør scenariet officielt først."
                               position="is-top">
                        <b-button icon-left="email-fast" disabled>
                            Send hold til spillere (Låst i udkast)
                        </b-button>
                    </b-tooltip>
                    <b-button v-else icon-left="email-fast" type="is-info">
                        Send hold til spillere
                    </b-button>
                </div>
            </div>
            <div class="level-right">
                <span class="tag is-light is-size-7">
                    Model: <strong>Kompakt Dropdown-vælger</strong>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ScenarioVariantC',
    props: {
        scenarios: {
            type: Array,
            required: true
        },
        activeScenarioId: {
            type: String,
            required: true
        },
        currentScenarioId: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            selectedId: this.currentScenarioId
        }
    },
    watch: {
        currentScenarioId(newVal) {
            this.selectedId = newVal
        }
    },
    computed: {
        currentScenario() {
            return this.scenarios.find(s => s.id === this.selectedId) || this.scenarios[0]
        },
        activeScenario() {
            return this.scenarios.find(s => s.id === this.activeScenarioId) || this.scenarios[0]
        },
        isCurrentActive() {
            return this.selectedId === this.activeScenarioId
        }
    },
    methods: {
        onSelectChange(id) {
            this.$emit('select-scenario', id)
        },
        confirmPromote() {
            this.$buefy.dialog.confirm({
                title: 'Gør til officiel holdopstilling?',
                message: `Vil du udgive <strong>"${this.currentScenario.name}"</strong> som den officielle opstilling for denne holdrunde?<br><br>
                          Dette er opstillingen spillere ser, og som kan sendes ud og eksporteres. Dit tidligere scenarie forbliver gemt i listen.`,
                confirmText: 'Ja, gør officiel',
                cancelText: 'Annuller',
                type: 'is-success',
                hasIcon: true,
                icon: 'checkbox-marked-circle-outline',
                onConfirm: () => {
                    this.$emit('promote-scenario', this.currentScenario.id)
                    this.$buefy.snackbar.open({
                        message: `"${this.currentScenario.name}" er nu den officielle holdopstilling!`,
                        type: 'is-success',
                        duration: 3500
                    })
                }
            })
        },
        promptNewScenario() {
            this.$buefy.dialog.prompt({
                message: 'Angiv navn på nyt scenarie:',
                inputAttrs: {
                    placeholder: 'f.eks. Plan B (uden Mads)',
                    maxlength: 50
                },
                trapFocus: true,
                onConfirm: (value) => {
                    if (value && value.trim()) {
                        this.$emit('create-scenario', value.trim())
                    }
                }
            })
        },
        promptRenameScenario() {
            this.$buefy.dialog.prompt({
                message: 'Omdøb scenarie:',
                inputAttrs: {
                    value: this.currentScenario.name,
                    maxlength: 50
                },
                trapFocus: true,
                onConfirm: (value) => {
                    if (value && value.trim()) {
                        this.$emit('rename-scenario', { id: this.currentScenario.id, name: value.trim() })
                    }
                }
            })
        },
        duplicateScenario() {
            this.$emit('duplicate-scenario', this.currentScenario.id)
        },
        deleteScenario() {
            this.$buefy.dialog.confirm({
                title: 'Slet scenarie?',
                message: `Er du sikker på, at du vil slette udkastet <strong>"${this.currentScenario.name}"</strong>?`,
                confirmText: 'Slet udkast',
                cancelText: 'Annuller',
                type: 'is-danger',
                hasIcon: true,
                icon: 'delete',
                onConfirm: () => {
                    this.$emit('delete-scenario', this.currentScenario.id)
                }
            })
        }
    }
}
</script>

<style scoped>
.scenario-variant-c .card {
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
</style>
