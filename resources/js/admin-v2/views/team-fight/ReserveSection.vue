<template>
    <!-- Reserve Players (§43) on a Squad: "X for Y", where X comes from a lower Squad. Reserves are rare, so the section
         stays a small dashed box until the coach opens it. -->
    <div v-if="!reserves.length && !adding" class="reserve-collapsed" :dusk="'reserve-section-' + squadIndex">
        <a class="reserve-add-box" :dusk="'reserve-open-' + squadIndex" @click="adding = true">
            <b-icon icon="plus-circle-outline"/>
            <span>
                <strong>Tilføj reserve til Hold {{ squadIndex + 1 }}</strong><br>
                <span class="is-size-7 has-text-grey">Kun hvis en spiller melder afbud pga. skade, sygdom eller force majeure (§43)</span>
            </span>
        </a>
    </div>
    <div v-else class="reserve-box" :dusk="'reserve-section-' + squadIndex">
        <p v-if="reserves.length" class="mb-2"><strong>Reserver på Hold {{ squadIndex + 1 }}</strong></p>
        <div v-for="record in reserves" :key="record.id" class="reserve-row" :class="{'is-editing': editing?.id === record.id}"
             dusk="reserve-row">
            <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
                <b-icon icon="account-switch" class="has-text-primary"/>
                <div class="is-flex-grow-1">
                    <div><strong>{{ record.reserve.name }}</strong> for <strong>{{ record.replaced.name }}</strong></div>
                    <div v-if="origin(record)" class="is-size-7 has-text-grey">
                        Fra Hold {{ origin(record).index + 1 }} · {{ origin(record).categoryName }}
                    </div>
                    <div v-if="warnings(record).length" class="tags mt-1 mb-0">
                        <b-tag v-for="warning in warnings(record)" :key="warning" size="is-small" type="is-warning is-light"
                               dusk="reserve-row-warning">{{ warning }}</b-tag>
                    </div>
                </div>
                <b-button size="is-small" title="Ret reserve" icon-right="pencil" :disabled="loading"
                          dusk="reserve-edit" @click="edit(record)"/>
                <b-button size="is-small" title="Fjern reserve" icon-right="close" :disabled="loading"
                          dusk="reserve-remove" @click="$emit('remove', record)"/>
            </div>
        </div>
        <div v-if="!adding && !editing" class="pt-2">
            <a class="is-size-7" :dusk="'reserve-open-' + squadIndex" @click="adding = true">
                <b-icon icon="plus" size="is-small"/> Tilføj endnu en reserve
            </a>
        </div>
        <div v-else class="add-form" :class="{'has-rows': reserves.length}" dusk="reserve-form">
            <p v-if="editing" class="mb-3" dusk="reserve-editing">
                Ret <strong>{{ editing.reserve.name }}</strong> for <strong>{{ editing.replaced.name }}</strong>
            </p>
            <p v-else class="has-text-grey is-size-7 mb-3">
                Melder en spiller på Hold {{ squadIndex + 1 }} afbud pga. skade, sygdom eller force majeure, før
                holdopstillingen er udvekslet, må der sættes en reserve ind fra et lavere hold (§43). Vælg reserven og
                hvem vedkommende erstatter.
            </p>
            <b-field label="Reserve fra et lavere hold" class="mb-3">
                <b-autocomplete
                    v-model="reserveSearch"
                    field="player.name"
                    icon="magnify"
                    placeholder="Søg spiller på et lavere hold..."
                    :data="filteredCandidates"
                    :open-on-focus="true"
                    :keep-first="true"
                    dusk="reserve-player"
                    @select="option => reserveCandidate = option">
                    <template #default="props">
                        {{ props.option.player.name }}
                        <small class="has-text-grey">· Hold {{ props.option.fromIndex + 1 }} · {{ props.option.categoryName }}</small>
                    </template>
                    <template #empty>Ingen spillere på lavere hold matcher.</template>
                </b-autocomplete>
            </b-field>
            <b-field :label="`Erstatter (spiller på Hold ${squadIndex + 1})`" class="mb-3">
                <b-autocomplete
                    v-model="replacedSearch"
                    field="name"
                    icon="magnify"
                    :placeholder="`Søg spiller på Hold ${squadIndex + 1}...`"
                    :data="filteredReplaceable"
                    :open-on-focus="true"
                    :keep-first="true"
                    dusk="reserve-replaced"
                    @select="option => replaced = option">
                    <template #default="props">
                        {{ props.option.name }} <small class="has-text-grey">· {{ props.option.categories.join(', ') }}</small>
                    </template>
                    <template #empty>Ingen spillere på Hold {{ squadIndex + 1 }} matcher.</template>
                </b-autocomplete>
            </b-field>
            <div class="buttons is-right">
                <b-button dusk="reserve-cancel" @click="cancel">Annuller</b-button>
                <b-button v-if="editing" type="is-primary" icon-left="content-save"
                          :disabled="!reserveCandidate || !replaced || loading" dusk="reserve-save" @click="save">Gem</b-button>
                <b-button v-else type="is-primary" icon-left="plus" :disabled="!reserveCandidate || !replaced || loading"
                          dusk="reserve-add" @click="add">Tilføj reserve</b-button>
            </div>
        </div>
    </div>
