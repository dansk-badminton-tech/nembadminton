<template>
    <div dusk="team-table-section">
        <b-loading :is-full-page="false" v-model="loading" :can-cancel="true"></b-loading>
        <div v-for="(squad, index) in squads" :key="squad.id" class="column is-full" :dusk="'squad-' + index">
            <table class="table is-striped mt-5 is-fullwidth">
                <thead>
                <tr>
                    <th colspan="2">
                        <div class="is-flex is-justify-content-space-between is-align-items-start">
                            <div class="is-flex-grow-1">
                                <h2><strong>Hold {{ index + 1 }}</strong> {{ squad.name || 'intet navn' }}</h2>
                                <div class="tags squad-info-row mt-2" :dusk="'squad-info-' + index">
                                    <b-tooltip type="is-info" :label="squad.playingDatetime ? formatPlayingDatetimeLong(squad.playingDatetime) : 'Spillestart er ikke angivet. Klik for at udfylde.'">
                                        <b-tag
                                            rounded
                                            :class="squad.playingDatetime ? '' : 'squad-info-missing'"
                                            dusk="squad-info-datetime"
                                            @click.native="!squad.playingDatetime && openEditSquadModal(squad)">
                                            <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                                            <span v-if="squad.playingDatetime">{{ formatPlayingDatetime(squad.playingDatetime) }}</span>
                                            <span v-else><em>Spillestart ikke angivet</em></span>
                                        </b-tag>
                                    </b-tooltip>
                                    <b-tooltip type="is-info" :label="placeTooltip(squad)" multilined>
                                        <b-tag
                                            rounded
                                            :class="squad.playingPlace ? '' : 'squad-info-missing'"
                                            dusk="squad-info-place"
                                            @click.native="!squad.playingPlace && openEditSquadModal(squad)">
                                            <b-icon icon="map-marker" size="is-small" class="mr-1"></b-icon>
                                            <span v-if="squad.playingPlace">{{ squad.playingPlace }}</span>
                                            <span v-else><em>Spillested ikke angivet</em></span>
                                        </b-tag>
                                    </b-tooltip>
                                    <b-tooltip type="is-info" :label="squad.externalTeamFightID ? 'Åbn kampen på badmintonplayer.dk' : 'BadmintonPlayer kampnummer er ikke angivet. Klik for at udfylde.'">
                                        <a v-if="squad.externalTeamFightID"
                                           :href="badmintonPlayerUrl(squad.externalTeamFightID)"
                                           target="_blank"
                                           rel="noopener"
                                           class="tag is-rounded is-link is-light"
                                           dusk="squad-info-bp-link">
                                            <b-icon icon="open-in-new" size="is-small" class="mr-1"></b-icon>
                                            BP #{{ squad.externalTeamFightID }}
                                        </a>
                                        <b-tag
                                            v-else
                                            rounded
                                            class="squad-info-missing"
                                            dusk="squad-info-bp-link"
                                            @click.native="openEditSquadModal(squad)">
                                            <b-icon icon="open-in-new" size="is-small" class="mr-1"></b-icon>
                                            <em>BP kampnummer ikke angivet</em>
                                        </b-tag>
                                    </b-tooltip>
                                    <b-tooltip v-if="squad.version" type="is-info" label="Rangliste anvendt på dette hold">
                                        <b-tag rounded type="is-info" dusk="squad-info-version">
                                            <b-icon icon="format-list-numbered" size="is-small" class="mr-1"></b-icon>
                                            {{ timeToMonth(squad.version) }}
                                        </b-tag>
                                    </b-tooltip>
                                </div>
                            </div>
                            <b-dropdown position="is-bottom-left" :triggers="['hover']" aria-role="list">
                                <template v-slot:trigger>
                                    <b-button
                                        type="is-info"
                                        icon-right="cog" />
                                </template>
                                <b-dropdown-item aria-role="listitem" title="Udfyld holdnavn, kampnummer, spillestart, spillested, adresse, postnummer og by" icon-left="pencil" @click="openEditSquadModal(squad)">
                                    <b-icon icon="pencil"></b-icon>
                                    Rediger holdet
                                </b-dropdown-item>
                                <b-dropdown-item aria-role="listitem" :disabled="index === 0" @click="moveSquadOrderUp(squad)">
                                    <b-icon icon="arrow-up"></b-icon>
                                    Flyt hold up
                                </b-dropdown-item>
                                <b-dropdown-item aria-role="listitem" :disabled="index === squads.length-1" @click="moveSquadOrderDown(squad)">
                                    <b-icon icon="arrow-down"></b-icon>
                                    Flyt hold ned
                                </b-dropdown-item>
                                <b-dropdown-item class="is-danger" aria-role="listitem" type="is-danger" @click="confirmDelete(squad)">
                                    <b-icon icon="delete"></b-icon>
                                    Slet holdet
                                </b-dropdown-item>
                            </b-dropdown>
                        </div>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="category in squad.categories" :key="category.name">
                    <th>{{ category.name }}</th>
                    <td @drop="onDrop($event, squad, category)"
                        @dragover.prevent
                        @dragenter.prevent>
                        <div draggable="true"
                             v-for="player in category.players"
                             @dragstart="startDrag($event, squad, category, player)"
                             :key="player.id"
                             class="is-clearfix mt-1">
                            <input type="hidden" :data-player-id-input="player.id"/>
                            <b-tooltip
                                type="is-info"
                                class="is-pulled-left"
                                size="is-large"
                                :active="isPlayingToHigh(player) || isPlayingToHighInSquad(player, category.category) || !hasPointsInCategory(player, category.category)"
                                multilined>
                                <template v-slot:content>
                                    <span v-html="resolveLabel(player, category.category, squad.league)"></span>
                                </template>
                                <div class="is-flex is-justify-content-space-between is-align-items-center ml-2">
                                    <p class="handle" v-bind:class="highlight(player, category.category)">
                                        <b-icon
                                            v-show="player.gender === 'MEN'"
                                            icon="gender-male"
                                            size="is-small">
                                        </b-icon>
                                        <b-icon
                                            v-show="player.gender === 'WOMEN'"
                                            icon="gender-female"
                                            size="is-small">
                                        </b-icon>
                                        {{ player.name }}
                                        ({{findPositions(player, category.category) }})
                                    </p>
                                    <b-tag v-if="isYoungPlayer(player)">{{ageGroupLabel(player)}}</b-tag>
                                </div>
                            </b-tooltip>
                            <b-tooltip type="is-info" class="is-pulled-left" label="Point er redigeret manuelt">
                                <b-icon
                                    v-show="hasCorrectedPoints(player.points)"
                                    icon="information"
                                    type="is-info"
                                    size="is-small">
                                </b-icon>
                            </b-tooltip>
                            <div class="buttons is-pulled-right">
                                <b-button :disabled="loading" size="is-small" title="Rediger" icon-right="pen"
                                          @click="openEditPlayerModal(player)"></b-button>
                                <b-button :disabled="loading" size="is-small" title="Slet" icon-right="close"
                                          @click="deletePlayer(squad, category, player)"></b-button>
                            </div>
                        </div>
                        <PlayerSearch
                            v-if="category.players.length === 0"
                            @select-player="addPlayer(squad, category, $event)"
                            :squad="squad"
                            :version="resolveVersionToUse(squad)"
                            :category="category"/>
                        <PlayerSearch
                            class="mt-1"
                            v-if="isDouble(category) && category.players.length <= 1"
                            @select-player="addPlayer(squad, category, $event)"
                            :squad="squad"
                            :version="resolveVersionToUse(squad)" :category="category"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <b-modal
            v-model="showPlayerModal"
            :can-cancel="['x']"
            has-modal-card
            trap-focus
            @close="closeEditPlayerModal"
        >
            <template #default="props">
                <edit-player-modal :version="version" :player="modalPlayer" @close="props.close" />
            </template>
        </b-modal>
    </div>
