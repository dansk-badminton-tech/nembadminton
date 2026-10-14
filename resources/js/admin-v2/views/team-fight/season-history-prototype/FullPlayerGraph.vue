<!-- PROTOTYPE (issue #254) full E: modal with a rank chart of the player and the players around them across the season. Throwaway. -->
<template>
    <b-modal :model-value="true" has-modal-card :can-cancel="['escape', 'x', 'outside']" full-screen-mobile @close="$emit('close')">
        <div class="modal-card graph-card" dusk="full-player-graph">
            <header class="modal-card-head">
                <div class="modal-card-title">
                    {{ info.name }}
                    <b-tag v-if="info.youth" type="is-success is-light">{{ info.vintage }}{{ info.simulatedYouth ? ' (simuleret)' : '' }}</b-tag>
                    <p class="is-size-7 has-text-grey mt-1">Rækkefølgen blandt {{ season.genderWord(info.refId) }} i klubbens holdrunder. Opstillinger, ikke resultater.</p>
                </div>
                <button class="delete" aria-label="Luk" @click="$emit('close')"></button>
            </header>
            <section class="modal-card-body">
                <p v-if="!season.hasEarlier" class="has-text-grey">{{ EMPTY_TEXT }}</p>
                <div v-else class="chart-wrap">
                    <svg :viewBox="`0 0 ${W} ${H}`" class="chart" role="img">
                        <text v-for="(round, r) in season.rounds" :key="round.key" :x="x(r)" y="14" text-anchor="middle" class="axis">
                            {{ round.isCurrent ? 'Nu' : 'R' + (r + 1) }}
                        </text>
                        <line v-for="(round, r) in season.rounds" :key="'g' + round.key" :x1="x(r)" :x2="x(r)" :y1="24" :y2="H - 8" class="grid"/>
                        <text v-for="rank in rankRange" :key="'y' + rank" :x="PAD_L - 10" :y="y(rank) + 4" text-anchor="end" class="axis">{{ rank }}</text>
                        <g v-for="line in lines" :key="line.refId" :class="line.kind">
                            <title>{{ line.name }}</title>
                            <polyline v-for="(segment, i) in line.segments" :key="i" :points="segment" fill="none"/>
                            <circle v-for="pt in line.points" :key="pt.r" :cx="x(pt.r)" :cy="y(pt.rank)" :r="line.kind === 'subject' ? 5 : 3.5">
                                <title>{{ line.name }}: nr. {{ pt.rank }} ({{ season.rounds[pt.r].name }})</title>
                            </circle>
                            <text v-if="line.labelAt" :x="x(line.labelAt.r) + 10" :y="y(line.labelAt.rank) + 4" class="label">{{ line.name }}</text>
                        </g>
                    </svg>
                </div>
                <p class="is-size-7 mt-2">
                    <span class="key subject"></span> {{ info.name }}
                    <span class="key youth ml-3"></span> Ungdomsspiller
                    <span class="key other ml-3"></span> Spillere omkring
                </p>
                <table class="table is-narrow is-fullwidth mt-4">
                    <tbody>
                    <tr v-for="h in rows" :key="h.round.key">
                        <td class="nowrap">{{ h.round.name }} <span class="is-size-7 has-text-grey">· {{ formatDate(h.round.gameDate) }}</span></td>
                        <td class="nowrap">{{ h.placed ? 'Nr. ' + h.rank : '' }}</td>
                        <td class="is-size-7">
                            <em v-if="!h.placed" class="has-text-grey">{{ NOT_PLACED_TEXT }}</em>
                            <span v-for="p in h.placements" :key="p.label">
                                {{ formatPlacement(p) }}<span v-if="p.partnerName" class="has-text-grey"> m. {{ p.partnerName }}</span><br>
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
import {EMPTY_TEXT, NOT_PLACED_TEXT, formatDate, formatPlacement} from './season.js'

const PAD_L = 34
const PAD_T = 36
const LABEL_W = 190
const ROW_H = 28
const W = 820

export default {
    name: 'FullPlayerGraph',
    props: {season: Object, player: Object},
    emits: ['close'],
    data: () => ({EMPTY_TEXT, NOT_PLACED_TEXT, PAD_L, W}),
    computed: {
        info() {
            return this.season.players.get(this.player.refId)
        },
        rows() {
            return this.season.history(this.player.refId).reverse()
        },
        included() {
            const me = this.info.refId
            const R = this.season.rounds.length
            return this.season.order[this.info.gender].filter(other => {
                if (other === me) return true
                for (let r = 0; r < R; r++) {
                    const a = this.season.rankAt(me, r)
                    const b = this.season.rankAt(other, r)
                    if (a !== null && b !== null && Math.abs(a - b) <= 2) return true
                }
                return false
            })
        },
        rankRange() {
            const ranks = []
            this.included.forEach(refId => this.season.rounds.forEach((_, r) => {
                const rank = this.season.rankAt(refId, r)
                if (rank !== null) ranks.push(rank)
            }))
            const min = Math.min(...ranks)
            const max = Math.max(...ranks)
            return Array.from({length: max - min + 1}, (_, i) => min + i)
        },
        H() {
            return PAD_T + this.rankRange.length * ROW_H
        },
        lines() {
            const me = this.info.refId
            const R = this.season.rounds.length
            const lines = this.included.map(refId => {
                const p = this.season.players.get(refId)
                const points = []
                const segments = []
                let current = []
                for (let r = 0; r < R; r++) {
                    const rank = this.season.rankAt(refId, r)
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
                    kind: refId === me ? 'subject' : (p.youth ? 'youth' : 'other'),
                    points,
                    segments: segments.map(s => s.join(' ')),
                    labelAt: points[points.length - 1] || null,
                }
            })
            // Draw the subject last so it sits on top.
            return lines.sort((a, b) => (a.kind === 'subject') - (b.kind === 'subject'))
        }
    },
    methods: {
        formatDate, formatPlacement,
        x(r) {
            const R = this.season.rounds.length
            return PAD_L + 10 + r * ((W - PAD_L - LABEL_W - 10) / Math.max(R - 1, 1))
        },
        y(rank) {
            return PAD_T + (rank - this.rankRange[0]) * ROW_H
        }
    }
}
</script>
<style scoped>
.graph-card { width: min(900px, 100vw); }
.chart-wrap { overflow-x: auto; }
.chart { width: 100%; min-width: 560px; height: auto; }
.chart .axis { font-size: 11px; fill: #7a7a7a; }
.chart .grid { stroke: #f0f0f0; }
.chart .label { font-size: 12px; fill: #4a4a4a; }
.chart .other polyline { stroke: #c4c4c4; stroke-width: 2; }
.chart .other circle { fill: #c4c4c4; }
.chart .youth polyline { stroke: #48c78e; stroke-width: 2.5; stroke-dasharray: 6 4; }
.chart .youth circle { fill: #48c78e; }
.chart .youth .label { fill: #257953; }
.chart .subject polyline { stroke: #3e8ed0; stroke-width: 4; }
.chart .subject circle { fill: #3e8ed0; }
.chart .subject .label { font-weight: 700; fill: #1d5d90; }
.nowrap { white-space: nowrap; }
.key { display: inline-block; width: 18px; height: 0; vertical-align: middle; border-top: 3px solid; margin-right: 4px; }
.key.subject { border-color: #3e8ed0; }
.key.youth { border-color: #48c78e; border-top-style: dashed; }
.key.other { border-color: #c4c4c4; }
</style>
