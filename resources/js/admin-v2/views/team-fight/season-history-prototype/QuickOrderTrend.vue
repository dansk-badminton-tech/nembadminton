<!-- PROTOTYPE (issue #254) quick B: always-visible rank trend under the player name, flags when someone moved past. Throwaway. -->
<template>
    <div class="trend" dusk="season-history-trend">
        <span class="lead">Nr. i rækkefølgen:</span>
        <span v-if="!season.hasEarlier" class="has-text-grey-light"><em>{{ EMPTY_TEXT }}</em></span>
        <template v-else>
            <template v-for="(h, i) in recent" :key="h.round.key">
                <span v-if="i" class="sep">›</span>
                <b-tooltip type="is-dark" multilined :triggers="['hover', 'click']">
                    <template #content>
                        <strong>{{ h.round.name }} · {{ formatDate(h.round.gameDate) }}</strong><br>
                        <span v-if="!h.placed">{{ NOT_PLACED_TEXT }}</span>
                        <span v-for="p in h.placements" :key="p.label">{{ formatPlacement(p) }}<br></span>
                    </template>
                    <span class="rank" :class="{current: h.round.isCurrent, absent: !h.placed}">{{ h.placed ? h.rank : '–' }}</span>
                </b-tooltip>
            </template>
            <b-tag v-if="net" size="is-small" :type="net > 0 ? 'is-success is-light' : 'is-light'" class="ml-1">
                {{ net > 0 ? '▲' : '▼' }} {{ Math.abs(net) }} {{ Math.abs(net) === 1 ? 'plads' : 'pladser' }}
            </b-tag>
            <b-tooltip v-if="passedBy.length" type="is-warning" multilined :label="'Rykket forbi af: ' + passedBy.join(', ')">
                <b-tag size="is-small" type="is-warning is-light">{{ passedBy.length }} rykket forbi</b-tag>
            </b-tooltip>
        </template>
    </div>
</template>
<script>
import {EMPTY_TEXT, NOT_PLACED_TEXT, formatDate, formatPlacement} from './season.js'

export default {
    name: 'QuickOrderTrend',
    props: {season: Object, player: Object},
    data: () => ({EMPTY_TEXT, NOT_PLACED_TEXT}),
    computed: {
        recent() {
            return this.season.history(this.player.refId).slice(-4)
        },
        net() {
            const placed = this.recent.filter(h => h.placed)
            return placed.length > 1 ? placed[0].rank - placed[placed.length - 1].rank : 0
        },
        passedBy() {
            return [...new Set(this.recent.flatMap(h => h.passedBy))]
        }
    },
    methods: {formatDate, formatPlacement}
}
</script>
<style scoped>
.trend {
    clear: both;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem;
    margin: 0.15rem 0 0.35rem 1.75rem;
    font-size: 0.75rem;
}
.lead { color: #7a7a7a; margin-right: 0.15rem; }
.sep { color: #b5b5b5; }
.rank {
    display: inline-block;
    min-width: 1.5rem;
    text-align: center;
    border: 1px solid #dbdbdb;
    border-radius: 4px;
    cursor: default;
}
.rank.current { background: #3e8ed0; border-color: #3e8ed0; color: #fff; font-weight: 600; }
.rank.absent { border-style: dashed; color: #b5b5b5; }
</style>
