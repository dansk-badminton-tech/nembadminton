<template>
    <div dusk="scenario-prototype-page" class="scenario-prototype-container">
        <hero-bar :has-right-visible="true">
            Holdrunde nr. 3 (Senior)
            <template v-slot:subtitle>
                <div class="is-flex is-align-items-center has-text-grey">
                    <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                    <span>Dato: 24-10-2026</span>
                    <b-icon icon="format-list-numbered" size="is-small" class="ml-3 mr-1"></b-icon>
                    <span>Rangliste: Oktober 2026</span>
                    <span class="ml-4 tag is-dark is-rounded is-small">PROTOTYPE PREVIEW</span>
                </div>
            </template>
            <template v-slot:right>
                <b-button icon-left="refresh" size="is-small" @click="resetScenarios">Nulstil prototype</b-button>
            </template>
        </hero-bar>

        <section class="section is-main-section pt-4">
            <!-- Dynamic Variant Mount -->
            <component :is="activeVariantComponent"
                       :scenarios="scenarios"
                       :active-scenario-id="activeScenarioId"
                       :current-scenario-id="currentScenarioId"
                       @select-scenario="onSelectScenario"
                       @promote-scenario="onPromoteScenario"
                       @create-scenario="onCreateScenario"
                       @rename-scenario="onRenameScenario"
                       @duplicate-scenario="onDuplicateScenario"
                       @delete-scenario="onDeleteScenario" />

            <!-- Main Workspace: Players & Squads Columns (Real Density) -->
            <div class="columns mt-2">
                <!-- Left Column: Search & Available Players -->
                <div class="column is-5">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">
                                <b-icon icon="account-search" size="is-small" class="mr-2"></b-icon>
                                Tilgængelige spillere i klubben
                            </p>
                        </header>
                        <div class="card-content p-3">
                            <b-field>
                                <b-input placeholder="Søg spiller på navn eller nr..."
                                         icon="magnify"
                                         size="is-small"
                                         v-model="playerSearchQuery">
                                </b-input>
                            </b-field>
                            <div class="available-players-list">
                                <div v-for="player in filteredPlayers"
                                     :key="player.id"
                                     class="player-item is-flex is-justify-content-space-between is-align-items-center p-2 mb-1">
                                    <div>
                                        <strong>{{ player.name }}</strong>
                                        <span class="has-text-grey is-size-7 ml-2">({{ player.points }} pt)</span>
                                    </div>
                                    <div>
                                        <b-tag :type="player.gender === 'M' ? 'is-info is-light' : 'is-danger is-light'" size="is-small">
                                            {{ player.gender === 'M' ? 'Herre' : 'Dame' }}
                                        </b-tag>
                                        <b-tag v-if="isPlayerAssigned(player.id)" type="is-light" size="is-small" class="ml-1">
                                            I opstilling
                                        </b-tag>
                                        <b-button v-else size="is-small" type="is-ghost" icon-left="plus" @click="addPlayerToNextFreeSpot(player)">
                                            Indsæt
                                        </b-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Squads & Teams -->
                <div class="column is-7">
                    <!-- Validation Alert Banner -->
                    <div v-if="validationErrors.length > 0" class="notification is-danger is-light p-3 mb-4">
                        <div class="is-flex is-align-items-center mb-1">
                            <b-icon icon="alert" type="is-danger" class="mr-2"></b-icon>
                            <strong>Advarsler for denne opstilling:</strong>
                        </div>
                        <ul class="is-size-7 ml-4" style="list-style-type: disc;">
                            <li v-for="(err, idx) in validationErrors" :key="idx">{{ err }}</li>
                        </ul>
                    </div>
                    <div v-else class="notification is-success is-light p-3 mb-4">
                        <b-icon icon="check-circle" type="is-success" class="mr-2"></b-icon>
                        <span class="is-size-7"><strong>Alle hold er gyldige.</strong> Korrekt rangorden og fuldt besatte pladser.</span>
                    </div>

                    <!-- Teams List -->
                    <div v-for="team in currentTeams" :key="team.id" class="card mb-4 team-card">
                        <header class="card-header has-background-white-ter p-2">
                            <p class="card-header-title is-size-6 p-0 m-0">
                                {{ team.name }} ({{ team.league }})
                            </p>
                            <span class="tag is-small is-rounded" :class="team.spotsLeft === 0 ? 'is-success' : 'is-warning'">
                                {{ team.spotsLeft === 0 ? 'Fuldendt' : `Mangler ${team.spotsLeft} spiller` }}
                            </span>
                        </header>
                        <div class="card-content p-0">
                            <table class="table is-fullwidth is-striped is-narrow is-hoverable is-size-7 mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Kategori</th>
                                        <th style="width: 45%;">Spiller</th>
                                        <th style="width: 20%;">Point</th>
                                        <th style="width: 10%;">Handling</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="cat in team.categories" :key="cat.name">
                                        <td class="has-text-weight-bold">{{ cat.name }}</td>
                                        <td>
                                            <span v-if="cat.player">
                                                {{ cat.player.name }}
                                                <b-tag v-if="cat.diffTag" :type="cat.diffTagType" size="is-small" class="ml-2">
                                                    {{ cat.diffTag }}
                                                </b-tag>
                                            </span>
                                            <span v-else class="has-text-danger has-text-weight-bold">
                                                [Ledig plads - mangler spiller]
                                            </span>
                                        </td>
                                        <td>{{ cat.player ? cat.player.points + ' pt' : '-' }}</td>
                                        <td>
                                            <button v-if="cat.player"
                                                    class="button is-small is-ghost p-0 has-text-danger"
                                                    @click="removePlayerFromSpot(team.id, cat.name)"
                                                    title="Fjern spiller">
                                                <b-icon icon="close" size="is-small"></b-icon>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Floating Prototype Switcher Pill -->
        <prototype-switcher :current="currentVariant"
                            :variants="['A', 'B', 'C']"
                            :variant-names="variantLabels"
                            @change="onVariantChange" />
    </div>
