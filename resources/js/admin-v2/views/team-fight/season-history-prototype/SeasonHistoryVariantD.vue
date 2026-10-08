<!-- PROTOTYPE (issue #254) variant D: always-visible mini timeline under the player name, details on hover/tap. Throwaway. -->
<template>
    <div class="season-history-d" dusk="season-history-strip">
        <span class="lead">Tidl. opstillet:</span>
        <span v-if="history.length === 0" class="has-text-grey-light"><em>{{ EMPTY_TEXT }}</em></span>
        <b-tooltip v-for="entry in history" :key="entry.teamRoundId" type="is-dark" multilined :triggers="['hover', 'click']">
            <template #content>
                <strong>{{ entry.name }} · {{ formatDate(entry.gameDate) }}</strong><br>
                <span v-if="!entry.placed">{{ NOT_PLACED_TEXT }}</span>
                <span v-for="p in entry.placements" :key="p.category">{{ formatPlacement(p) }}<br></span>
            </template>
            <span class="chip" :class="entry.placed ? teamClass(entry) : 'is-empty'">
                <span class="round">R{{ entry.round }}</span>
                <span v-if="!entry.placed">–</span>
                <span v-else>{{ entry.placements.map(shortPlacement).join(' + ') }}</span>
            </span>
        </b-tooltip>
    </div>
</template>
<script>
import {seasonHistoryFor, formatDate, formatPlacement, shortPlacement, EMPTY_TEXT, NOT_PLACED_TEXT} from './stub.js'

export default {
    name: 'SeasonHistoryVariantD',
    props: {player: Object, empty: Boolean},
    data: () => ({EMPTY_TEXT, NOT_PLACED_TEXT}),
    computed: {
        history() {
            return seasonHistoryFor(this.player, {empty: this.empty})
        }
    },
    methods: {
        formatDate,
        formatPlacement,
        shortPlacement,
        teamClass(entry) {
            const name = entry.placements[0].teamName || ''
            const number = name.match(/(\d+)$/)
            return 'team-' + (number ? number[1] : 'x')
        }
    }
}
</script>
<style scoped>
.season-history-d {
    clear: both;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem;
    margin: 0.15rem 0 0.35rem 1.75rem;
    font-size: 0.75rem;
}
.lead {
    color: #7a7a7a;
    margin-right: 0.15rem;
}
.chip {
    display: inline-flex;
    gap: 0.3rem;
    padding: 0 0.4rem;
    border-radius: 4px;
    border: 1px solid #dbdbdb;
    cursor: default;
    white-space: nowrap;
}
.chip .round {
    color: #7a7a7a;
}
.chip.is-empty {
    border-style: dashed;
    color: #b5b5b5;
}
.chip.team-1 { background: #eef6fc; border-color: #9fc9ea; }
.chip.team-2 { background: #effaf5; border-color: #9ad8bd; }
.chip.team-3 { background: #fffaeb; border-color: #f1d48c; }
.chip.team-x { background: #f5f5f5; }
</style>
