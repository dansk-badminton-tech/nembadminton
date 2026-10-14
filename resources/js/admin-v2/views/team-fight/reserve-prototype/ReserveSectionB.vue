<template>
    <!-- PROTOTYPE B (#227): a reserve section below the Squad's category slots. -->
    <div class="prototype-box" :dusk="'reserve-section-b-' + squadIndex">
        <div class="is-flex is-justify-content-space-between is-align-items-center is-flex-wrap-wrap mb-2" style="gap: 0.5rem;">
            <strong>Reserver (§43)</strong>
            <b-autocomplete
                v-model="search"
                class="reserve-search"
                size="is-small"
                icon="magnify"
                field="name"
                placeholder="Søg spiller fra lavere hold..."
                :data="filteredCandidates"
                :open-on-focus="true"
                :clear-on-select="true"
                :keep-first="true"
                dusk="add-reserve-b"
                @select="addFromSquad">
                <template #default="props">
                    {{ props.option.player.name }}
                    <small class="has-text-grey">· Hold {{ props.option.fromIndex + 1 }} · {{ props.option.category }}</small>
                </template>
                <template #empty>Ingen spillere på lavere hold matcher.</template>
            </b-autocomplete>
        </div>
        <p v-if="!reserves.length" class="has-text-grey is-size-7">
            Ingen reserver. Søg en spiller fra et lavere hold her, eller tilføj en ledig spiller med
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
            <b-tag v-if="reserve.parallelAllocation" size="is-small" type="is-warning is-light" class="is-pulled-left ml-2">
                <b-icon icon="alert-circle" size="is-small" class="mr-1"/>
                Optaget på: {{ reserve.parallelAllocation.teamRoundName }}
            </b-tag>
            <b-tag v-if="movedHigher(reserve)" size="is-small" type="is-danger is-light" class="is-pulled-left ml-2">
                Står nu på et højere hold
            </b-tag>
            <div class="buttons is-pulled-right">
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
    data() {
        return {search: ''}
    },
    computed: {
        squad() {
            return this.squads[this.squadIndex]
        },
        reserves() {
            return reservesB(this.squad.id)
        },
        // Only selectable players: Members on lower Squads who aren't a reserve yet.
        candidates() {
            const reserved = new Set(this.squads.flatMap(squad => reservesB(squad.id).map(r => r.refId)))
            return candidatesB(this.squads, this.squadIndex).filter(c => c.allowed && !reserved.has(c.player.refId))
        },
        filteredCandidates() {
            const needle = this.search.trim().toLowerCase()
            return needle === '' ? this.candidates : this.candidates.filter(c => c.player.name.toLowerCase().includes(needle))
        }
    },
    methods: {
        removeReserveB,
        addFromSquad(candidate) {
            if (!candidate) return
            const {player, fromIndex, category} = candidate
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
.reserve-search {
    min-width: 16rem;
}
.reserve-row {
    padding: 0.25rem 0;
    border-top: 1px solid #ede9fe;
}
</style>
