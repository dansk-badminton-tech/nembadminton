<!-- PROTOTYPE (issue #254) variant A: popover from a history icon next to the player's buttons. Throwaway. -->
<template>
    <b-dropdown position="is-bottom-left" aria-role="dialog" :close-on-click="true" class="season-history-a">
        <template #trigger>
            <b-button size="is-small" title="Opstillet tidligere i sæsonen" icon-right="history" dusk="season-history-trigger"></b-button>
        </template>
        <b-dropdown-item custom paddingless aria-role="listitem">
            <div class="popover-body">
                <p class="heading mb-2">{{ TITLE }}</p>
                <p v-if="history.length === 0" class="has-text-grey is-size-7">{{ EMPTY_TEXT }}</p>
                <div v-for="entry in history" :key="entry.teamRoundId" class="entry">
                    <p class="is-size-7 has-text-grey">
                        {{ formatDate(entry.gameDate) }} · {{ entry.name }}
                    </p>
                    <p v-if="!entry.placed" class="has-text-grey-light"><em>{{ NOT_PLACED_TEXT }}</em></p>
                    <p v-for="p in entry.placements" :key="p.category">{{ formatPlacement(p) }}</p>
                </div>
            </div>
        </b-dropdown-item>
    </b-dropdown>
</template>
<script>
import {seasonHistoryFor, formatDate, formatPlacement, EMPTY_TEXT, NOT_PLACED_TEXT, TITLE} from './stub.js'

export default {
    name: 'SeasonHistoryVariantA',
    props: {player: Object, empty: Boolean},
    data: () => ({EMPTY_TEXT, NOT_PLACED_TEXT, TITLE}),
    computed: {
        history() {
            return seasonHistoryFor(this.player, {empty: this.empty})
        }
    },
    methods: {formatDate, formatPlacement}
}
</script>
<style scoped>
.popover-body {
    padding: 0.75rem 1rem;
    min-width: 260px;
    max-width: 340px;
    white-space: normal;
}
.entry + .entry {
    border-top: 1px solid #eee;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
}
</style>
