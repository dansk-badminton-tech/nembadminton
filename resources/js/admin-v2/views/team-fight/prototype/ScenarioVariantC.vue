<template>
    <div class="scenario-variant-c">
        <!-- Compact Scenario Control Bar -->
        <div class="card mb-4">
            <div class="card-content p-3">
                <div class="level is-mobile mb-0">
                    <div class="level-left">
                        <div class="is-flex is-align-items-center" style="gap: 10px;">
                            <span class="is-size-7 has-text-weight-bold has-text-grey">OPSTILLING:</span>
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
                                    <div class="is-flex is-justify-content-space-between is-align-items-center" style="min-width: 240px;">
                                        <span>{{ scenario.name }}</span>
                                        <b-tag v-if="scenario.id === activeScenarioId" type="is-success" size="is-small">Aktiv</b-tag>
                                        <b-tag v-else type="is-warning is-light" size="is-small">Udkast</b-tag>
                                    </div>
                                </b-dropdown-item>
                                <hr class="dropdown-divider">
                                <b-dropdown-item aria-role="listitem" @click="promptNewScenario">
                                    <b-icon icon="plus" size="is-small" class="mr-1"></b-icon>
                                    Opret nyt scenarie...
                                </b-dropdown-item>
                            </b-dropdown>

                            <b-switch v-if="!isCurrentActive"
                                      v-model="showDiff"
                                      size="is-small"
                                      type="is-info">
                                <span class="is-size-7">Vis roster diff</span>
                            </b-switch>
                        </div>
                    </div>

                    <div class="level-right">
                        <div class="buttons mb-0">
                            <b-button v-if="!isCurrentActive"
                                      type="is-success"
                                      size="is-small"
                                      class="has-text-weight-bold"
                                      icon-left="lightning-bolt"
                                      @click="confirmPromote">
                                Overskriv & Aktiver scenarie
                            </b-button>
                            <b-button v-else
                                      size="is-small"
                                      type="is-ghost"
                                      icon-left="plus"
                                      @click="promptNewScenario">
                                Gem kopi som nyt scenarie
                            </b-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Roster Diff Inspector (when viewing a draft) -->
        <div v-if="!isCurrentActive && showDiff" class="diff-inspector-panel card mb-4">
            <header class="card-header has-background-light p-2">
                <p class="card-header-title is-size-7 p-0 m-0 has-text-grey-dark">
                    <b-icon icon="compare-horizontal" size="is-small" class="mr-2"></b-icon>
                    Forskelle mellem <strong>"{{ currentScenario.name }}"</strong> og Aktiv opstilling (3 ændringer fundet):
                </p>
                <button class="card-header-icon p-0 pr-2" @click="showDiff = false" title="Luk diff panel">
                    <b-icon icon="close" size="is-small"></b-icon>
                </button>
            </header>
            <div class="card-content p-3 is-size-7">
                <div class="columns is-multiline mb-0">
                    <div class="column is-4 py-1">
                        <div class="diff-item diff-added p-2">
                            <span class="tag is-success is-light is-small mr-2">+ RYK OP</span>
                            <strong>Hold 1 HS1:</strong> Anders Jensen (flyttet op fra Hold 2)
                        </div>
                    </div>
                    <div class="column is-4 py-1">
                        <div class="diff-item diff-removed p-2">
                            <span class="tag is-danger is-light is-small mr-2">- UDE</span>
                            <strong>Hold 1 HD2:</strong> Mads Petersen (fjernet / meldt skadet)
                        </div>
                    </div>
                    <div class="column is-4 py-1">
                        <div class="diff-item diff-warning p-2">
                            <span class="tag is-warning is-light is-small mr-2">⚠️ MANGLER</span>
                            <strong>Hold 2 HS2:</strong> 1 ledig plads (skal besættes)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action bar -->
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
                               label="Kun den aktive opstilling kan sendes til spillere. Gør scenariet aktivt først."
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
                    Model: <strong>Kompakt Dropdown & Diff-oversigt</strong>
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
            selectedId: this.currentScenarioId,
            showDiff: true
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
                title: 'Overskriv og aktiver scenarie?',
                message: `Dette vil overføre alle 3 ændringer fra <strong>"${this.currentScenario.name}"</strong> til den aktive holdrunde.<br><br>
                          Tidligere aktiv opstilling gemmes som sikkerhedskopi i scenarielisten.`,
                confirmText: 'Ja, overskriv og aktiver',
                cancelText: 'Annuller',
                type: 'is-success',
                hasIcon: true,
                icon: 'lightning-bolt',
                onConfirm: () => {
                    this.$emit('promote-scenario', this.currentScenario.id)
                    this.$buefy.snackbar.open({
                        message: `"${this.currentScenario.name}" er nu aktiveret som officiel holdrunde!`,
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
                    placeholder: 'f.eks. Scenarie: Plan B',
                    maxlength: 50
                },
                trapFocus: true,
                onConfirm: (value) => {
                    if (value && value.trim()) {
                        this.$emit('create-scenario', value.trim())
                    }
                }
            })
        }
    }
}
</script>

<style scoped>
.diff-inspector-panel {
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.diff-item {
    border-radius: 4px;
    background-color: #f8fafc;
    border: 1px solid #edf2f7;
}

.diff-added {
    border-left: 3px solid #48c78e;
}

.diff-removed {
    border-left: 3px solid #f14668;
}

.diff-warning {
    border-left: 3px solid #ffe08a;
}
</style>
