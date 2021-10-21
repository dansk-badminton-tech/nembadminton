<template>
    <fragment>
        <section>
            <b-field label="Filter" grouped>
                <b-input @input="search" placeholder="Søg på navn"></b-input>
                <b-select v-model="gender">
                    <option :value="null">Begge</option>
                    <option value="M">Mand</option>
                    <option value="K">Kvinde</option>
                </b-select>
                <b-checkbox-button v-model="hideHidden">
                    <b-icon size="is-small" v-if="!hideHidden" icon="user-alt"></b-icon>
                    <span v-if="!hideHidden">Skjul afbud</span>
                    <b-icon size="is-small" v-if="hideHidden" icon="user-slash"></b-icon>
                    <span v-if="hideHidden">Vis afbud</span>
                </b-checkbox-button>
            </b-field>
        </section>
        <b-table
            :data="memberSearchPointsFiltered"
            :paginated="true"
            :backend-pagination="true"
            :loading="$apollo.queries.memberSearchPoints.loading"
            :total="memberSearchPoints.paginatorInfo.total"
            :per-page="perPage"
            @page-change="onPageChange"
            :pagination-rounded="true">
            <b-table-column field="points" label="#" v-slot="props">
                {{ (props.index + 1) + perPage * (currentPage - 1) }}
            </b-table-column>
            <b-table-column field="points" label="Niveau" v-slot="props">
                {{ findLevel(props.row, null) }}
            </b-table-column>
            <b-table-column field="name" label="Navn" v-slot="props">
                {{ props.row.name }}
            </b-table-column>
            <b-table-column field="name" v-slot="props">
                <div class="buttons">
                    <b-button size="is-small" type="is-danger" v-if="hasCancellation(props.row)"
                              title="Annuller afbud" icon-right="user-alt" @click="deleteCancellation(props.row)"></b-button>
                    <b-button size="is-small" v-if="!hasCancellation(props.row)" title="Afbud"
                              icon-right="user-slash" @click="makeCancellation(props.row)"></b-button>
                    <b-button size="is-small" title="Tilføj på hold (Næste ledig plads)" icon-right="plus"
                              @click="addPlayer(props.row)"></b-button>
                </div>
            </b-table-column>
        </b-table>
    </fragment>
</template>

<script>

import gql from 'graphql-tag'
import {debounce, findLevel} from "../helpers";


export default {
    name: 'SearchPlayers',
    props: {
        clubId: String,
        addPlayer: Function,
        version: Date
    },
    computed: {
        memberSearchPointsFiltered() {
            return this.memberSearchPoints.data
        }
    },
    data() {
        return {
            memberSearchPoints: {
                data: [],
                paginatorInfo: {
                    total: 0
                }
            },
            hideHidden: true,
            hiddenPlayers: [],
            gender: null,
            perPage: 30,
            currentPage: 1,
            total: 0,
            searchName: '',
            cancellations: []
        }
    },
    methods: {
        makeCancellation(player) {
            this.$apollo
                .mutate({
                    mutation: gql`
                        mutation createCancellation($refId : String!, $teamId: ID!){
                            createCancellation(refId: $refId, teamId: $teamId){
                                id
                                refId
                                teamId
                            }
                        }
                    `,
                    variables: {
                        refId: player.refId,
                        teamId: this.clubId
                    }
                })
                .then(() => {
                    this.$apollo.queries.cancellations.refresh()
                    this.$apollo.queries.memberSearchPoints.refresh()
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                        duration: 4000,
                        type: 'is-danger',
                        message: `Kunne ikke register afbud :(`
                    })
                })
        },
        deleteCancellation(player) {
            const cancellation = this.cancellations.find(function(cancellation){
                return cancellation.refId === player.refId
            })

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
                    this.$apollo.queries.cancellations.refresh()
                    this.$apollo.queries.memberSearchPoints.refresh()
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                        duration: 4000,
                        type: 'is-danger',
                        message: `Kunne ikke annuller afbud :(`
                    })
                })
            this.hiddenPlayers.splice(this.hiddenPlayers.indexOf(player), 1)
        },
        hasCancellation(player){
            return this.cancellations.some(function(cancellation){
                return cancellation.refId === player.refId
            })
        },
        search: debounce(function (name) {
            this.searchName = name;
        }, 300),
        findLevel,
        onPageChange(page) {
            this.currentPage = page
        },
    },
    apollo: {
        cancellations: {
            query: gql`
                query cancellations($teamId: String!){
                    cancellations(teamId: $teamId){
                        id
                        refId
                    }
                }
            `,
            fetchPolicy: "network-only",
            skip(){
                return !this.clubId;
            },
            variables(){
                return {
                    teamId: this.clubId
                }
            }
        },
        memberSearchPoints: {
            query: gql`query MembersSearch($hasClubs: MemberSearchPointsHasClubsWhereConditions, $version: String, $page: Int, $first: Int, $gender: String, $name: String, $includeCancellations: Boolean!){
                      memberSearchPoints(hasClubs: $hasClubs, version: $version, gender: $gender, page: $page, name: $name, first: $first, includeCancellations: $includeCancellations) {
                        data {
                          id
                          name
                          gender
                          refId
                          points(version: $version){
                            points
                            position
                            category
                            vintage
                          }
                        }
                        paginatorInfo {
                          count
                          total
                        }
                      }
                    }
                `,
            fetchPolicy: "network-only",
            variables() {
                let params = {
                    page: this.currentPage,
                    first: this.perPage,
                    includeCancellations: !this.hideHidden
                }
                if (this.searchName.trim() !== '') {
                    params.name = '%' + this.searchName + '%'
                }
                if (!!this.gender) {
                    params.gender = this.gender
                }
                if (!!this.version) {
                    params.version = this.version.getFullYear() + "-" + (this.version.getMonth() + 1) + "-" + this.version.getDate()
                }
                if (this.clubId) {
                    params.hasClubs = {
                        column: "ID",
                        operator: "EQ",
                        value: this.clubId
                    }
                }
                return params
            }
        }
    }

}
</script>
