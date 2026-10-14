<template>
    <div class="modal-card season-history-card" dusk="season-history-modal">
        <header class="modal-card-head">
            <div class="modal-card-title">
                {{ player.name }}
                <b-tag v-if="youth" type="is-success is-light" dusk="season-history-age-group">{{ ageGroupLabel(player) }}</b-tag>
                <p class="is-size-7 has-text-grey mt-1" dusk="season-history-explanation">
                    <template v-if="category">Rækkefølgen i {{ CATEGORY_NAMES[category] }} på tværs af klubbens hold (1. hold øverst).</template>
                    <template v-if="isDouble"> I double deler makkerne plads.</template>
                    Opstillinger, ikke resultater.
                </p>
            </div>
            <button type="button" class="delete" aria-label="Luk" dusk="season-history-close" @click="$emit('close')"></button>
        </header>
        <section class="modal-card-body">
            <b-loading :is-full-page="false" :active="$apollo.queries.rounds.loading"></b-loading>
            <b-message v-if="error" type="is-danger">Sæsonhistorikken kunne ikke hentes.</b-message>
            <template v-else-if="history">
                <p v-if="!history.hasEarlierRounds" class="has-text-grey" dusk="season-history-empty">{{ EMPTY_TEXT }}</p>
                <template v-else>
                    <b-field v-if="categories.length" class="mb-3">
                        <b-radio-button v-for="c in categories" :key="c" v-model="category" :native-value="c" size="is-small"
                                        :dusk="'season-history-category-' + c">
                            {{ c }}<span v-if="currentCategories.includes(c)" class="ml-1 has-text-grey">· nu</span>
                        </b-radio-button>
                    </b-field>
                    <div v-if="chart" class="chart-wrap">
                        <svg ref="chart" :viewBox="`0 0 ${W} ${H}`" class="chart" role="img" dusk="season-history-chart"
                             :aria-label="'Kategoriplads i ' + category + ' pr. holdrunde'" @mouseleave="hoverRound = null">
                            <rect v-if="hoverRound !== null" :x="x(hoverRound) - step / 2" y="0" :width="step" :height="H"
                                  class="hover-band" :data-round="hoverRound" dusk="season-history-hover-band"/>
                            <line v-for="(round, r) in rounds" :key="'grid-' + round.id" :x1="x(r)" :x2="x(r)" y1="24" :y2="H - 8" class="grid"/>
                            <text v-for="(round, r) in rounds" :key="'axis-' + round.id" :x="x(r)" y="14" text-anchor="middle"
                                  class="axis" :class="{'is-hovered': r === hoverRound}">{{ roundShortName(round) }}</text>
                            <text v-for="row in chart.places" :key="'place-' + row.place" :x="PAD_L - 8" :y="y(row.place) + 4"
                                  text-anchor="end" class="axis">{{ row.label }}</text>
                            <rect v-for="(round, r) in rounds" :key="'hit-' + round.id" :x="x(r) - step / 2" y="0" :width="step" :height="H"
                                  class="hit" :dusk="'season-history-round-' + r" @mouseenter="hoverRound = r" @click="hoverRound = r"/>
                            <g v-for="line in chart.lines" :key="line.refId" :class="line.kind">
                                <polyline v-for="(segment, i) in line.segments" :key="i" fill="none"
                                          :points="segment.map(point => `${x(point.round)},${y(point.place)}`).join(' ')"/>
                                <template v-for="segment in line.segments">
                                    <circle v-for="point in segment" :key="point.round" :cx="x(point.round)" :cy="y(point.place)"
                                            :r="line.kind === 'subject' ? 5 : 3.5" @mouseenter="hoverRound = point.round" @click="hoverRound = point.round">
                                        <title>{{ line.name }}: {{ history.placeLabel(category, point.place, point.round) }} ({{ rounds[point.round].name }})</title>
                                    </circle>
                                </template>
                            </g>
                            <text v-for="label in chart.labels" :key="label.key" :x="x(label.round) + 10" :y="y(label.place) + 4"
                                  class="label" :class="{subject: label.subject, youth: label.youth}">{{ label.text }}</text>
                        </svg>
                    </div>
                    <p v-if="chart" class="is-size-7 mt-2">
                        <span class="key subject"></span> {{ player.name }}
                        <span class="key youth ml-3"></span> Ungdomsspiller
                        <span class="key other ml-3"></span> Spillere højst 2 pladser fra
                    </p>
                    <div class="table-container mt-4">
                        <table class="table is-narrow is-fullwidth" dusk="season-history-table">
                            <thead>
                            <tr>
                                <th>Holdrunde</th>
                                <th v-if="category">Plads i {{ category }}</th>
                                <th>Opstillet</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="row in rows" :key="row.round.id" :class="{'is-hovered': row.r === hoverRound}"
                                :dusk="'season-history-row-' + row.r"
                                @mouseenter="hoverRound = row.r" @mouseleave="hoverRound = null" @click="hoverRound = row.r">
                                <td>
                                    {{ row.round.name }}
                                    <span class="is-size-7 has-text-grey nowrap">· {{ row.round.isCurrent ? 'nu' : formatDate(row.round.gameDate) }}</span>
                                </td>
                                <td v-if="category" class="nowrap">
                                    <template v-if="row.place">nr. {{ row.place }} <span class="has-text-grey">· {{ history.placeLabel(category, row.place, row.r) }}</span></template>
                                    <template v-else>–</template>
                                </td>
                                <td class="is-size-7">
                                    <em v-if="!row.placements.length" class="has-text-grey">{{ NOT_PLACED_TEXT }}</em>
                                    <span v-for="p in row.placements" :key="p.category" class="placement"
                                          :class="{'has-text-weight-semibold': p.category === category}">
                                        {{ p.category }} nr. {{ p.place }} · {{ p.label }}<span v-if="p.partners.length" class="has-text-grey has-text-weight-normal"> m. {{ p.partners.join(', ') }}</span>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </template>
        </section>
    </div>
