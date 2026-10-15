<template>
    <!-- PROTOTYPE A (#227): records the coach's decision "X (from a lower Squad) is reserve for Y (on this Squad)",
         like the "Reserver" section on badmintonplayer.dk's match page. -->
    <!-- Minimal by default: only a small link between the Squads, the form opens on demand. -->
    <!-- PROTOTYPE: three suggestions for the collapsed look, stacked so they can be compared. -->
    <div v-if="!reserves.length && !adding" class="reserve-suggestions" :dusk="'reserve-marks-a-' + squadIndex">
        <p class="suggestion-label">Forslag 1: bjælke med knap</p>
        <div class="suggestion-1">
            <span><b-icon icon="account-switch" size="is-small"/> <strong>Reserver</strong>
                <span class="has-text-grey">· ingen på Hold {{ squadIndex + 1 }}</span></span>
            <b-button size="is-small" icon-left="plus" dusk="reserve-a-open" @click="adding = true">Tilføj reserve</b-button>
        </div>

        <p class="suggestion-label">Forslag 2: stiplet felt</p>
        <a class="suggestion-2" dusk="reserve-a-open-2" @click="adding = true">
            <b-icon icon="plus-circle-outline"/>
            <span>
                <strong>Tilføj reserve til Hold {{ squadIndex + 1 }}</strong><br>
                <span class="is-size-7 has-text-grey">Kun hvis en spiller melder afbud pga. skade, sygdom eller force majeure (§43)</span>
            </span>
        </a>

        <p class="suggestion-label">Forslag 3: række i holdtabellen</p>
        <div class="suggestion-3">
            <span class="suggestion-3-label">Reserve</span>
            <span class="has-text-grey is-flex-grow-1">Ingen</span>
            <b-button size="is-small" type="is-primary is-light" icon-left="account-switch" dusk="reserve-a-open-3" @click="adding = true">Tilføj</b-button>
        </div>
    </div>
    <div v-else class="prototype-box" :dusk="'reserve-marks-a-' + squadIndex">
        <p class="mb-2"><strong>Reserver på Hold {{ squadIndex + 1 }}</strong></p>
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
        <div v-if="!adding" class="pt-2">
            <a class="is-size-7" dusk="reserve-a-open" @click="adding = true">
                <b-icon icon="plus" size="is-small"/> Tilføj endnu en reserve
            </a>
        </div>
        <div v-else class="add-form">
            <p class="has-text-grey is-size-7 mb-3">
                Melder en spiller på Hold {{ squadIndex + 1 }} afbud pga. skade, sygdom eller force majeure (§43),
                så vælg reserven fra et lavere hold og hvem vedkommende erstatter.
            </p>
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
            <div class="buttons is-right">
                <b-button dusk="reserve-a-cancel" @click="cancel">Annuller</b-button>
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
            forPlayer: null,
            adding: false
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
            this.cancel()
        },
        cancel() {
            this.reserveCandidate = this.forPlayer = null
            this.reserveSearch = this.forSearch = ''
            this.adding = false
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
.reserve-suggestions {
    margin-top: -0.75rem;
    margin-bottom: 1rem;
}
.suggestion-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #8b5cf6;
    margin: 0.75rem 0 0.25rem;
}
.suggestion-1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #f5f5f5;
    border-radius: 6px;
}
.suggestion-2 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: 2px dashed #dbdbdb;
    border-radius: 6px;
    color: #4a4a4a;
}
.suggestion-2:hover {
    border-color: #8b5cf6;
    background: #faf5ff;
}
.suggestion-3 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-top: 1px solid #ededed;
    border-bottom: 1px solid #ededed;
}
.suggestion-3-label {
    width: 3.5rem;
    font-weight: 700;
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