</template>
<script>
import {replaceableMembers, reserveCandidates, reserveOrigin, reserveWarnings} from "./reserves.js";

export default {
    name: 'ReserveSection',
    props: {
        squads: {type: Array, required: true},
        squadIndex: {type: Number, required: true},
        loading: Boolean
    },
    emits: ['add', 'update', 'remove'],
    data() {
        return {
            reserveSearch: '',
            reserveCandidate: null,
            replacedSearch: '',
            replaced: null,
            adding: false,
            // The record being changed, or null
            editing: null
        }
    },
    computed: {
        squad() {
            return this.squads[this.squadIndex]
        },
        reserves() {
            return this.squad.reserves ?? []
        },
        filteredCandidates() {
            const needle = this.reserveSearch.trim().toLowerCase()
            return reserveCandidates(this.squads, this.squadIndex, this.editing).filter(c => c.player.name.toLowerCase().includes(needle))
        },
        filteredReplaceable() {
            const needle = this.replacedSearch.trim().toLowerCase()
            return replaceableMembers(this.squad, this.editing).filter(member => member.name.toLowerCase().includes(needle))
        }
    },
    methods: {
        origin(record) {
            return reserveOrigin(this.squads, record)
        },
        warnings(record) {
            return reserveWarnings(this.squads, this.squadIndex, record)
        },
        add() {
            this.$emit('add', {
                squad: this.squad,
                reserveRefId: this.reserveCandidate.player.refId,
                replacedRefId: this.replaced.refId
            })
            this.cancel()
        },
        // Opens the form on the record, with its reserve and replaced Member chosen when they still stand where §43
        // allows; a stale side is left empty for the coach to pick again.
        edit(record) {
            this.cancel()
            this.editing = record
            this.reserveCandidate = reserveCandidates(this.squads, this.squadIndex, record)
                .find(candidate => candidate.player.refId === record.reserve.refId) ?? null
            this.replaced = replaceableMembers(this.squad, record).find(member => member.refId === record.replaced.refId) ?? null
            this.reserveSearch = this.reserveCandidate?.player.name ?? ''
            this.replacedSearch = this.replaced?.name ?? ''
        },
        save() {
            this.$emit('update', {
                record: this.editing,
                reserveRefId: this.reserveCandidate.player.refId,
                replacedRefId: this.replaced.refId
            })
            this.cancel()
        },
        cancel() {
            this.reserveCandidate = this.replaced = null
            this.reserveSearch = this.replacedSearch = ''
            this.adding = false
            this.editing = null
        }
    }
}
</script>
<style scoped>
.reserve-collapsed {
    margin-top: -0.75rem;
    margin-bottom: 1rem;
}
.reserve-add-box {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: 2px dashed #dbdbdb;
    border-radius: 6px;
    color: #4a4a4a;
}
.reserve-add-box:hover {
    border-color: #b5b5b5;
    background: #fafafa;
}
.reserve-box {
    border: 1px solid #dbdbdb;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    margin-top: -0.75rem;
    margin-bottom: 1rem;
}
.reserve-row {
    padding: 0.5rem 0;
    border-top: 1px solid #ededed;
}
.reserve-row.is-editing {
    background: #f5f5f5;
}
.add-form.has-rows {
    border-top: 1px solid #ededed;
    padding-top: 0.75rem;
    margin-top: 0.25rem;
}
</style>
