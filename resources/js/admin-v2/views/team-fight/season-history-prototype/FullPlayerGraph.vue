<!-- PROTOTYPE (issue #254) full E: modal with a rank chart per category, across all teams, for the player and those around them. Throwaway. -->
<template>
    <b-modal :model-value="true" has-modal-card :can-cancel="['escape', 'x', 'outside']" full-screen-mobile @close="$emit('close')">
        <div class="modal-card graph-card" dusk="full-player-graph">
            <header class="modal-card-head">
                <div class="modal-card-title">
                    {{ info.name }}
                    <b-tag v-if="info.youth" type="is-success is-light">{{ info.vintage }}{{ info.simulatedYouth ? ' (simuleret)' : '' }}</b-tag>
                    <p class="is-size-7 has-text-grey mt-1">
                        Rækkefølgen i {{ CATEGORY_LABEL[category] }} på tværs af klubbens hold (1. hold øverst).
                        <span v-if="isDouble">I double deler makkerne plads.</span>
                        Opstillinger, ikke resultater.
                    </p>
                </div>
                <button class="delete" aria-label="Luk" @click="$emit('close')"></button>
            </header>
            <section class="modal-card-body">
                <b-field class="mb-3">
                    <b-radio-button v-for="c in categories" :key="c" v-model="category" :native-value="c" size="is-small"
                                    :dusk="'graph-category-' + c">
                        {{ c }}<span v-if="currentCategories.includes(c)" class="ml-1 has-text-grey">· nu</span>
                    </b-radio-button>
                </b-field>
                <p v-if="!season.hasEarlier" class="has-text-grey">{{ EMPTY_TEXT }}</p>
                <div v-else class="chart-wrap">
                    <svg :viewBox="`0 0 ${W} ${H}`" class="chart" role="img">
                        <text v-for="(round, r) in season.rounds" :key="round.key" :x="x(r)" y="14" text-anchor="middle" class="axis">
                            {{ round.isCurrent ? 'Nu' : 'R' + (r + 1) }}
                        </text>
                        <line v-for="(round, r) in season.rounds" :key="'g' + round.key" :x1="x(r)" :x2="x(r)" :y1="24" :y2="H - 8" class="grid"/>
                        <text v-for="row in rankRange" :key="'y' + row.rank" :x="PAD_L - 6" :y="y(row.rank) + 4" text-anchor="end" class="axis">
                            {{ row.label }}
                        </text>
                        <g v-for="line in lines" :key="line.refId" :class="line.kind">
                            <title>{{ line.name }}</title>
                            <polyline v-for="(segment, i) in line.segments" :key="i" :points="segment" fill="none"/>
                            <circle v-for="pt in line.points" :key="pt.r" :cx="x(pt.r)" :cy="y(pt.rank)" :r="line.kind === 'subject' ? 5 : 3.5">
                                <title>{{ line.name }}: nr. {{ pt.rank }} ({{ season.rounds[pt.r].name }})</title>
                            </circle>
                        </g>
                        <text v-for="label in labels" :key="label.key" :x="x(label.r) + 10" :y="y(label.rank) + 4"
                              class="label" :class="{subject: label.subject, youth: label.youth}">{{ label.text }}</text>
                    </svg>
                </div>
                <p class="is-size-7 mt-2">
                    <span class="key subject"></span> {{ info.name }}
                    <span class="key youth ml-3"></span> Ungdomsspiller
                    <span class="key other ml-3"></span> Spillere omkring (højst 2 pladser fra)
                </p>
                <table class="table is-narrow is-fullwidth mt-4">
                    <thead>
                    <tr><th>Runde</th><th>Nr. i {{ category }}</th><th>Opstillet</th></tr>
                    </thead>
                    <tbody>
                    <tr v-for="row in rows" :key="row.round.key">
                        <td class="nowrap">{{ row.round.name }} <span class="is-size-7 has-text-grey">· {{ formatDate(row.round.gameDate) }}</span></td>
                        <td class="nowrap">{{ row.rank ?? '–' }}</td>
                        <td class="is-size-7">
                            <em v-if="!row.placements.length" class="has-text-grey">{{ NOT_PLACED_TEXT }}</em>
                            <span v-for="p in row.placements" :key="p.category" :class="{'has-text-weight-semibold': p.category === category}">
                                {{ formatPlacement(p) }}<span v-if="p.partnerName" class="has-text-grey has-text-weight-normal"> m. {{ p.partnerName }}</span><br>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </b-modal>
</template>
<script>
import {EMPTY_TEXT, NOT_PLACED_TEXT, CATEGORY_LABEL, formatDate, formatPlacement} from './season.js'

const PAD_L = 110
const PAD_T = 36
const LABEL_W = 320
const ROW_H = 28
const W = 960

export default {
    name: 'FullPlayerGraph',
    props: {season: Object, player: Object},
    emits: ['close'],
    data() {
        const model = this.season.byCategory
        const now = model.currentCategories(this.player.refId)
        const all = model.categoriesFor(this.player.refId)
        return {
            EMPTY_TEXT, NOT_PLACED_TEXT, CATEGORY_LABEL, PAD_L, W,
            category: now.find(c => c === 'HS' || c === 'DS') || now[0] || all[0],
        }
    },
    computed: {
        model() {
            return this.season.byCategory
        },
        info() {
            return this.season.players.get(this.player.refId)
        },
        categories() {
            return this.model.categoriesFor(this.player.refId)
        },
        currentCategories() {
            return this.model.currentCategories(this.player.refId)
        },
        isDouble() {
            return ['HD', 'DD', 'MD'].includes(this.category)
        },
        R() {
            return this.season.rounds.length
        },
        rows() {
            return this.season.rounds.map((round, r) => ({
                round,
                rank: this.model.rank(this.player.refId, this.category, r),
                placements: this.model.placementsAt(this.player.refId, r),
            })).reverse()
        },
        included() {
            const me = this.info.refId
            return this.model.playersIn(this.category).filter(other => {
                if (other === me) return true
                for (let r = 0; r < this.R; r++) {
                    const a = this.model.rank(me, this.category, r)
                    const b = this.model.rank(other, this.category, r)
                    if (a !== null && b !== null && Math.abs(a - b) <= 2) return true
                }
                return false
            })
        },
        rankRange() {
            const ranks = []
            this.included.forEach(refId => this.season.rounds.forEach((_, r) => {
                const rank = this.model.rank(refId, this.category, r)
                if (rank !== null) ranks.push(rank)
            }))
            if (!ranks.length) return []
            const min = Math.min(...ranks)
            const max = Math.max(...ranks)
            // Each place is a fixed slot in the category, e.g. place 5 = "Hold 2 · 1. HS".
            return Array.from({length: max - min + 1}, (_, i) => {
                const rank = min + i
                return {rank, label: this.model.slotLabel(this.category, rank)}
            })
        },
        H() {
            return PAD_T + Math.max(this.rankRange.length, 1) * ROW_H
        },
        lines() {
            const me = this.info.refId
            const lines = this.included.map(refId => {
                const p = this.season.players.get(refId)
                const points = []
                const segments = []
                let current = []
                for (let r = 0; r < this.R; r++) {
                    const rank = this.model.rank(refId, this.category, r)
                    if (rank === null) {
                        if (current.length) segments.push(current)
                        current = []
                        continue
                    }
                    points.push({r, rank})
                    current.push(`${this.x(r)},${this.y(rank)}`)
                }
                if (current.length) segments.push(current)
                return {
                    refId,
                    name: p.name,
                    youth: p.youth,
                    kind: refId === me ? 'subject' : (p.youth ? 'youth' : 'other'),
                    points,
                    segments: segments.map(s => s.join(' ')),
                    end: points[points.length - 1] || null,
                }
            })
            return lines.sort((a, b) => (a.kind === 'subject') - (b.kind === 'subject'))
        },
        // Partners end on the same point in doubles: one label per point, names joined.
        labels() {
            const groups = new Map()
            for (const line of this.lines) {
                if (!line.end) continue
                const key = `${line.end.r}-${line.end.rank}`
                if (!groups.has(key)) groups.set(key, {key, r: line.end.r, rank: line.end.rank, names: [], subject: false, youth: false})
                const g = groups.get(key)
                g.names.push(line.name)
                g.subject = g.subject || line.kind === 'subject'
                g.youth = g.youth || line.youth
            }
            return [...groups.values()].map(g => ({...g, text: g.names.join(' / ')}))
        }
    },
    methods: {
        formatDate,
        formatPlacement(p) {
            return `${p.category} nr. ${p.rank} · ` + formatPlacement(p)
        },
        x(r) {
            return PAD_L + 10 + r * ((W - PAD_L - LABEL_W - 10) / Math.max(this.R - 1, 1))
        },
        y(rank) {
            const min = this.rankRange[0]?.rank ?? 1
            return PAD_T + (rank - min) * ROW_H
        }
    }
}
</script>
<style scoped>
.graph-card { width: min(1040px, 100vw); }
.chart-wrap { overflow-x: auto; }
.chart { width: 100%; min-width: 600px; height: auto; }
.chart .axis { font-size: 11px; fill: #7a7a7a; }
.chart .grid { stroke: #f0f0f0; }
.chart .label { font-size: 12px; fill: #4a4a4a; }
.chart .label.youth { fill: #257953; }
.chart .label.subject { font-weight: 700; fill: #1d5d90; }
.chart .other polyline { stroke: #c4c4c4; stroke-width: 2; }
.chart .other circle { fill: #c4c4c4; }
.chart .youth polyline { stroke: #48c78e; stroke-width: 2.5; stroke-dasharray: 6 4; }
.chart .youth circle { fill: #48c78e; }
.chart .subject polyline { stroke: #3e8ed0; stroke-width: 4; }
.chart .subject circle { fill: #3e8ed0; }
.nowrap { white-space: nowrap; }
.key { display: inline-block; width: 18px; height: 0; vertical-align: middle; border-top: 3px solid; margin-right: 4px; }
.key.subject { border-color: #3e8ed0; }
.key.youth { border-color: #48c78e; border-top-style: dashed; }
.key.other { border-color: #c4c4c4; }
</style>
