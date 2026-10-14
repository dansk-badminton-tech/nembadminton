<template>
    <!-- PROTOTYPE B (#227): a reserve section below the Squad's category slots. -->
    <div class="prototype-box" :dusk="'reserve-section-b-' + squadIndex">
        <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
            <strong>Reserver (§43)</strong>
            <b-dropdown aria-role="list" position="is-bottom-left" scrollable max-height="300">
                <template #trigger>
                    <b-button size="is-small" icon-left="plus" dusk="add-reserve-b">Tilføj reserve fra andet hold</b-button>
                </template>
                <b-dropdown-item v-if="!candidates.length" custom aria-role="listitem" class="has-text-grey">
                    Ingen spillere på andre hold.
                </b-dropdown-item>
                <b-dropdown-item v-for="candidate in candidates" :key="candidate.player.refId" aria-role="listitem"
                                 :disabled="!candidate.allowed" @click="candidate.allowed && addFromSquad(candidate)">
                    <div>{{ candidate.player.name }}
                        <span class="has-text-grey is-size-7">· Hold {{ candidate.fromIndex + 1 }} · {{ candidate.category }}</span>
                    </div>
                    <div v-if="!candidate.allowed" class="is-size-7 has-text-danger">Står på et højere hold, kan ikke være reserve her</div>
                </b-dropdown-item>
            </b-dropdown>
        </div>
        <p v-if="!reserves.length" class="has-text-grey is-size-7">
            Ingen reserver. Tilføj en spiller fra et lavere hold her, eller en ledig spiller med
            <b-icon icon="account-switch" size="is-small"/> i spillerlisten.
        </p>
        <div v-for="reserve in reserves" :key="reserve.refId" class="reserve-row is-clearfix">
            <span class="is-pulled-left">
                <b-icon :icon="reserve.gender === 'MEN' ? 'gender-male' : 'gender-female'" size="is-small"/>
                {{ reserve.name }}
            </span>
            <b-tag size="is-small" class="is-pulled-left ml-2" :class="reserve.source === 'available' ? 'is-light' : 'is-info is-light'">
                {{ reserve.source === 'available' ? 'Ledig spiller' : `Står også på Hold ${reserve.source + 1} · ${reserve.category}` }}
            </b-tag>
            <b-tooltip class="is-pulled-left ml-2" type="is-info" multilined
                       label="Reserven står uden for kategorierne og har ingen kategoriplads. De andre spilleres kategoripladser ændrer sig ikke.">
                <b-tag size="is-small" class="no-rank">Ingen kategoriplads</b-tag>
            </b-tooltip>
            <b-tag v-if="reserve.parallelAllocation" size="is-small" type="is-warning is-light" class="is-pulled-left ml-2">
                <b-icon icon="alert-circle" size="is-small" class="mr-1"/>
                Optaget på: {{ reserve.parallelAllocation.teamRoundName }}
            </b-tag>
            <b-tag v-if="movedHigher(reserve)" size="is-small" type="is-danger is-light" class="is-pulled-left ml-2">
                Står nu på et højere hold
            </b-tag>
            <div class="buttons is-pulled-right">
                <b-button size="is-small" title="Sæsonhistorik" icon-right="history" @click="$emit('open-season-history', reserve)"/>
                <b-button size="is-small" title="Fjern reserve" icon-right="close" @click="removeReserveB(squad.id, reserve.refId)"/>
            </div>
        </div>
    </div>
</template>
<script>
import {addReserveB, candidatesB, placedSquadIndex, removeReserveB, reservesB} from "./reserve-prototype.js";

export default {
    name: 'ReserveSectionB',
    props: {
        squads: {type: Array, required: true},
        squadIndex: {type: Number, required: true}
    },
    emits: ['open-season-history'],
    computed: {
        squad() {
            return this.squads[this.squadIndex]
        },
        reserves() {
            return reservesB(this.squad.id)
        },
        candidates() {
            const reserved = new Set(this.reserves.map(r => r.refId))
            return candidatesB(this.squads, this.squadIndex).filter(c => !reserved.has(c.player.refId))
        }
    },
    methods: {
        removeReserveB,
        addFromSquad({player, fromIndex, category}) {
            addReserveB(this.squad.id, {
                refId: player.refId,
                name: player.name,
                gender: player.gender,
                vintage: player.vintage,
                points: player.points,
                parallelAllocation: player.parallelAllocation,
                source: fromIndex,
                category
            })
        },
        movedHigher(reserve) {
            const index = placedSquadIndex(this.squads, reserve.refId)
            return index !== -1 && index < this.squadIndex
        }
    }
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
.reserve-row {
    padding: 0.25rem 0;
    border-top: 1px solid #ede9fe;
}
.no-rank {
    background: transparent;
    border: 1px dashed #b5b5b5;
    color: #7a7a7a;
}
</style>
