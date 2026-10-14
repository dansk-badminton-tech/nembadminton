<!-- PROTOTYPE (issue #254) quick A: popover with the player's place in the club-wide order and who moved past whom. Throwaway. -->
<template>
    <b-dropdown position="is-bottom-left" aria-role="dialog">
        <template #trigger>
            <b-button size="is-small" title="Placering i rækkefølgen" icon-right="history" dusk="season-history-trigger"></b-button>
        </template>
        <b-dropdown-item custom paddingless aria-role="listitem">
            <div class="popover-body">
                <p class="heading mb-2">Placering i klubbens rækkefølge</p>
                <p v-if="!season.hasEarlier" class="has-text-grey is-size-7">{{ EMPTY_TEXT }}</p>
                <div v-for="h in recent" :key="h.round.key" class="entry">
                    <p class="is-size-7 has-text-grey">{{ h.round.name }} · {{ formatDate(h.round.gameDate) }}</p>
                    <p v-if="!h.placed" class="has-text-grey-light"><em>{{ NOT_PLACED_TEXT }}</em></p>
                    <template v-else>
                        <p>
                            <strong>Nr. {{ h.rank }}</strong> blandt {{ season.genderWord(player.refId) }}
                            <span class="delta" :class="h.delta > 0 ? 'up' : 'down'">{{ deltaText(h.delta) }}</span>
                        </p>
                        <p v-for="p in h.placements" :key="p.label" class="is-size-7">
                            {{ formatPlacement(p) }}<span v-if="p.partnerName" class="has-text-grey"> m. {{ p.partnerName }}</span>
                        </p>
                    </template>
                </div>
                <div v-if="moves.length" class="moves">
                    <p class="heading mb-1">Ændringer i rækkefølgen</p>
                    <p v-for="(m, i) in moves" :key="i" class="is-size-7">
                        <span :class="m.up ? 'up' : 'down'">{{ m.up ? '▲' : '▼' }}</span> {{ m.text }}
                        <span class="has-text-grey">({{ m.round }})</span>
                    </p>
                </div>
            </div>
        </b-dropdown-item>
    </b-dropdown>
</template>
<script>
import {EMPTY_TEXT, NOT_PLACED_TEXT, formatDate, formatPlacement, deltaText} from './season.js'

export default {
    name: 'QuickOrderPopover',
    props: {season: Object, player: Object},
    data: () => ({EMPTY_TEXT, NOT_PLACED_TEXT}),
    computed: {
        recent() {
            return this.season.history(this.player.refId).slice(-4).reverse()
        },
        moves() {
            return this.recent.flatMap(h => [
                ...h.passed.map(name => ({up: true, text: `Rykkede forbi ${name}`, round: h.round.name})),
                ...h.passedBy.map(name => ({up: false, text: `${name} rykkede forbi`, round: h.round.name})),
            ])
        }
    },
    methods: {formatDate, formatPlacement, deltaText}
}
</script>
<style scoped>
.popover-body {
    padding: 0.75rem 1rem;
    min-width: 280px;
    max-width: 360px;
    white-space: normal;
}
.entry + .entry, .moves {
    border-top: 1px solid #eee;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
}
.delta { font-size: 0.75rem; margin-left: 0.25rem; }
.up { color: #257953; }
.down { color: #cc0f35; }
</style>
