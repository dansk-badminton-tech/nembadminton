<template>
    <div class="scenario-variant-a">
        <!-- Scenario Tabs Bar -->
        <div class="scenario-tab-bar mb-4">
            <div class="tabs is-boxed mb-0">
                <ul>
                    <li v-for="scenario in scenarios"
                        :key="scenario.id"
                        :class="{'is-active': currentScenarioId === scenario.id}">
                        <a @click="selectScenario(scenario.id)" class="scenario-tab-link">
                            <span class="mr-2">
                                <b-icon v-if="scenario.id === activeScenarioId"
                                        icon="check-circle"
                                        type="is-success"
                                        size="is-small">
                                </b-icon>
                                <b-icon v-else
                                        icon="file-document-edit-outline"
                                        type="is-grey"
                                        size="is-small">
                                </b-icon>
                            </span>
                            <span class="scenario-name">{{ scenario.name }}</span>
                            <b-tag v-if="scenario.id === activeScenarioId"
                                   type="is-success"
                                   size="is-small"
                                   class="ml-2">
                                Aktiv
                            </b-tag>
                            <b-tag v-else
                                   type="is-warning is-light"
                                   size="is-small"
                                   class="ml-2">
                                Kladde
                            </b-tag>
                        </a>
                    </li>
                    <li>
                        <a @click="promptNewScenario" class="has-text-grey">
                            <b-icon icon="plus" size="is-small" class="mr-1"></b-icon>
                            <span>Nyt scenarie</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Banner for Draft Scenario -->
        <div v-if="currentScenario.id !== activeScenarioId" class="notification is-warning is-light mb-4 p-4 border-warning">
            <div class="level is-mobile mb-0">
                <div class="level-left">
                    <div>
                        <div class="is-flex is-align-items-center">
                            <b-icon icon="alert-circle-outline" type="is-warning" class="mr-2"></b-icon>
                            <strong class="is-size-6">Udkast: {{ currentScenario.name }}</strong>
                            <b-tag type="is-warning" class="ml-2">Internt arbejdsdokument</b-tag>
                        </div>
                        <p class="is-size-7 has-text-grey-dark mt-1">
                            Spillere ser <strong>ikke</strong> ændringer i dette udkast.
                            Officiel holdrunde forbliver: <em>"{{ activeScenario.name }}"</em>.
                        </p>
                    </div>
                </div>
                <div class="level-right">
                    <div class="buttons">
                        <b-button type="is-success"
                                  icon-left="checkbox-marked-circle-outline"
                                  @click="confirmPromoteCurrent">
                            Gør dette scenarie aktivt
                        </b-button>
                        <b-dropdown position="is-bottom-left" aria-role="menu">
                            <template #trigger="{ active }">
                                <b-button type="is-light" icon-right="dots-vertical" aria-haspopup="true"></b-button>
                            </template>
                            <b-dropdown-item @click="promptRenameScenario">
                                <b-icon icon="pencil" size="is-small" class="mr-1"></b-icon> Omdøb
                            </b-dropdown-item>
                            <b-dropdown-item @click="duplicateScenario">
                                <b-icon icon="content-copy" size="is-small" class="mr-1"></b-icon> Dupliker
                            </b-dropdown-item>
                            <hr class="dropdown-divider">
                            <b-dropdown-item @click="deleteScenario" class="has-text-danger">
                                <b-icon icon="delete" size="is-small" class="mr-1"></b-icon> Slet udkast
                            </b-dropdown-item>
                        </b-dropdown>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Bar for Active Scenario -->
        <div v-else class="notification is-success is-light mb-4 p-3 border-success">
            <div class="level is-mobile mb-0">
                <div class="level-left">
                    <div class="is-flex is-align-items-center">
                        <b-icon icon="check-decagram" type="is-success" class="mr-2"></b-icon>
                        <span class="is-size-7">
                            <strong>Aktiv & Officiel opstilling.</strong>
                            Dette er holdopstillingen der eksporteres til BadmintonPlayer og deles med spillere.
                        </span>
                    </div>
                </div>
                <div class="level-right">
                    <b-button size="is-small" type="is-ghost" icon-left="content-copy" @click="duplicateScenario">
                        Kopier til nyt udkast
                    </b-button>
                </div>
            </div>
        </div>

        <!-- Simulated Action Bar with Read-only / Protection Warnings -->
        <div class="level mb-4">
            <div class="level-left">
                <div class="buttons">
                    <b-dropdown aria-role="list" :disabled="currentScenario.id !== activeScenarioId">
                        <template #trigger="{ active }">
                            <button class="button is-link" :disabled="currentScenario.id !== activeScenarioId">
                                <span>Del</span>
                                <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                            </button>
                        </template>
                        <b-dropdown-item aria-role="listitem">CSV</b-dropdown-item>
                        <b-dropdown-item aria-role="listitem">Link</b-dropdown-item>
                    </b-dropdown>

                    <b-tooltip v-if="currentScenario.id !== activeScenarioId"
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
                    Model: <strong>Faneblade med Aktiv-markør</strong>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ScenarioVariantA',
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
    computed: {
        currentScenario() {
            return this.scenarios.find(s => s.id === this.currentScenarioId) || this.scenarios[0]
        },
        activeScenario() {
            return this.scenarios.find(s => s.id === this.activeScenarioId) || this.scenarios[0]
        }
    },
    methods: {
        selectScenario(id) {
            this.$emit('select-scenario', id)
        },
        confirmPromoteCurrent() {
            this.$buefy.dialog.confirm({
                title: 'Gør scenarie aktivt?',
                message: `Vil du overskrive den officielle holdrunde med <strong>"${this.currentScenario.name}"</strong>?<br><br>
                          Dette opdaterer den officielle holdopstilling. Det hidtidige aktive hold arkiveres som et udkast.`,
                confirmText: 'Ja, gør aktiv',
                cancelText: 'Annuller',
                type: 'is-success',
                hasIcon: true,
                icon: 'checkbox-marked-circle-outline',
                onConfirm: () => {
                    this.$emit('promote-scenario', this.currentScenario.id)
                    this.$buefy.snackbar.open({
                        message: `"${this.currentScenario.name}" er nu den aktive holdopstilling!`,
                        type: 'is-success',
                        duration: 3500
                    })
                }
            })
        },
        promptNewScenario() {
            this.$buefy.dialog.prompt({
                message: 'Angiv navn på det nye scenarie:',
                inputAttrs: {
                    placeholder: 'f.eks. Scenarie C: Rasmus syg',
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
                title: 'Slet udkast?',
                message: `Er du sikker på, at du vil slette <strong>"${this.currentScenario.name}"</strong>?`,
                confirmText: 'Slet udkast',
                cancelText: 'Annuller',
                type: 'is-danger',
                hasIcon: true,
                onConfirm: () => {
                    this.$emit('delete-scenario', this.currentScenario.id)
                }
            })
        }
    }
}
</script>

<style scoped>
.scenario-tab-bar .tabs ul {
    border-bottom-color: #dbdbdb;
}

.scenario-tab-link {
    display: inline-flex;
    align-items: center;
    font-weight: 500;
}

.border-warning {
    border-left: 4px solid #ffdd57;
}

.border-success {
    border-left: 4px solid #48c78e;
}
</style>
