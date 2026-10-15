<template>
    <!-- PROTOTYPE A (#227): the reserve plays in the category slots like anyone else. This section records the
         coach's decision "X is reserve for Y", like the "Reserver" section on badmintonplayer.dk's match page. -->
    <div class="prototype-box" :dusk="'reserve-marks-a-' + squadIndex">
        <strong>Reserver (§43)</strong>
        <p v-if="!reserves.length" class="has-text-grey is-size-7">
            Ingen. Har en spiller meldt afbud pga. skade, sygdom eller force majeure, så sæt reserven ind på pladsen og registrér det her.
        </p>
        <div v-for="reserve in reserves" :key="reserve.refId" class="reserve-row is-flex is-align-items-center" style="gap: 0.5rem;"
             dusk="reserve-row-a">
            <span>
                <b-icon icon="account-switch" size="is-small"/>
                <strong>{{ reserve.name }}</strong> for {{ reserve.forName }}
            </span>
            <b-tag v-if="!isPlaced(reserve.refId)" size="is-small" type="is-warning is-light">
                Står ikke længere på Hold {{ squadIndex + 1 }}
            </b-tag>
            <b-button class="ml-auto" size="is-small" title="Fjern reserve" icon-right="close"
                      @click="removeReserveA(squad.id, reserve.refId)"/>
        </div>
        <div class="is-flex is-align-items-flex-end is-flex-wrap-wrap mt-2" style="gap: 0.5rem;">
            <b-field label="Reserve" label-position="on-border" class="mb-0 reserve-input">
                <b-autocomplete
                    v-model="reserveSearch"
                    size="is-small"
                    field="name"
                    placeholder="Spiller på holdet..."
                    :data="filteredReserveCandidates"
                    :open-on-focus="true"
                    :keep-first="true"
                    dusk="reserve-a-player"
                    @select="option => reservePlayer = option">
                    <template #default="props">
                        {{ props.option.name }} <small class="has-text-grey">· {{ props.option.categories.join(', ') }}</small>
                    </template>
                    <template #empty>Ingen spillere på holdet matcher.</template>
                </b-autocomplete>
            </b-field>
            <span class="pb-1">for</span>
            <b-field label="Erstatter" label-position="on-border" class="mb-0 reserve-input">
                <b-autocomplete
                    v-model="forSearch"
                    size="is-small"
                    field="name"
                    placeholder="Spilleren, der meldte afbud..."
                    :data="forCandidates"
                    :loading="$apollo.queries.membersSearch.loading"
                    :open-on-focus="true"
                    :keep-first="true"
                    dusk="reserve-a-for"
                    @focus="forFocused = true"
                    @select="option => forPlayer = option">
                    <template #empty>Ingen spiller fundet.</template>
                </b-autocomplete>
            </b-field>
            <b-button size="is-small" type="is-primary" icon-left="plus" :disabled="!reservePlayer || !forPlayer"
                      dusk="reserve-a-add" @click="add">Tilføj</b-button>
        </div>
    </div>
</template>
<script>
import gql from 'graphql-tag'
import {addReserveA, removeReserveA, reservesA} from "./reserve-prototype.js";

export default {
    name: 'ReserveMarksA',
    inject: ['clubhouseId'],
    props: {
        squad: {type: Object, required: true},
        squadIndex: {type: Number, required: true}
    },
    data() {
        return {
            reserveSearch: '',
            reservePlayer: null,
            forSearch: '',
            forPlayer: null,
            forFocused: false,
            members: []
        }
    },
    computed: {
        placed() {
            const byRefId = new Map()
            this.squad.categories.forEach(category => category.players.forEach(player => {
                const entry = byRefId.get(player.refId) ?? {refId: player.refId, name: player.name, gender: player.gender, vintage: player.vintage, points: player.points, categories: []}
                entry.categories.push(category.name)
                byRefId.set(player.refId, entry)
            }))
            return [...byRefId.values()]
        },
        reserves() {
            return reservesA(this.squad.id)
        },
        // The reserve: a Member already placed on this Squad who isn't recorded as a reserve yet.
        filteredReserveCandidates() {
            const reserved = new Set(this.reserves.map(r => r.refId))
            const needle = this.reserveSearch.trim().toLowerCase()
            return this.placed.filter(p => !reserved.has(p.refId) && p.name.toLowerCase().includes(needle))
        },
        // Who they replaced: any Member of the Clubhouse who isn't playing on this Squad.
        forCandidates() {
            const placedRefIds = new Set(this.placed.map(p => p.refId))
            return this.members.filter(m => !placedRefIds.has(m.refId))
        }
    },
    methods: {
        removeReserveA,
        isPlaced(refId) {
            return this.placed.some(p => p.refId === refId)
        },
        add() {
            const {refId, name, gender, vintage, points} = this.reservePlayer
            addReserveA(this.squad.id, {refId, name, gender, vintage, points, forRefId: this.forPlayer.refId, forName: this.forPlayer.name})
            this.reservePlayer = this.forPlayer = null
            this.reserveSearch = this.forSearch = ''
        }
    },
    apollo: {
        membersSearch: {
            query: gql`query reservePrototypeMembers($clubhouse: Int!, $name: String) {
                membersSearch(clubhouse: $clubhouse, name: $name, orderBy: {column: NAME, order: ASC}) {
                    data { id name refId gender }
                }
            }`,
            variables() {
                return {clubhouse: this.clubhouseId, name: '%' + this.forSearch.trim() + '%'}
            },
            skip() {
                return !this.forFocused
            },
            debounce: 300,
            result({data}) {
                this.members = data?.membersSearch?.data ?? []
            }
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
.reserve-input {
    flex: 1 1 13rem;
}
.reserve-row {
    padding: 0.25rem 0;
    border-top: 1px solid #ede9fe;
}
</style>
