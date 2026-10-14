<template>
    <!-- PROTOTYPE A (#227): reserves are placed Members with a mark. -->
    <div class="prototype-box" :dusk="'reserve-marks-a-' + squadIndex">
        <div class="is-flex is-align-items-center is-flex-wrap-wrap" style="gap: 0.5rem;">
            <strong>Reserver på Hold {{ squadIndex + 1 }}</strong>
            <b-tag v-for="player in marked" :key="player.refId" closable
                   class="reserve-tag" @close="toggleMarkA(squad.id, player.refId)">
                {{ player.name }}
            </b-tag>
            <span v-if="!marked.length" class="has-text-grey is-size-7">Ingen. Klik <b-icon icon="account-switch" size="is-small"/> på en spiller eller vælg her:</span>
            <b-dropdown aria-role="list" scrollable max-height="260">
                <template #trigger>
                    <b-button size="is-small" icon-left="account-switch">Markér reserve</b-button>
                </template>
                <b-dropdown-item v-for="player in placed" :key="player.refId" aria-role="listitem"
                                 @click="toggleMarkA(squad.id, player.refId)">
                    <b-icon :icon="isMarkedA(squad.id, player.refId) ? 'checkbox-marked' : 'checkbox-blank-outline'" size="is-small"/>
                    {{ player.name }} <span class="has-text-grey is-size-7">{{ player.categories.join(', ') }}</span>
                </b-dropdown-item>
            </b-dropdown>
        </div>
        <p class="is-size-7 has-text-grey mt-1">
            Reserven står på sine kategoripladser som alle andre og har kategoriplads som normalt.
            Kun spillere, der står på Hold {{ squadIndex + 1 }}, kan markeres.
        </p>
    </div>
</template>
<script>
import {isMarkedA, reserveState, toggleMarkA} from "./reserve-prototype.js";

export default {
    name: 'ReserveMarksA',
    props: {
        squad: {type: Object, required: true},
        squadIndex: {type: Number, required: true}
    },
    computed: {
        placed() {
            const byRefId = new Map()
            this.squad.categories.forEach(category => category.players.forEach(player => {
                const entry = byRefId.get(player.refId) ?? {refId: player.refId, name: player.name, categories: []}
                entry.categories.push(category.name)
                byRefId.set(player.refId, entry)
            }))
            return [...byRefId.values()]
        },
        marked() {
            const marks = reserveState.marksA[this.squad.id] ?? []
            return this.placed.filter(player => marks.includes(player.refId))
        }
    },
    methods: {isMarkedA, toggleMarkA}
}
</script>
<style scoped>
.prototype-box {
    border: 2px dashed #8b5cf6;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    margin-top: -0.75rem;
    background: #faf5ff;
}
.reserve-tag {
    background: #ede9fe;
    color: #5b21b6;
}
</style>
