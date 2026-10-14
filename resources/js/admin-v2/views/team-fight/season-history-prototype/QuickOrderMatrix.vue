<!-- PROTOTYPE (issue #254) quick C: one table of the club-wide order per gender across recent rounds, above the squads. Throwaway. -->
<template>
    <div class="matrix column is-full" dusk="season-order-matrix">
        <div class="is-flex is-flex-wrap-wrap is-align-items-center" style="gap: 0.5rem">
            <b-button icon-left="table" :type="open ? 'is-info' : ''" @click="open = !open" dusk="season-history-trigger">
                Rækkefølge over sæsonen
            </b-button>
            <b-field v-if="open" class="mb-0">
                <b-radio-button v-model="gender" native-value="MEN" size="is-small">Herrer</b-radio-button>
                <b-radio-button v-model="gender" native-value="WOMEN" size="is-small">Damer</b-radio-button>
            </b-field>
        </div>
        <div v-if="open" class="mt-2">
            <p class="is-size-7 has-text-grey mb-2">
                Nr. i klubbens samlede rækkefølge pr. holdrunde (opstillinger, ikke resultater).
                Orange: nogen rykkede forbi. Grøn: rykkede selv forbi.
            </p>
            <p v-if="!season.hasEarlier" class="has-text-grey">{{ EMPTY_TEXT }}</p>
            <div v-else class="table-container">
                <table class="table is-narrow is-fullwidth is-hoverable">
                    <thead>
                    <tr>
                        <th>Spiller</th>
                        <th v-for="round in rounds" :key="round.key" class="has-text-centered" :title="round.name">
                            {{ round.isCurrent ? 'Nu' : 'R' + round.name.split('.')[0] }}<br>
                            <span class="is-size-7 has-text-grey has-text-weight-normal">{{ formatDate(round.gameDate) }}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="row in rows" :key="row.player.refId" :class="{youth: row.player.youth}">
                        <td class="name">
                            {{ row.player.name }}
                            <b-tag v-if="row.player.youth" size="is-small" type="is-success is-light">
                                {{ row.player.vintage }}{{ row.player.simulatedYouth ? ' (simuleret)' : '' }}
                            </b-tag>
                        </td>
                        <td v-for="h in row.cells" :key="h.round.key" class="has-text-centered cell"
                            :class="{'passed-by': h.passedBy.length, passed: h.passed.length && !h.passedBy.length}">
                            <b-tooltip type="is-dark" multilined :triggers="['hover', 'click']">
                                <template #content>
                                    <span v-if="!h.placed">{{ NOT_PLACED_TEXT }}</span>
                                    <span v-for="p in h.placements" :key="p.label">{{ formatPlacement(p) }}<br></span>
                                    <span v-if="h.passedBy.length">Rykket forbi af: {{ h.passedBy.join(', ') }}<br></span>
                                    <span v-if="h.passed.length">Rykkede forbi: {{ h.passed.join(', ') }}</span>
                                </template>
                                <span v-if="!h.placed" class="has-text-grey-light">–</span>
                                <span v-else class="cell-content">
                                    <strong>{{ h.rank }}</strong>
                                    <span class="slot">{{ h.placements[0]?.label }}</span>
                                </span>
                            </b-tooltip>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script>
import {EMPTY_TEXT, NOT_PLACED_TEXT, formatDate, formatPlacement} from './season.js'

export default {
    name: 'QuickOrderMatrix',
    props: {season: Object},
    data: () => ({open: true, gender: 'MEN', EMPTY_TEXT, NOT_PLACED_TEXT}),
    computed: {
        rounds() {
            return this.season.rounds.slice(-4)
        },
        rows() {
            return this.season.order[this.gender].map(refId => ({
                player: this.season.players.get(refId),
                cells: this.season.history(refId).slice(-4),
            }))
        }
    },
    methods: {formatDate, formatPlacement}
}
</script>
<style scoped>
.matrix .name { white-space: nowrap; }
.matrix .table-container { overflow-x: auto; max-width: 100%; }
.matrix .cell-content { display: inline-flex; flex-direction: column; line-height: 1.1; }
.matrix .slot { font-size: 0.65rem; color: #7a7a7a; white-space: nowrap; }
.matrix tr.youth td.name { font-weight: 600; }
.matrix tr.youth { box-shadow: inset 3px 0 0 #48c78e; }
.matrix td.cell.passed-by { background: #fff3e0; }
.matrix td.cell.passed { background: #effaf5; }
</style>