</template>
<script>
import TeamRoundSeasonHistoryQuery from "../../../../queries/teamRoundSeasonHistory.graphql";
import {ageGroupLabel, isYoungPlayer} from "../../../helpers";
import {CATEGORY_NAMES, EMPTY_TEXT, isDoubleCategory, NOT_PLACED_TEXT, seasonHistory} from "./season-history.js";

const PAD_L = 120
const PAD_T = 40
const LABEL_W = 220
const ROW_H = 28
const W = 960

export default {
    name: 'SeasonHistoryModal',
    props: {
        teamRoundId: {type: String, required: true},
        // The lineup's player: refId, name and vintage
        player: {type: Object, required: true}
    },
    emits: ['close'],
    data() {
        return {
            CATEGORY_NAMES, EMPTY_TEXT, NOT_PLACED_TEXT, PAD_L, W,
            rounds: null,
            category: null,
            error: false,
            // The round pointed at in the chart or the table; both highlight it.
            hoverRound: null
        }
    },
    apollo: {
        rounds: {
            query: TeamRoundSeasonHistoryQuery,
            // The current round must match the lineup as it is saved now.
            fetchPolicy: 'network-only',
            variables() {
                return {id: this.teamRoundId}
            },
            update: data => data.teamRound.seasonHistory,
            result({data}) {
                if (data && this.category === null) {
                    this.category = seasonHistory(data.teamRound.seasonHistory).defaultCategory(this.player.refId)
                }
            },
            error() {
                this.error = true
            }
        }
    },
    computed: {
        history() {
            return this.rounds ? seasonHistory(this.rounds) : null
        },
        youth() {
            return isYoungPlayer(this.player)
        },
        categories() {
            return this.history.categoriesOf(this.player.refId)
        },
        currentCategories() {
            return this.history.currentCategoriesOf(this.player.refId)
        },
        isDouble() {
            return this.category !== null && isDoubleCategory(this.category)
        },
        chart() {
            return this.category ? this.history.chart(this.player.refId, this.category) : null
        },
        rows() {
            return this.rounds.map((round, r) => ({
                r,
                round,
                place: this.category ? this.history.placeOf(this.player.refId, this.category, r) : null,
                placements: this.history.placementsOf(this.player.refId, r)
            })).reverse()
        },
        H() {
            return PAD_T + Math.max(this.chart.places.length, 1) * ROW_H
        },
        step() {
            return (W - PAD_L - LABEL_W) / Math.max(this.rounds.length - 1, 1)
        }
    },
    methods: {
        ageGroupLabel,
        x(r) {
            return PAD_L + 20 + r * this.step
        },
        y(place) {
            return PAD_T + (place - (this.chart.places[0]?.place ?? 1)) * ROW_H
        },
        roundShortName(round) {
            if (round.isCurrent) {
                return 'Nu'
            }
            return round.round ? `R${round.round}` : this.formatDate(round.gameDate)
        },
        formatDate(gameDate) {
            return gameDate ? new Date(gameDate).toLocaleDateString('da-DK', {day: 'numeric', month: 'short'}) : ''
        }
    }
}
</script>
<style scoped>
.season-history-card { width: min(1040px, calc(100vw - 40px)); }
/* Bulma doesn't let the title shrink, so the explanation would push the close button off a phone screen. */
.season-history-card .modal-card-title { flex-shrink: 1; min-width: 0; }
.season-history-card .modal-card-head .delete { flex-shrink: 0; margin-left: 0.75rem; }
.chart-wrap { overflow-x: auto; }
.chart { width: 100%; min-width: 600px; height: auto; }
.chart .axis { font-size: 11px; fill: #7a7a7a; }
.chart .axis.is-hovered { fill: #1d5d90; font-weight: 700; }
.chart .grid { stroke: #f0f0f0; }
.chart .hover-band { fill: #eef6fc; }
.chart .hit { fill: transparent; cursor: pointer; }
.chart .label { font-size: 12px; fill: #4a4a4a; }
.chart .label.youth { fill: #257953; }
.chart .label.subject { font-weight: 700; fill: #1d5d90; }
.chart .other polyline { stroke: #c4c4c4; stroke-width: 2; }
.chart .other circle { fill: #c4c4c4; }
.chart .youth polyline { stroke: #48c78e; stroke-width: 2.5; stroke-dasharray: 6 4; }
.chart .youth circle { fill: #48c78e; }
.chart .subject polyline { stroke: #3e8ed0; stroke-width: 4; }
.chart .subject circle { fill: #3e8ed0; }
tr.is-hovered td { background: #eef6fc; }
tbody tr { cursor: pointer; }
.placement { display: block; }
.nowrap { white-space: nowrap; }
.key { display: inline-block; width: 18px; height: 0; vertical-align: middle; border-top: 3px solid; margin-right: 4px; }
.key.subject { border-color: #3e8ed0; }
.key.youth { border-color: #48c78e; border-top-style: dashed; }
.key.other { border-color: #c4c4c4; }
</style>
