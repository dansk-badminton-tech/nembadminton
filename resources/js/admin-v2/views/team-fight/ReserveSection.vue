<template>
    <!-- Reserve Players (§43) on a Squad: "X for Y", where X is an Available Member or comes from a lower Squad. Reserves
         are rare, so the section stays a small dashed box until the coach opens it. -->
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
        <div v-for="record in reserves" :key="record.id" class="reserve-row" dusk="reserve-row">
            <div class="is-flex is-align-items-center" style="gap: 0.75rem;">
                <b-icon icon="account-switch" class="has-text-primary"/>
                <div class="is-flex-grow-1">
                    <div><strong>{{ record.reserve.name }}</strong> for <strong>{{ record.replaced.name }}</strong></div>
                    <div v-if="origin(record)" class="is-size-7 has-text-grey">
                        Fra Hold {{ origin(record).index + 1 }} · {{ origin(record).categoryName }}
                    </div>
                    <div v-else class="is-size-7 has-text-grey">Ledig spiller</div>
                    <div v-if="warnings(record).length" class="tags mt-1 mb-0">
                        <b-tag v-for="warning in warnings(record)" :key="warning" size="is-small" type="is-warning is-light"
                               dusk="reserve-row-warning">{{ warning }}</b-tag>
                    </div>
                </div>
                <b-button size="is-small" title="Fjern reserve" icon-right="close" :disabled="loading"
                          dusk="reserve-remove" @click="remove(record)"/>
            </div>
        </div>
        <div v-if="!adding" class="pt-2">
            <a class="is-size-7" :dusk="'reserve-open-' + squadIndex" @click="adding = true">
                <b-icon icon="plus" size="is-small"/> Tilføj endnu en reserve
            </a>
        </div>
        <div v-else class="add-form" :class="{'has-rows': reserves.length}" dusk="reserve-form">
            <p class="has-text-grey is-size-7 mb-3">
                Melder en spiller på Hold {{ squadIndex + 1 }} afbud pga. skade, sygdom eller force majeure, før
                holdopstillingen er udvekslet, må der sættes en reserve ind (§43). Reserven kan være en ledig spiller
                eller en spiller fra et lavere hold. Vælg reserven og hvem vedkommende erstatter. Reserven sættes ind på
                spillerens pladser; ret derefter rækkefølgen, hvis pointene kræver det.
            </p>
            <b-field label="Reserve" class="mb-3">
                <b-autocomplete
                    v-model="reserveSearch"
                    field="player.name"
                    icon="magnify"
                    placeholder="Søg ledig spiller eller spiller på et lavere hold..."
                    :data="filteredCandidates"
                    :loading="$apollo.queries.availableMembers.loading"
                    :open-on-focus="true"
                    :keep-first="true"
                    dusk="reserve-player"
                    @select="option => reserveCandidate = option">
                    <template #default="props">
                        {{ props.option.player.name }}
                        <small v-if="props.option.fromIndex === null" class="has-text-grey">· Ledig</small>
                        <small v-else class="has-text-grey">· Hold {{ props.option.fromIndex + 1 }} · {{ props.option.categoryName }}</small>
                    </template>
                    <template #empty>Ingen spillere matcher.</template>
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
                <b-button type="is-primary" icon-left="plus" :disabled="!reserveCandidate || !replaced || loading"
                          dusk="reserve-add" @click="add">Tilføj reserve</b-button>
            </div>
        </div>
    </div>
</template>
<script>
import gql from "graphql-tag";
import {debounce} from "../../helpers";
import {replaceableMembers, reserveCandidates, reserveOrigin, reserveWarnings} from "./reserves.js";

export default {
    name: 'ReserveSection',
    inject: ['clubhouseId', 'lineupSession'],
    props: {
        squads: {type: Array, required: true},
        squadIndex: {type: Number, required: true},
        teamRoundId: String,
        scenarioId: [String, Number],
        loading: Boolean
    },
    data() {
        return {
            reserveSearch: '',
            reserveCandidate: null,
            replacedSearch: '',
            replaced: null,
            adding: false,
            // The name the Available Member search last ran for, set a moment after typing stops
            availableSearch: '',
            availableMembers: []
        }
    },
    apollo: {
        // Available Members: the Clubhouse's Members who aren't placed in this lineup, like the player list.
        availableMembers: {
            query: gql`query reserveAvailableMembers($clubhouse: Int!, $name: String, $notOnSquad: String, $notOnScenario: ID){
                membersSearch(clubhouse: $clubhouse, name: $name, inactive: false, notOnSquad: $notOnSquad, notOnScenario: $notOnScenario, orderBy: {column: NAME, order: ASC}) {
                    data { refId name gender }
                }
            }`,
            variables() {
                return {
                    clubhouse: parseInt(this.clubhouseId),
                    name: '%' + this.availableSearch + '%',
                    notOnSquad: this.scenarioId == null ? this.teamRoundId : null,
                    notOnScenario: this.scenarioId
                }
            },
            update: data => data.membersSearch.data,
            fetchPolicy: 'network-only',
            skip() {
                return !this.adding
            }
        }
    },
    watch: {
        reserveSearch: debounce(function (name) {
            this.availableSearch = name.trim()
        }, 300)
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
            return reserveCandidates(this.squads, this.squadIndex, this.availableMembers).filter(c => c.player.name.toLowerCase().includes(needle))
        },
        filteredReplaceable() {
            const needle = this.replacedSearch.trim().toLowerCase()
            return replaceableMembers(this.squad).filter(member => member.name.toLowerCase().includes(needle))
        }
    },
    methods: {
        origin(record) {
            return reserveOrigin(this.squads, this.squadIndex, record)
        },
        warnings(record) {
            return reserveWarnings(this.squads, this.squadIndex, record)
        },
        // A failure is shown by the editor through the session's onError.
        add() {
            this.lineupSession.addReserve({
                squadId: this.squad.id,
                reserveRefId: this.reserveCandidate.player.refId,
                replacedRefId: this.replaced.refId
            }).catch(() => {})
            this.cancel()
        },
        remove(record) {
            this.lineupSession.removeReserve(record).catch(() => {})
        },
        cancel() {
            this.reserveCandidate = this.replaced = null
            this.reserveSearch = this.replacedSearch = this.availableSearch = ''
            this.adding = false
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
.add-form.has-rows {
    border-top: 1px solid #ededed;
    padding-top: 0.75rem;
    margin-top: 0.25rem;
}
</style>
