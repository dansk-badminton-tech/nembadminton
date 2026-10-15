<template>
    <!-- PROTOTYPE A (#227): records the coach's decision "X (from a lower Squad) is reserve for Y (on this Squad)",
         like the "Reserver" section on badmintonplayer.dk's match page. -->
    <div class="prototype-box" :dusk="'reserve-marks-a-' + squadIndex">
        <p class="mb-2"><strong>Reserver (§43)</strong></p>
        <p v-if="!reserves.length" class="has-text-grey is-size-7 mb-3">
            Ingen. Melder en spiller på Hold {{ squadIndex + 1 }} afbud pga. skade, sygdom eller force majeure,
            så vælg reserven fra et lavere hold og hvem vedkommende erstatter.
        </p>
        <div v-for="reserve in reserves" :key="reserve.refId" class="reserve-row" dusk="reserve-row-a">
            <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
                <b-icon icon="account-switch" class="has-text-primary"/>
                <div class="is-flex-grow-1">
                    <div><strong>{{ reserve.name }}</strong> for <strong>{{ reserve.forName }}</strong></div>
                    <div class="is-size-7 has-text-grey">Fra Hold {{ reserve.fromIndex + 1 }} · {{ reserve.fromCategory }}</div>
                    <div class="tags mt-1 mb-0" v-if="warnings(reserve).length">
                        <b-tag v-for="warning in warnings(reserve)" :key="warning" size="is-small" type="is-warning is-light">{{ warning }}</b-tag>
                    </div>
                </div>
                <b-button size="is-small" title="Fjern reserve" icon-right="close" @click="removeReserveA(squad.id, reserve.refId)"/>
            </div>
        </div>
        <div class="add-form">
            <b-field :label="`Reserve fra et lavere hold`" class="mb-3">
                <b-autocomplete
                    v-model="reserveSearch"
                    field="player.name"
                    icon="magnify"
                    placeholder="Søg spiller på et lavere hold..."
                    :data="filteredReserveCandidates"
                    :open-on-focus="true"
                    :keep-first="true"
                    dusk="reserve-a-player"
                    @select="option => reserveCandidate = option">
                    <template #default="props">
                        {{ props.option.player.name }}
                        <small class="has-text-grey">· Hold {{ props.option.fromIndex + 1 }} · {{ props.option.category }}</small>
                    </template>
                    <template #empty>Ingen spillere på lavere hold matcher.</template>
                </b-autocomplete>
            </b-field>
            <b-field :label="`Erstatter (spiller på Hold ${squadIndex + 1})`" class="mb-3">
                <b-autocomplete
                    v-model="forSearch"
                    field="name"
                    icon="magnify"
                    :placeholder="`Søg spiller på Hold ${squadIndex + 1}...`"
                    :data="filteredForCandidates"
                    :open-on-focus="true"
                    :keep-first="true"
                    dusk="reserve-a-for"
                    @select="option => forPlayer = option">
                    <template #default="props">
                        {{ props.option.name }} <small class="has-text-grey">· {{ props.option.categories.join(', ') }}</small>
                    </template>
                    <template #empty>Ingen spillere på Hold {{ squadIndex + 1 }} matcher.</template>
                </b-autocomplete>
            </b-field>
            <div class="has-text-right">
                <b-button type="is-primary" icon-left="plus" :disabled="!reserveCandidate || !forPlayer"
                          dusk="reserve-a-add" @click="add">Tilføj reserve</b-button>
            </div>
        </div>
    </div>
</template>
<script>
import {addReserveA, candidatesB, placedSquadIndex, removeReserveA, reservesA} from "./reserve-prototype.js";

export default {
    name: 'ReserveMarksA',
    props: {
        squads: {type: Array, required: true},
        squadIndex: {type: Number, required: true}
    },
    data() {
        return {
            reserveSearch: '',
            reserveCandidate: null,
            forSearch: '',
            forPlayer: null
        }
    },
    computed: {
        squad() {
            return this.squads[this.squadIndex]
        },
        reserves() {
            return reservesA(this.squad.id)
        },
        // Players placed on this Squad, with their categories.
        placed() {
            const byRefId = new Map()
            this.squad.categories.forEach(category => category.players.forEach(player => {
                const entry = byRefId.get(player.refId) ?? {refId: player.refId, name: player.name, categories: []}
                entry.categories.push(category.name)
                byRefId.set(player.refId, entry)
            }))
            return [...byRefId.values()]
        },
        // Reserve: a Member placed on a lower Squad who isn't a reserve on this Squad yet.
        filteredReserveCandidates() {
            const reserved = new Set(this.reserves.map(r => r.refId))
            const needle = this.reserveSearch.trim().toLowerCase()
            return candidatesB(this.squads, this.squadIndex)
                .filter(c => c.allowed && !reserved.has(c.player.refId) && c.player.name.toLowerCase().includes(needle))
        },
        // Replaced: a Member placed on this Squad who hasn't been replaced yet.
        filteredForCandidates() {
            const replaced = new Set(this.reserves.map(r => r.forRefId))
            const needle = this.forSearch.trim().toLowerCase()
            return this.placed.filter(p => !replaced.has(p.refId) && p.name.toLowerCase().includes(needle))
        }
    },
    methods: {
        removeReserveA,
        warnings(reserve) {
            const warnings = []
            if (!this.placed.some(p => p.refId === reserve.forRefId)) {
                warnings.push(`${reserve.forName} står ikke længere på Hold ${this.squadIndex + 1}`)
            }
            const index = placedSquadIndex(this.squads, reserve.refId)
            if (index !== -1 && index <= this.squadIndex) {
                warnings.push(`${reserve.name} står nu på Hold ${index + 1}, ikke et lavere hold`)
            }
            return warnings
        },
        add() {
            const {player, fromIndex, category} = this.reserveCandidate
            addReserveA(this.squad.id, {
                refId: player.refId,
                name: player.name,
                gender: player.gender,
                vintage: player.vintage,
                points: player.points,
                fromIndex,
                fromCategory: category,
                forRefId: this.forPlayer.refId,
                forName: this.forPlayer.name
            })
            this.reserveCandidate = this.forPlayer = null
            this.reserveSearch = this.forSearch = ''
        }
    }
}
</script>
<style scoped>
.prototype-box {
    border: 2px dashed #8b5cf6;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    margin-top: -0.75rem;
    background: #faf5ff;
}
.reserve-row {
    padding: 0.5rem 0;
    border-top: 1px solid #ede9fe;
}
.add-form {
    border-top: 1px solid #ede9fe;
    padding-top: 0.75rem;
    margin-top: 0.25rem;
}
</style>
