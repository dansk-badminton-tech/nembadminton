<template>
    <div class="sticky">
        <b-field grouped group-multiline>
            <b-input @input="search" placeholder="Søg på navn"></b-input>
            <b-select v-model="rankingList">
                <option v-for="(label, value) in rankings" :value="value">{{ label }}</option>
            </b-select>
            <b-checkbox-button type="is-info" v-model="showCancellation">
                <b-icon size="is-small" v-if="showCancellation" icon="account"></b-icon>
                <span v-if="showCancellation">Skjul afbud</span>
                <b-icon size="is-small" v-if="!showCancellation" icon="account-off"></b-icon>
                <span v-if="!showCancellation">Vis afbud</span>
            </b-checkbox-button>
            <b-button icon-left="plus" @click="openAddMemberModal">
                Tilføj spiller
            </b-button>
            <b-switch v-show="showCancellation" v-model="showPlayable">Vis permanent afbud</b-switch>
        </b-field>
        <p class="mt-4" v-html="resolveHelperTextForCancellation"></p>
        <b-table
            class="mt-5"
            :data="membersList"
            :paginated="true"
            :backend-pagination="true"
            :loading="apolloLoading || loading"
            :total="memberPageInfo.paginatorInfo.total"
            :per-page="perPage"
            @page-change="onPageChange"
            :pagination-rounded="true"
            :detailed="showCancellation"
            detail-key="id"
            @click="openDetailedRow"
            :opened-detailed="openedDetailed"
            :hoverable="true"
        >
            <b-table-column field="points" label="#" v-slot="props">
                {{ (props.index + 1) + perPage * (currentPage - 1) }}
            </b-table-column>
            <b-table-column :visible="!showCancellation" field="points" label="Points" v-slot="props">
                {{ findLevel(props.row, convertRankingToCategory(rankingList)) }}
            </b-table-column>
            <b-table-column field="vintage" label="" v-slot="props">
                <p>{{ props.row.vintage }}</p>
            </b-table-column>
            <b-table-column field="name" label="Navn" v-slot="props">
                <p>{{ props.row.name }}</p>
            </b-table-column>
            <b-table-column field="name" v-slot="props">
                <div class="buttons" v-if="showCancellation">
                    <b-button size="is-small" v-show="!hasPermanentCancellation(props.row)" title="Lav afbud permanent (Alle holdrunder)"
                              icon-right="account-injury" @click.stop="makeCancellationPermanent(props.row)"></b-button>
                    <b-button size="is-small" type="is-danger" v-show="hasPermanentCancellation(props.row)" title="Annuller permanent afbud"
                              icon-right="account-injury" @click.stop="removePermanentCancellation(props.row)"></b-button>
                    <b-button size="is-small" title="Tilføj på hold (Næste ledig plads)" icon-right="plus"
                              @click.stop="addPlayerCustom(props.row)"></b-button>
                </div>
                <div class="buttons" v-if="!showCancellation">
                    <b-button size="is-small" type="is-danger" v-show="props.row.ownerId" icon-right="delete" @click="deleteMember(props.row)"></b-button>
                    <b-button size="is-small" title="Afbud (Denne holdrunde)"
                              icon-right="account-off" @click="makeCancellation(props.row)"></b-button>
                    <b-button size="is-small" title="Tilføj på hold (Næste ledig plads)" icon-right="plus"
                              @click="addPlayerCustom(props.row)"></b-button>
                </div>
            </b-table-column>
            <template v-slot:empty="props">
                <div class="has-text-centered" v-if="showCancellation">Søgte på afbud for {{ rankingListTranslate }} og {{ formatGameDate }} - fandt 0.</div>
            </template>
            <template v-slot:detail="props">
                <tr>
                    <td>Oprettet</td>
                    <td></td>
                    <td></td>
                    <td>Afbuds datoer</td>
                    <td>Oprettet af</td>
                    <td>Funktioner</td>
                </tr>
                <tr v-if="props.row.playable" v-for="cancellation in props.row.cancellations" :key="cancellation.id">
                    <td colspan="3">{{ cancellation.createdAt }}</td>
                    <td>{{ cancellation.dates.map(d => d.date).join(", ") }}</td>
                    <td>{{ resolveCancellationCreatedBy(cancellation) }}</td>
                    <td>
                        <b-tooltip type="is-info" position="is-top" :active="!!cancellation?.cancellationCollector?.id" label="Du kan ikke slette afbud fra spiller.">
                            <b-button
                                :disabled="cancellation?.cancellationCollector?.id"
                                size="is-small" type="is-danger"
                                title="Annuller afbud (Denne holdrunde)" icon-right="delete"
                                @click="deleteCancellation(cancellation)"></b-button>
                        </b-tooltip>
                    </td>
                </tr>
                <tr v-if="!props.row.playable">
                    <td colspan="6">Spilleren er markeret som permanent afbud</td>
                </tr>
            </template>
        </b-table>
    </div>
