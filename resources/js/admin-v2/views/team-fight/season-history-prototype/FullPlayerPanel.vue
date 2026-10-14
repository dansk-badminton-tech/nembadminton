<!-- PROTOTYPE (issue #254) full D: wide side panel with every earlier round, partners and the players around. Throwaway. -->
<template>
    <b-sidebar :model-value="true" class="full-player-panel" position="fixed" right overlay fullheight
               :mobile="'fullwidth'" :can-cancel="['escape', 'outside']" @close="$emit('close')">
        <div class="panel-body" dusk="full-player-panel">
            <div class="is-flex is-justify-content-space-between is-align-items-start mb-3">
                <div>
                    <p class="heading mb-0">Spilleroverblik · sæsonen</p>
                    <p class="title is-4 mb-1">
                        {{ info.name }}
                        <b-tag v-if="info.youth" type="is-success is-light">{{ info.vintage }}{{ info.simulatedYouth ? ' (simuleret)' : '' }}</b-tag>
                    </p>
                    <p v-if="current.placed" class="has-text-grey">Nr. {{ current.rank }} blandt {{ season.genderWord(info.refId) }} i denne runde</p>
                </div>
                <button class="delete" aria-label="Luk" @click="$emit('close')"></button>
            </div>
            <p class="is-size-7 has-text-grey mb-3">Klubbens holdrunder i sæsonen. Viser opstillinger, ikke kampe spillet.</p>
            <p v-if="!season.hasEarlier" class="has-text-grey mb-3">{{ EMPTY_TEXT }}</p>
            <div class="table-container">
                <table class="table is-narrow is-fullwidth">
                    <thead>
                    <tr><th>Runde</th><th>Nr.</th><th>Opstillet</th><th>Omkring</th></tr>
                    </thead>
                    <tbody>
                    <tr v-for="h in rows" :key="h.round.key" :class="{'is-selected-row': h.round.isCurrent}">
                        <td class="nowrap">{{ h.round.name }}<br><span class="is-size-7 has-text-grey">{{ formatDate(h.round.gameDate) }}</span></td>
                        <td class="nowrap">
                            <template v-if="h.placed">
                                <strong>{{ h.rank }}</strong>
                                <span class="delta" :class="h.delta > 0 ? 'up' : 'down'"> {{ deltaText(h.delta) }}</span>
                            </template>
                        </td>
                        <td>
                            <em v-if="!h.placed" class="has-text-grey">{{ NOT_PLACED_TEXT }}</em>
                            <p v-for="p in h.placements" :key="p.label" class="is-size-7">
                                {{ formatPlacement(p) }}<span v-if="p.partnerName" class="has-text-grey"><br>m. {{ p.partnerName }}</span>
                            </p>
                        </td>
                        <td class="is-size-7">
                            <template v-if="h.placed">
                                <p v-if="neighbours(h).above">↑ {{ neighbours(h).above.name }}</p>
                                <p v-if="neighbours(h).below">↓ {{ neighbours(h).below.name }}</p>
                                <p v-for="name in h.passedBy" :key="'b' + name" class="down">▼ {{ name }} rykkede forbi</p>
                                <p v-for="name in h.passed" :key="'p' + name" class="up">▲ Rykkede forbi {{ name }}</p>
                            </template>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </b-sidebar>
</template>
<script>
import {EMPTY_TEXT, NOT_PLACED_TEXT, formatDate, formatPlacement, deltaText} from './season.js'

export default {
    name: 'FullPlayerPanel',
    props: {season: Object, player: Object},
    emits: ['close'],
    data: () => ({EMPTY_TEXT, NOT_PLACED_TEXT}),
    computed: {
        info() {
            return this.season.players.get(this.player.refId)
        },
        rows() {
            return this.season.history(this.player.refId).reverse()
        },
        current() {
            return this.rows[0]
        }
    },
    methods: {
        formatDate, formatPlacement, deltaText,
        neighbours(h) {
            return this.season.neighbours(this.player.refId, h.r)
        }
    }
}
</script>
<style scoped>
.panel-body { padding: 1.5rem; }
.nowrap { white-space: nowrap; }
.delta { font-size: 0.75rem; }
.up { color: #257953; }
.down { color: #cc0f35; }
.is-selected-row { background: #f5f9fd; }
</style>
<style>
.full-player-panel .sidebar-content {
    width: 640px !important;
    max-width: 100vw !important;
}
</style>
