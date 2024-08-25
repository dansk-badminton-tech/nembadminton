<template>
  <div class="sticky">
    <b-field grouped group-multiline>
      <b-input @input="search" placeholder="Søg på navn"></b-input>
      <b-select v-model="rankingList">
        <option value="WOMEN_SINGLE">Dame Single</option>
        <option value="WOMENS_DOUBLE">Dame Double</option>
        <option value="WOMEN_MIX">Dame Mix</option>
        <option value="MEN_SINGLE">Herre Single</option>
        <option value="MENS_DOUBLE">Herre Double</option>
        <option value="MEN_MIX">Herre Mix</option>
      </b-select>
      <b-checkbox-button v-model="hideCancellation">
        <b-icon size="is-small" v-if="!hideCancellation" icon="account"></b-icon>
        <span v-if="!hideCancellation">Skjul afbud</span>
        <b-icon size="is-small" v-if="hideCancellation" icon="account-off"></b-icon>
        <span v-if="hideCancellation">Vis afbud</span>
      </b-checkbox-button>
      <b-button icon-left="plus" @click="openAddMemberModal">
        Tilføj spiller
      </b-button>
    </b-field>
    <b-table
        class="mt-5"
        :data="memberSearchPointsFiltered"
        :paginated="true"
        :backend-pagination="true"
        :loading="$apollo.queries.memberSearch.loading || loading"
        :total="memberSearch.paginatorInfo.total"
        :per-page="perPage"
        @page-change="onPageChange"
        :pagination-rounded="true">
      <b-table-column field="points" label="#" v-slot="props">
        {{ (props.index + 1) + perPage * (currentPage - 1) }}
      </b-table-column>
      <b-table-column field="points" label="Points" v-slot="props">
        {{ findLevel(props.row, convertRankingToCategory(rankingList)) }}
      </b-table-column>
      <b-table-column field="vintage" label="" v-slot="props">
        <p>{{ props.row.vintage }}</p>
      </b-table-column>
      <b-table-column field="name" label="Navn" v-slot="props">
        <p>{{ props.row.name }}</p>
      </b-table-column>
      <b-table-column field="name" v-slot="props">
        <div class="buttons">
          <b-button size="is-small" type="is-danger" v-show="props.row.ownerId && hideCancellation" icon-right="delete" @click="deleteMember(props.row)"></b-button>
          <b-button size="is-small" type="is-danger" v-show="!hideCancellation && !hasPermanentCancellation(props.row)"
                    title="Annuller afbud (Denne holdrunde)" icon-right="account-remove"
                    @click="deleteCancellation(props.row)"></b-button>
          <b-button size="is-small" v-show="!hideCancellation && !hasPermanentCancellation(props.row)" title="Lav afbud permanent (Alle holdrunder)"
                    icon-right="account-injury" @click="makeCancellationPermanent(props.row)"></b-button>
          <b-button size="is-small" type="is-danger" v-show="!hideCancellation && hasPermanentCancellation(props.row)" title="Annuller permanent afbud"
                    icon-right="account-injury" @click="removePermanentCancellation(props.row)"></b-button>
          <b-button size="is-small" v-show="hideCancellation" title="Afbud (Denne holdrunde)"
                    icon-right="account-off" @click="makeCancellation(props.row)"></b-button>
          <b-button size="is-small" v-show="hideCancellation" title="Tilføj på hold (Næste ledig plads)" icon-right="plus"
                    @click="addPlayerCustom(props.row)"></b-button>
        </div>
      </b-table-column>
      <template #empty>
        <div class="has-text-centered">Ingen afbud.</div>
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
import EditPlayerModal from "./EditPlayerModal.vue";
import AddMemberModal from "./AddMemberModal.vue";

export default {
  name: 'PlayersListSearch',
  props: {
    clubId: String,
    teamId: String,
    addPlayer: Function,
    version: Date,
    loading: Boolean
  },
  computed: {
    memberSearchPointsFiltered() {
      return this.memberSearch.data
    }
  },
  mounted() {
    this.$root.$on('player-added-to-category', (player) => {
      this.$apollo.queries.memberSearch.refresh()
    })
    this.$root.$on('player-deleted-from-category', (player) => {
      this.$apollo.queries.memberSearch.refresh()
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
      hideCancellation: true,
      rankingList: 'WOMEN_SINGLE',
      perPage: 15,
      currentPage: 1,
      total: 0,
      searchName: ''
    }
  },
  methods: {
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
            this.$apollo.queries.memberSearch.refresh()
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
    findPermanentCancellation(cancellations) {
      return cancellations.find((c) => {
        return c.teamId === null
      })
    },
    makeCancellationPermanent(player) {
      this.$apollo
          .mutate({
                    mutation: gql`
                        mutation createCancellation($refId : String!, $teamId: ID){
                            createCancellation(refId: $refId, teamId: $teamId){
                                id
                                refId
                                teamId
                            }
                        }
                    `,
                    variables: {
                      refId: player.refId,
                      teamId: null
                    }
                  })
          .then(() => {
            this.$apollo.queries.memberSearch.refresh()
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
      const cancellation = this.findPermanentCancellation(player.cancellations)

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
            this.$apollo.queries.memberSearch.refresh()
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
                      teamId: this.teamId
                    }
                  })
          .then(() => {
            this.$apollo.queries.memberSearch.refresh()
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
    deleteCancellation(player) {
      // If delete cancellation is called its garantied that only one cancellation exists
      const cancellation = player.cancellations[0];
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
            this.$apollo.queries.memberSearch.refresh()
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
    hasPermanentCancellation(player) {
      return player.hasOwnProperty('cancellations') && this.findPermanentCancellation(player.cancellations) !== undefined;
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
    memberSearch: {
      query() {
        if (this.hideCancellation) {
          return gql`
                        query memberSearchPoints(
                            $version: Date!,
                            $page: Int,
                            $first: Int,
                            $name: String,
                            $teamId: String!,
                            $rankingList: MemberSearchOrderBy!){
                          memberSearchPoints(
                          version: $version,
                          name: $name,
                          page: $page,
                          first: $first,
                          teamId: $teamId,
                          rankingList: $rankingList) {
                            data {
                              id
                              name
                              gender
                              refId
                              vintage
                              ownerId
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
                    `
        } else {
          return gql`
                        query memberSearchCancellation($teamId: String!, $version: Date){
                            memberSearchCancellation(teamId: $teamId){
                                data {
                                  id
                                  name
                                  gender
                                  refId
                                  ownerId
                                  points(version: $version){
                                    points
                                    position
                                    category
                                    vintage
                                  }
                                  cancellations(teamId: $teamId){
                                    id
                                    refId
                                    teamId
                                  }
                                }
                                paginatorInfo {
                                  count
                                  total
                                }
                            }
                        }
                    `
        }
      },
      update: data => data.memberSearchPoints || data.memberSearchCancellation,
      fetchPolicy: "network-only",
      variables() {
        let params = {
          page: this.currentPage,
          first: this.perPage,
          teamId: this.teamId
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
        if (!this.hideCancellation) {
          params.rankingList = 'ALL_LEVEL';
        }
        return params
      }
    }
  }

}
</script>