</template>

<style>
@media only screen and (min-width: 769px) {
    .sticky {
        position: sticky;
        top: 55px;
        align-self: start;
        max-height: 100vh;
    }
}
</style>

<script>

import gql from 'graphql-tag'
import {convertRankingToCategory, debounce, findLevel} from "../../helpers";
import AddMemberModal from "./AddMemberModal.vue";
import MemberSearchPoints from "./memberSearchPoints.gql"
import MemberSearchCancellation from "./memberSearchCancellation.gql"
import ME from "../../../queries/me.gql";

export default {
    name: 'PlayersListSearch',
    props: {
        clubhouseId: Number,
        clubId: String,
        teamId: String,
        addPlayer: Function,
        version: Date,
        loading: Boolean,
        gameDate: Date
    },
    computed: {
        resolveHelperTextForCancellation() {
            if (this.showCancellation) {
                return 'Inkluderer afbud fra afbudslink for datoen <strong>' + this.gameDate.toISOString().substring(0, 10) + '</strong>'
            }
        },
        membersList() {
            if (this.showCancellation) {
                return this.memberSearchCancellation?.data
            }
            return this.memberSearch?.data
        },
        memberPageInfo() {
            if (this.showCancellation) {
                return this.memberSearchCancellation
            }
            return this.memberSearch
        },
        apolloLoading() {
            if (this.showCancellation) {
                return this.$apollo.queries.memberSearchCancellation.loading
            }
            return this.$apollo.queries.memberSearch.loading
        },
        rankingListTranslate() {
            return this.rankings[this.rankingList] || this.rankingList
        },
        formatGameDate() {
            return this.gameDate.toISOString().substring(0, 10)
        }
    },
    mounted() {
        this.$root.$on('player-added-to-category', (player) => {
            this.$apollo.queries.memberSearch.refresh()
            this.$apollo.queries.memberSearchCancellation.refresh()
        })
        this.$root.$on('player-deleted-from-category', (player) => {
            this.$apollo.queries.memberSearch.refresh()
            this.$apollo.queries.memberSearchCancellation.refresh()
        })
    },
    data() {
        return {
            memberSearch: {
                data: [],
                paginatorInfo: {
                    total: 0
                }
            },
            me: {
                id: 0
            },
            openedDetailed: [],
            showCancellation: false,
            rankingList: 'WOMEN_SINGLE',
            perPage: 15,
            currentPage: 1,
            total: 0,
            searchName: '',
            rankings: {
                WOMEN_SINGLE: 'Dame Single',
                WOMENS_DOUBLE: 'Dame Double',
                WOMEN_MIX: 'Dame Mix',
                MEN_SINGLE: 'Herre Single',
                MENS_DOUBLE: 'Herre Double',
                MEN_MIX: 'Herre Mix'
            },
            showPlayable: false
        }
    },
    methods: {
        refreshMembers() {
            this.$apollo.queries.memberSearch.refresh()
            this.$apollo.queries.memberSearchCancellation.refresh()
        },
        openDetailedRow(obj) {
            if (this.showCancellation) {
                const index = this.openedDetailed.indexOf(obj.id);
                if (index !== -1) {
                    // If obj.id exists in the array, remove it
                    this.openedDetailed.splice(index, 1);
                } else {
                    // If obj.id does not exist in the array, add it
                    this.openedDetailed.push(obj.id);
                }
            }
        },
        deleteMember(player) {
            this.$apollo
                .mutate({
                            mutation: gql`
                        mutation deleteMember($id: ID!){
                            deleteMember(id: $id){
                                id
                            }
                        }
                    `,
                            variables: {
                                id: player.id
                            },
                        })
                .then(() => {
                    this.refreshMembers()
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-success',
                                                  message: `Spiller slettet`
                                              })
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-danger',
                                                  message: `Kunne ikke slette spiller :(`
                                              })
                })
        },
        addPlayerCustom(player) {
            this.addPlayer(player).then(() => {
                this.$apollo.queries.memberSearch.refresh()
            })
        },
        convertRankingToCategory,
        makeCancellationPermanent(player) {
            this.$apollo
                .mutate({
                            mutation: gql`
                        mutation updateMember($input: CreateMemberInput!){
                            updateMember(input: $input){
                                id
                                playable
                            }
                        }
                    `,
                            variables: {
                                input: {
                                    id: player.id,
                                    playable: false
                                }
                            }
                        })
                .then(() => {
                    this.refreshMembers()
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-success',
                                                  message: `Permanent afbud registret`
                                              })
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-danger',
                                                  message: `Kunne ikke lave permanent afbud :(`
                                              })
                })
        },
        removePermanentCancellation(player) {
            this.$apollo
                .mutate({
                            mutation: gql`
                        mutation updateMember($input: CreateMemberInput!){
                            updateMember(input: $input){
                                id
                                playable
                            }
                        }
                    `,
                            variables: {
                                input: {
                                    id: player.id,
                                    playable: true
                                }
                            }
                        })
                .then(() => {
                    this.refreshMembers()
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-success',
                                                  message: `Permanent afbud slettet`
                                              })
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-danger',
                                                  message: `Kunne ikke annuller permanent afbud :(`
                                              })
                })
        },
        makeCancellation(player) {
            this.$apollo
                .mutate(
                    {
                        mutation: gql`
                                mutation createCancellation($input: CancellationInput!){
                                        createCancellation(input: $input){
                                            id
                                            refId
                                            teamId
                                            dates{
                                             date
                                             updatedAt
                                             createdAt
                                            }
                                        }
                                    }
                                `,
                        variables: {
                            input: {
                                refId: player.refId,
                                teamId: this.teamId,
                                dates: {
                                    create: [{date: this.gameDate.toISOString().substring(0, 10)}]
                                }
                            }
                        }
                    }
                )
                .then(() => {
                    this.refreshMembers()
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-success',
                                                  message: `Afbud registret`
                                              })
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-danger',
                                                  message: `Kunne ikke register afbud :(`
                                              })
                })
        },
        deleteCancellation(cancellation) {
            this.$apollo
                .mutate({
                            mutation: gql`
                                mutation deleteCancellation($id : ID!){
                                    deleteCancellation(id: $id){
                                        id
                                    }
                                }
                            `,
                            variables: {
                                id: cancellation.id
                            }
                        })
                .then(() => {
                    this.refreshMembers()
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-success',
                                                  message: `Afbud slettet`
                                              })
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-danger',
                                                  message: `Kunne ikke annuller afbud :(`
                                              })
                })
        },
        resolveCancellationCreatedBy(cancellation) {
            if (cancellation.teamId) {
                return "Dig"
            }
            if (cancellation.cancellationCollector) {
                return "Afbudslink"
            }
        },
        hasPermanentCancellation(player) {
            return !player.playable;
        },
        search: debounce(function (name) {
            this.searchName = name;
        }, 300),
        findLevel,
        onPageChange(page) {
            this.currentPage = page
        },
        openAddMemberModal() {
            this.$buefy.modal.open({
                                       parent: this,
                                       props: {
                                           version: this.version
                                       },
                                       events: {
                                           close() {
                                           }
                                       },
                                       canCancel: ["x"],
                                       component: AddMemberModal,
                                       hasModalCard: true,
                                       trapFocus: true
                                   })
        }
    },
    apollo: {
        me: ME,
        memberSearchCancellation: {
            query: MemberSearchCancellation,
            update: data => data.membersSearch,
            fetchPolicy: "network-only",
            variables() {

                let whereCondition

                let baseCondition = {
                    HAS: {
                        relation: 'cancellations',
                        condition: {
                            OR: [
                                {
                                    column: 'TEAM_ID',
                                    value: this.teamId
                                },
                                {
                                    AND: [
                                        {
                                            HAS: {
                                                relation: 'cancellationCollector',
                                                condition: {
                                                    column: 'USER_ID',
                                                    value: this.me?.id
                                                }
                                            }
                                        },
                                        {
                                            HAS: {
                                                relation: 'dates',
                                                condition: {
                                                    column: 'DATE',
                                                    operator: 'BETWEEN',
                                                    value: [this.gameDate.toISOString().slice(0, 10), this.gameDate.toISOString().slice(0, 10)]
                                                }
                                            }
                                        }
                                    ],
                                }
                            ]
                        }
                    }
                }

                if (this.showPlayable) {
                    whereCondition = {
                        OR: [
                            {
                                column: 'PLAYABLE',
                                value: false
                            },
                            baseCondition
                        ]
                    }
                } else {
                    whereCondition = {
                        AND: [
                            {
                                column: 'PLAYABLE',
                                value: true
                            },
                            baseCondition
                        ]
                    }
                }

                return {
                    clubhouse: this.clubhouseId,
                    page: this.currentPage,
                    first: this.perPage,
                    notOnSquad: this.teamId,
                    cancellationWhere: {
                        OR: [{
                            column: 'TEAM_ID',
                            value: this.teamId
                        }, {
                            AND: [
                                {
                                    HAS: {
                                        relation: 'cancellationCollector',
                                        condition: {
                                            column: 'USER_ID',
                                            value: this.me.id
                                        }
                                    }
                                },
                                {
                                    HAS: {
                                        relation: 'dates',
                                        condition: {
                                            column: 'DATE',
                                            operator: 'BETWEEN',
                                            value: [this.gameDate.toISOString().slice(0, 10), this.gameDate.toISOString().slice(0, 10)]
                                        }
                                    }
                                }
                            ],
                        }]
                    },
                    whereCancellations: whereCondition
                }
            }
        },
        memberSearch: {
            query: MemberSearchPoints,
            update: data => data.memberSearchPoints,
            fetchPolicy: "network-only",
            variables() {
                let params = {
                    page: this.currentPage,
                    first: this.perPage,
                    playable: true
                }
                if (this.searchName.trim() !== '') {
                    params.name = '%' + this.searchName + '%'
                }
                if (!!this.rankingList) {
                    params.rankingList = this.rankingList
                }
                if (!!this.version) {
                    params.version = this.version.toISOString().slice(0, 10)
                }

                params.cancellationWhere = {
                    OR: [{
                        column: 'TEAM_ID',
                        value: this.teamId
                    }, {
                        AND: [
                            {
                                HAS: {
                                    relation: 'cancellationCollector',
                                    condition: {
                                        column: 'USER_ID',
                                        value: this.me.id
                                    }
                                }
                            },
                            {
                                HAS: {
                                    relation: 'dates',
                                    condition: {
                                        column: 'DATE',
                                        operator: 'BETWEEN',
                                        value: [this.gameDate.toISOString().slice(0, 10), this.gameDate.toISOString().slice(0, 10)]
                                    }
                                }
                            }
                        ],
                    }]
                }
                params.whereCancellations = {
                    HAS: {
                        relation: 'cancellations',
                        operator: 'LT',
                        amount: 1,
                        condition: {
                            OR: [{
                                column: 'TEAM_ID',
                                value: this.teamId
                            }, {
                                AND: [
                                    {
                                        HAS: {
                                            relation: 'dates',
                                            condition: {
                                                column: 'DATE',
                                                operator: 'BETWEEN',
                                                value: [this.gameDate.toISOString().slice(0, 10), this.gameDate.toISOString().slice(0, 10)]
                                            }
                                        }
                                    },
                                    {
                                        HAS: {
                                            relation: 'cancellationCollector',
                                            condition: {
                                                column: 'USER_ID',
                                                value: this.me.id
                                            }
                                        }
                                    }
                                ],
                            }
                            ]
                        }
                    }
                }

                params.teamId = this.teamId;
                params.clubhouse = this.clubhouseId;

                return params
            }
        }
    }

}
</script>
