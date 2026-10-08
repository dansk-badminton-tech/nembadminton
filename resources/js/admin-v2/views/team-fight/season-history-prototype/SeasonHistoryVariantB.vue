<!-- PROTOTYPE (issue #254) variant B: expandable strip under the player row, one column per earlier TeamRound. Throwaway. -->
<template>
    <div class="season-history-b" dusk="season-history-panel">
        <p class="heading mb-1">{{ TITLE }}</p>
        <p v-if="history.length === 0" class="has-text-grey is-size-7">{{ EMPTY_TEXT }}</p>
        <div class="columns is-mobile is-multiline is-gapless mb-0">
            <div v-for="entry in history" :key="entry.teamRoundId" class="column round">
                <p class="is-size-7 has-text-weight-semibold">{{ entry.name }}</p>
                <p class="is-size-7 has-text-grey mb-1">{{ formatDate(entry.gameDate) }}</p>
                <p v-if="!entry.placed" class="is-size-7 has-text-grey-light"><em>{{ NOT_PLACED_TEXT }}</em></p>
                <template v-for="p in entry.placements" :key="p.category">
                    <p class="is-size-7">
                        <strong>{{ p.position }}. {{ p.category }}</strong>
                    </p>
                    <p class="is-size-7 has-text-grey">{{ [p.teamName, p.tier].filter(Boolean).join(' · ') }}</p>
                </template>
            </div>
        </div>
    </div>
</template>
<script>
import {seasonHistoryFor, formatDate, EMPTY_TEXT, NOT_PLACED_TEXT, TITLE} from './stub.js'

export default {
    name: 'SeasonHistoryVariantB',
    props: {player: Object, empty: Boolean},
    data: () => ({EMPTY_TEXT, NOT_PLACED_TEXT, TITLE}),
    computed: {
        history() {
            return seasonHistoryFor(this.player, {empty: this.empty})
        }
    },
    methods: {formatDate}
}
</script>
<style scoped>
.season-history-b {
    clear: both;
    margin: 0.25rem 0 0.5rem 1.75rem;
    padding: 0.5rem 0.75rem;
    border-left: 3px solid #3e8ed0;
    background: #f5f9fd;
}
.round {
    min-width: 120px;
    padding-right: 0.75rem !important;
}
</style>
