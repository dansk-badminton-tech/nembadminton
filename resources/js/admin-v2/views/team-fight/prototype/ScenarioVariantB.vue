<template>
    <div class="scenario-variant-b">
        <!-- MODE 1: Standard Active Mode -->
        <div v-if="!isSandboxOpen" class="active-mode-header mb-4">
            <div class="level">
                <div class="level-left">
                    <div class="is-flex is-align-items-center">
                        <b-tag type="is-success" size="is-medium" class="mr-3">
                            <b-icon icon="check-circle" size="is-small" class="mr-1"></b-icon>
                            Officiel holdrunde
                        </b-tag>
                        <span class="has-text-grey is-size-7">
                            Du redigerer den aktive opstilling. Alle ændringer gemmes direkte.
                        </span>
                    </div>
                </div>
                <div class="level-right">
                    <b-button type="is-warning"
                              class="has-text-weight-bold"
                              icon-left="flask-outline"
                              @click="enterSandbox">
                        Åbn Sandkasse / Kladdeværktøj ({{ draftCount }} kladder)
                    </b-button>
                </div>
            </div>

            <!-- Standard Actions -->
            <div class="level mb-2">
                <div class="level-left">
                    <div class="buttons">
                        <b-dropdown aria-role="list">
                            <template #trigger="{ active }">
                                <button class="button is-link">
                                    <span>Del</span>
                                    <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                                </button>
                            </template>
                            <b-dropdown-item aria-role="listitem">CSV</b-dropdown-item>
                            <b-dropdown-item aria-role="listitem">Link</b-dropdown-item>
                        </b-dropdown>
                        <b-button icon-left="email-fast" type="is-info">
                            Send hold til spillere
                        </b-button>
                    </div>
                </div>
                <div class="level-right">
                    <span class="tag is-light is-size-7">
                        Model: <strong>Eksplicit Sandkasse-tilstand</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- MODE 2: Sandbox / Staging Mode Active -->
        <div v-else class="sandbox-mode-wrapper mb-4">
            <!-- Full-bleed Sandbox Banner -->
            <div class="sandbox-top-bar p-4 mb-3">
                <div class="level is-mobile mb-2">
                    <div class="level-left">
                        <div class="is-flex is-align-items-center">
                            <span class="sandbox-badge mr-3">
                                <b-icon icon="flask" size="is-small"></b-icon>
                                SANDKASSE
                            </span>
                            <b-dropdown v-model="selectedDraftId" aria-role="list">
                                <template #trigger="{ active }">
                                    <button class="button is-dark is-small">
                                        <span>Kladde: <strong>{{ currentScenario.name }}</strong></span>
                                        <b-icon :icon="active ? 'menu-up' : 'menu-down'"></b-icon>
                                    </button>
                                </template>
                                <b-dropdown-item v-for="draft in drafts"
                                                 :key="draft.id"
                                                 :value="draft.id"
                                                 aria-role="listitem"
                                                 @click="switchDraft(draft.id)">
                                    <b-icon icon="file-document-outline" size="is-small" class="mr-1"></b-icon>
                                    {{ draft.name }}
                                </b-dropdown-item>
                                <hr class="dropdown-divider">
                                <b-dropdown-item aria-role="listitem" @click="promptNewDraft">
                                    <b-icon icon="plus" size="is-small" class="mr-1"></b-icon>
                                    Opret ny kladde...
                                </b-dropdown-item>
                            </b-dropdown>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="buttons">
                            <b-button type="is-light"
                                      size="is-small"
                                      icon-left="close"
                                      @click="exitSandbox">
                                Forlad sandkasse
                            </b-button>
                            <b-button type="is-success"
                                      size="is-small"
                                      class="has-text-weight-bold"
                                      icon-left="upload"
                                      @click="confirmApplyToLive">
                                Anvend på holdrunde (Overskriv)
                            </b-button>
                        </div>
                    </div>
                </div>

                <div class="sandbox-subtext is-size-7">
                    <span>
                        ⚠️ Du arbejder i et isoleret testmiljø. Ændringer i denne kladde påvirker <strong>ikke</strong> den rigtige holdrunde, før du klikker <em>"Anvend på holdrunde"</em>.
                    </span>
                </div>
            </div>

            <!-- Sandbox Restricted Actions Bar -->
            <div class="level mb-2">
                <div class="level-left">
                    <div class="buttons">
                        <b-tooltip label="Deling og eksport er låst i sandkasse-tilstand" position="is-top">
                            <button class="button is-light" disabled>
                                <b-icon icon="lock" size="is-small" class="mr-1"></b-icon> Del (Låst)
                            </button>
                        </b-tooltip>
                        <b-tooltip label="Notifikationer kan kun sendes fra den officielle holdrunde" position="is-top">
                            <button class="button is-light" disabled>
                                <b-icon icon="email-off-outline" size="is-small" class="mr-1"></b-icon> Send til spillere (Låst)
                            </button>
                        </b-tooltip>
                    </div>
                </div>
                <div class="level-right">
                    <b-tag type="is-warning is-light">
                        Sandkasse aktiv: {{ currentScenario.name }}
                    </b-tag>
                </div>
            </div>

            <!-- Sticky Bottom Apply Drawer -->
            <div class="sandbox-bottom-dock">
                <div class="dock-container">
                    <div class="dock-left">
                        <span class="has-text-weight-bold mr-2">🧪 Sandkasse: {{ currentScenario.name }}</span>
                        <span class="tag is-warning is-light is-rounded">3 ændringer mod officiel</span>
                    </div>
                    <div class="dock-actions">
                        <b-button type="is-light" size="is-small" @click="exitSandbox">
                            Luk uden at gemme
                        </b-button>
                        <b-button type="is-success" size="is-small" class="has-text-weight-bold ml-2" @click="confirmApplyToLive">
                            Anvend på holdrunde
                        </b-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ScenarioVariantB',
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
            isSandboxOpen: this.currentScenarioId !== this.activeScenarioId,
            selectedDraftId: this.currentScenarioId !== this.activeScenarioId ? this.currentScenarioId : (this.scenarios.find(s => s.id !== this.activeScenarioId)?.id || '')
        }
    },
    watch: {
        currentScenarioId(newVal) {
            this.isSandboxOpen = newVal !== this.activeScenarioId
            if (newVal !== this.activeScenarioId) {
                this.selectedDraftId = newVal
            }
        }
    },
    computed: {
        drafts() {
            return this.scenarios.filter(s => s.id !== this.activeScenarioId)
        },
        draftCount() {
            return this.drafts.length
        },
        currentScenario() {
            return this.scenarios.find(s => s.id === (this.isSandboxOpen ? this.selectedDraftId : this.activeScenarioId)) || this.scenarios[0]
        }
    },
    methods: {
        enterSandbox() {
            const firstDraft = this.drafts[0]
            if (firstDraft) {
                this.selectedDraftId = firstDraft.id
                this.isSandboxOpen = true
                this.$emit('select-scenario', firstDraft.id)
            } else {
                this.promptNewDraft()
            }
        },
        exitSandbox() {
            this.isSandboxOpen = false
            this.$emit('select-scenario', this.activeScenarioId)
        },
        switchDraft(id) {
            this.selectedDraftId = id
            this.$emit('select-scenario', id)
        },
        confirmApplyToLive() {
            this.$buefy.dialog.confirm({
                title: 'Anvend kladde på officiel holdrunde?',
                message: `Dette vil overskrive den officielle holdrunde med opstillingen fra <strong>"${this.currentScenario.name}"</strong>.<br><br>
                          <strong>Ændringer der anvendes:</strong><br>
                          • Hold 1: 1 spiller udskiftet<br>
                          • Hold 2: 1 spiller oprykket<br><br>
                          Er du sikker på, at du vil overskrive?`,
                confirmText: 'Ja, overskriv officiel holdrunde',
                cancelText: 'Annuller',
                type: 'is-success',
                hasIcon: true,
                icon: 'upload',
                onConfirm: () => {
                    this.$emit('promote-scenario', this.currentScenario.id)
                    this.isSandboxOpen = false
                    this.$buefy.snackbar.open({
                        message: `Opstillingen fra "${this.currentScenario.name}" er nu anvendt på den officielle holdrunde!`,
                        type: 'is-success',
                        duration: 3500
                    })
                }
            })
        },
        promptNewDraft() {
            this.$buefy.dialog.prompt({
                message: 'Angiv navn på ny kladde / sandkasse:',
                inputAttrs: {
                    placeholder: 'f.eks. Kladde: Uden skadede spillere',
                    maxlength: 50
                },
                trapFocus: true,
                onConfirm: (value) => {
                    if (value && value.trim()) {
                        this.$emit('create-scenario', value.trim())
                        this.isSandboxOpen = true
                    }
                }
            })
        }
    }
}
</script>

<style scoped>
.sandbox-top-bar {
    background: linear-gradient(135deg, #2b303c 0%, #1a1e26 100%);
    border-radius: 6px;
    color: #ffffff;
    border-left: 5px solid #ffdd57;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.sandbox-badge {
    background-color: #ffdd57;
    color: #363636;
    font-size: 11px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    letter-spacing: 0.05em;
}

.sandbox-subtext {
    color: #d1d5db;
}

.sandbox-bottom-dock {
    position: fixed;
    bottom: 80px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 999;
}

.dock-container {
    background: #232731;
    color: #ffffff;
    border-radius: 8px;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 221, 87, 0.4);
}

.dock-left {
    display: flex;
    align-items: center;
    font-size: 13px;
}
</style>