</template>
<script>
import Draggable from "vuedraggable"
import {
    ageGroupLabel,
    compareDatesByYearMonthDay,
    findPositions as findPositionHelper,
    getCurrentSeason, hasPointsInCategory,
    highlight as simpleHighlight,
    isDoubleCategory,
    isPlayingToHighByBadmintonPlayerId,
    isYoungPlayer, parseDate,
    resolveToolTip
} from "../../helpers";
import PlayerSearch from "../common/PlayerSearch.vue";
import EditSquadModal from "./EditSquadModal.vue";
import {
    timeToMonth,
    formatPlayingDatetime,
    formatPlayingDatetimeLong,
    composedAddress,
    badmintonPlayerUrl
} from "./helper";
import EditPlayerModal from "@/views/team-fight/EditPlayerModal.vue";

export default {
    name: 'TeamTable',
    components: {EditPlayerModal, PlayerSearch, Draggable},
    props: {
        version: Date,
        confirmDelete: Function,
        moveSquadOrderUp: Function,
        moveSquadOrderDown: Function,
        playerMove: Function,
        deletePlayer: Function,
        addPlayer: Function,
        updateSquad: Function,
        playingToHigh: {
            type: Array,
            default: []
        },
        playingToHighInSquad: {
            type: Array,
            default: []
        },
        squads: {
            type: Array,
            default: []
        },
        teamsBaseValidations: {
            type: Array,
            default: []
        },
        loading: Boolean
    },
    computed: {
        getCurrentSeason
    },
    data(){
        return {
            modalPlayer: {},
            showPlayerModal: false
        }
    },
    methods: {
        resolveVersionToUse(squad){
            return squad.version ? new Date(squad.version) : new Date(this.version)
        },
        timeToMonth,
        formatPlayingDatetime,
        formatPlayingDatetimeLong,
        composedAddress,
        badmintonPlayerUrl(externalTeamFightID) {
            return badmintonPlayerUrl(externalTeamFightID, getCurrentSeason())
        },
        placeTooltip(squad) {
            if (!squad.playingPlace) {
                return 'Spillested er ikke angivet. Klik for at udfylde.'
            }
            const address = composedAddress(squad)
            return address || 'Ingen adresse angivet'
        },
        startDrag(evt, squad, category, player) {
            evt.dataTransfer.dropEffect = 'move'
            evt.dataTransfer.effectAllowed = 'move'
            evt.dataTransfer.setData('squad', JSON.stringify(squad))
            evt.dataTransfer.setData('category', JSON.stringify(category))
            evt.dataTransfer.setData('player', JSON.stringify(player))
        },
        onDrop(evt, targetSquad, targetCategory) {
            let sourceSquad = JSON.parse(evt.dataTransfer.getData('squad'))
            let sourceCategory = JSON.parse(evt.dataTransfer.getData('category'))
            let player = JSON.parse(evt.dataTransfer.getData('player'))
            if (sourceSquad.id !== targetSquad.id || targetCategory.id !== sourceCategory.id) {
                this.playerMove(evt, player, sourceSquad, sourceCategory, targetSquad, targetCategory)
            }
        },
        isYoungPlayer,
        ageGroupLabel,
        hasPointsInCategory,
        isDouble(category) {
            return isDoubleCategory(category)
        },
        resolveLabel(player, category, league) {
            return resolveToolTip(player, category, league, this.playingToHigh, this.playingToHighInSquad)
        },
        isPlayingToHigh(player) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHigh, player);
        },
        isPlayingToHighInSquad(player, category) {
            return isPlayingToHighByBadmintonPlayerId(this.playingToHighInSquad, player, category);
        },
        findPositions(player, category){
            let result = findPositionHelper(player, category)
            if(result === null){
                return category+': Ingen point'
            }
            return result
        },
        hasCorrectedPoints(points) {
            return points.some((point) => point.corrected_manually)
        },
        hasDifferentRankingList(points){
            return points.every((point) => {
                if(point.version === null){
                    return false
                }
                return !compareDatesByYearMonthDay(parseDate(point.version), this.version)}
            )
        },
        highlight: function (player, category) {
            return simpleHighlight(this.playingToHigh, this.playingToHighInSquad, player, category);
        },
        closeEditPlayerModal(){
            this.modalPlayer = {}
        },
        openEditPlayerModal(player) {
            this.modalPlayer = player
            this.showPlayerModal = true
        },
        openEditSquadModal(squad) {
            this.$buefy.modal.open(
                {
                    parent: this,
                    width: 1500,
                    props: {
                        squad: squad
                    },
                    events: {
                        close() {
                        }
                    },
                    canCancel: ["x"],
                    component: EditSquadModal,
                    hasModalCard: true,
                    trapFocus: true
                })
        }
    }
}
</script>
<style scoped>
.squad-info-row {
    flex-wrap: wrap;
    gap: 0.25rem 0.5rem;
    margin-bottom: 0;
}
.squad-info-row:empty {
    display: none;
}
.squad-info-row .tag {
    margin-bottom: 0;
}
.squad-info-row a.tag {
    text-decoration: none;
}
.squad-info-row .squad-info-missing {
    background-color: transparent;
    color: #7a7a7a;
    border: 1px dashed #b5b5b5;
    cursor: pointer;
}
.squad-info-row .squad-info-missing:hover {
    color: #363636;
    border-color: #7a7a7a;
    background-color: #fafafa;
}
.squad-info-row .squad-info-missing em {
    font-style: italic;
}
</style>