</template>

<script>
import HeroBar from '@/components/HeroBar.vue'
import PrototypeSwitcher from './PrototypeSwitcher.vue'
import ScenarioVariantA from './ScenarioVariantA.vue'
import ScenarioVariantB from './ScenarioVariantB.vue'
import ScenarioVariantC from './ScenarioVariantC.vue'

const INITIAL_PLAYERS = [
    { id: 'p1', name: 'Mads Petersen', gender: 'M', points: 3450 },
    { id: 'p2', name: 'Rasmus Vinter', gender: 'M', points: 3200 },
    { id: 'p3', name: 'Anders Jensen', gender: 'M', points: 2950 },
    { id: 'p4', name: 'Kasper Holm', gender: 'M', points: 2800 },
    { id: 'p5', name: 'Sarah Lind', gender: 'F', points: 3100 },
    { id: 'p6', name: 'Mette Bach', gender: 'F', points: 2750 },
    { id: 'p7', name: 'Jens Overgaard', gender: 'M', points: 2500 },
    { id: 'p8', name: 'Frederik Friis', gender: 'M', points: 2400 },
]

export default {
    name: 'ScenarioPrototypePage',
    components: {
        HeroBar,
        PrototypeSwitcher,
        ScenarioVariantA,
        ScenarioVariantB,
        ScenarioVariantC
    },
    data() {
        return {
            playerSearchQuery: '',
            availablePlayers: [...INITIAL_PLAYERS],
            activeScenarioId: 'active-1',
            currentScenarioId: 'active-1',
            variantLabels: {
                A: 'Variant A: Faneblade & Aktiv-markør (Named Tabs)',
                B: 'Variant B: Sandkasse-tilstand (Staging Drawer)',
                C: 'Variant C: Dropdown & Diff-oversigt (Diff Inspector)'
            },
            scenarios: [
                {
                    id: 'active-1',
                    name: 'Aktiv opstilling (Officiel)',
                    teams: [
                        {
                            id: 't1',
                            name: 'Hold 1',
                            league: 'Danmarksserien',
                            categories: [
                                { name: '1. Herresingle', player: { id: 'p1', name: 'Mads Petersen', points: 3450 } },
                                { name: '2. Herresingle', player: { id: 'p2', name: 'Rasmus Vinter', points: 3200 } },
                                { name: '1. Damesingle', player: { id: 'p5', name: 'Sarah Lind', points: 3100 } },
                                { name: '1. Damedouble', player: { id: 'p6', name: 'Mette Bach', points: 2750 } },
                            ]
                        },
                        {
                            id: 't2',
                            name: 'Hold 2',
                            league: 'Sjællandsserien',
                            categories: [
                                { name: '1. Herresingle', player: { id: 'p3', name: 'Anders Jensen', points: 2950 } },
                                { name: '2. Herresingle', player: { id: 'p4', name: 'Kasper Holm', points: 2800 } },
                            ]
                        }
                    ]
                },
                {
                    id: 'scenario-a',
                    name: 'Scenarie A: Plan A (Rasmus med)',
                    teams: [
                        {
                            id: 't1',
                            name: 'Hold 1',
                            league: 'Danmarksserien',
                            categories: [
                                { name: '1. Herresingle', player: { id: 'p1', name: 'Mads Petersen', points: 3450 } },
                                { name: '2. Herresingle', player: { id: 'p2', name: 'Rasmus Vinter', points: 3200 } },
                                { name: '1. Damesingle', player: { id: 'p5', name: 'Sarah Lind', points: 3100 } },
                                { name: '1. Damedouble', player: { id: 'p6', name: 'Mette Bach', points: 2750 } },
                            ]
                        },
                        {
                            id: 't2',
                            name: 'Hold 2',
                            league: 'Sjællandsserien',
                            categories: [
                                { name: '1. Herresingle', player: { id: 'p3', name: 'Anders Jensen', points: 2950 } },
                                { name: '2. Herresingle', player: { id: 'p4', name: 'Kasper Holm', points: 2800 } },
                            ]
                        }
                    ]
                },
                {
                    id: 'scenario-b',
                    name: 'Scenarie B: Skader (Mads ude)',
                    teams: [
                        {
                            id: 't1',
                            name: 'Hold 1',
                            league: 'Danmarksserien',
                            categories: [
                                { name: '1. Herresingle', player: { id: 'p2', name: 'Rasmus Vinter', points: 3200 } },
                                { name: '2. Herresingle', player: { id: 'p3', name: 'Anders Jensen', points: 2950 }, diffTag: 'Oprykket fra H2', diffTagType: 'is-info' },
                                { name: '1. Damesingle', player: { id: 'p5', name: 'Sarah Lind', points: 3100 } },
                                { name: '1. Damedouble', player: { id: 'p6', name: 'Mette Bach', points: 2750 } },
                            ]
                        },
                        {
                            id: 't2',
                            name: 'Hold 2',
                            league: 'Sjællandsserien',
                            categories: [
                                { name: '1. Herresingle', player: { id: 'p4', name: 'Kasper Holm', points: 2800 } },
                                { name: '2. Herresingle', player: null, diffTag: 'Mangler spiller', diffTagType: 'is-danger' },
                            ]
                        }
                    ]
                }
            ]
        }
    },
    computed: {
        currentVariant() {
            const v = (this.$route.query.variant || 'A').toUpperCase()
            return ['A', 'B', 'C'].includes(v) ? v : 'A'
        },
        activeVariantComponent() {
            switch (this.currentVariant) {
                case 'B': return 'ScenarioVariantB'
                case 'C': return 'ScenarioVariantC'
                default: return 'ScenarioVariantA'
            }
        },
        currentScenario() {
            return this.scenarios.find(s => s.id === this.currentScenarioId) || this.scenarios[0]
        },
        currentTeams() {
            return (this.currentScenario.teams || []).map(t => {
                const emptySpots = t.categories.filter(c => !c.player).length
                return {
                    ...t,
                    spotsLeft: emptySpots
                }
            })
        },
        filteredPlayers() {
            if (!this.playerSearchQuery) return this.availablePlayers
            const q = this.playerSearchQuery.toLowerCase()
            return this.availablePlayers.filter(p => p.name.toLowerCase().includes(q))
        },
        validationErrors() {
            const errors = []
            this.currentTeams.forEach(t => {
                if (t.spotsLeft > 0) {
                    errors.push(`${t.name} mangler ${t.spotsLeft} spiller for at være fuldtalligt.`)
                }
            })
            return errors
        }
    },
    methods: {
        onVariantChange(v) {
            // Handled via PrototypeSwitcher router replace
        },
        onSelectScenario(id) {
            this.currentScenarioId = id
        },
        onPromoteScenario(id) {
            this.activeScenarioId = id
            this.currentScenarioId = id
        },
        onCreateScenario(name) {
            const newId = 'scenario-' + Date.now()
            // Clone the currently viewed scenario's teams
            const clonedTeams = JSON.parse(JSON.stringify(this.currentScenario.teams))
            this.scenarios.push({
                id: newId,
                name: name,
                teams: clonedTeams
            })
            this.currentScenarioId = newId
            this.$buefy.snackbar.open({
                message: `Oprettet nyt scenarie: "${name}"`,
                type: 'is-info',
                duration: 2500
            })
        },
        onRenameScenario({ id, name }) {
            const s = this.scenarios.find(sc => sc.id === id)
            if (s) {
                s.name = name
            }
        },
        onDuplicateScenario(id) {
            const target = this.scenarios.find(sc => sc.id === id)
            if (!target) return
            const newName = `${target.name} (Kopi)`
            this.onCreateScenario(newName)
        },
        onDeleteScenario(id) {
            const idx = this.scenarios.findIndex(s => s.id === id)
            if (idx > -1) {
                this.scenarios.splice(idx, 1)
                this.currentScenarioId = this.activeScenarioId
                this.$buefy.snackbar.open({
                    message: 'Udkast slettet',
                    type: 'is-grey',
                    duration: 2500
                })
            }
        },
        isPlayerAssigned(playerId) {
            return this.currentTeams.some(t => t.categories.some(c => c.player?.id === playerId))
        },
        addPlayerToNextFreeSpot(player) {
            for (const team of this.currentScenario.teams) {
                for (const cat of team.categories) {
                    if (!cat.player) {
                        cat.player = { id: player.id, name: player.name, points: player.points }
                        return
                    }
                }
            }
            this.$buefy.snackbar.open({
                message: 'Ingen ledige pladser på holdene.',
                type: 'is-warning',
                duration: 2000
            })
        },
        removePlayerFromSpot(teamId, catName) {
            const team = this.currentScenario.teams.find(t => t.id === teamId)
            if (team) {
                const cat = team.categories.find(c => c.name === catName)
                if (cat) {
                    cat.player = null
                }
            }
        },
        resetScenarios() {
            window.location.reload()
        }
    }
}
</script>

<style scoped>
.scenario-prototype-container {
    background-color: #f5f7fb;
    min-height: 100vh;
    padding-bottom: 90px;
}

.available-players-list {
    max-height: 400px;
    overflow-y: auto;
}

.player-item {
    border-radius: 4px;
    background-color: #ffffff;
    border: 1px solid #edf2f7;
}

.player-item:hover {
    background-color: #f7fafc;
}

.team-card {
    border: 1px solid #e2e8f0;
}
</style>
