<script>
import {debounce} from "@/helpers.js";
import gql from "graphql-tag";

export default {
    name: "Cancellation",
    props: {
        teamId: String
    },
    data() {
        return {
            data: [],
            selected: null,
            isFetching: false,
            querySearchName: '',
            total: 0,
            perPage: 20,
            page: 0
        }
    },
    apollo: {
        team: {
            query: gql` query ($id: ID!){
                  team(id: $id){
                    id
                    name
                    gameDate
                    club {
                        id
                        name1
                    }
                  }
                }`,
            variables() {
                return {
                    id: this.teamId
                }
            }
        },
        membersCancellationSearch: {
            query: gql`query membersCancellationSearch($clubId: Int!, $name: String, $first: Int!, $page: Int){
                      membersCancellationSearch(name: $name, clubId: $clubId, first: $first, page: $page) {
                        data {
                          id
                          name
                          gender
                          refId
                        }
                        paginatorInfo{
                            total
                        }
                      }
                    }
                `,
            variables() {
                return {
                    clubId: 1622,
                    name: '%' + this.querySearchName + '%',
                    first: this.perPage,
                    page: this.page
                }
            },
            result({data}) {
                console.log(data)
                this.data = data.membersCancellationSearch.data
                this.total = data.membersCancellationSearch.paginatorInfo.total
            }
        }
    },
    methods: {
        confirm(member) {
            this.$buefy.dialog.prompt({
                                          message: `Sikker på du vil melde afbud på "${member.name}" BP ID: ${member.refId}?`,
                                          inputAttrs: {
                                              type: "email",
                                              placeholder: "daniel@gmail.com"
                                          },
                                          trapFocus: true,
                                          closeOnConfirm: false,
                                          onConfirm: (value, {close, startLoading, cancelLoading}) => {
                                              startLoading();
                                              this.$buefy.toast.open(`Your message is sending...`);
                                              setTimeout(() => {
                                                  this.$buefy.toast.open(`Success message send!`);
                                                  cancelLoading();
                                              }, 2000);
                                          },
                                          cancelText: 'Fortryd',
                                          confirmText: 'Meld afbud',
                                      })
        },
        reportCancellation() {
            this.$apollo.mutate({
                                    mutation: gql`
                    mutation createCancellation($refId: String!, $teamId: ID){
                        createCancellation(refId: $refId, teamId: $teamId){
                            id
                            teamId
                            refId
                        }
                    }
                `
                                })
                .then(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-success',
                            message: `Afbud registeret!`
                        })
                })
                .catch(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            message: `Kunne ikke tilføje spiller til holdet :(`
                        })
                })
        }
    }
}
</script>

<template>
    <section>
        <div class="block">
            <h1 class="title is-3">Melde afbud til holdrunde</h1>
            <ul>
                <li>{{ team?.club.name1 }}</li>
                <li>{{ team?.name }}</li>
                <li>{{ team?.gameDate }}</li>
            </ul>
        </div>
        <b-field label="Navn" expanded>
            <b-input placeholder="Fx. Anders Antonsen" v-model="querySearchName" rounded></b-input>
        </b-field>
        <b-table
            :data="data"
            paginated
            backend-pagination
            :total="total"
            :per-page="perPage"
            @page-change="page => this.page = page"
            :loading="$apollo.queries.membersCancellationSearch.loading"
        >
            <b-table-column field="name" label="Navn" v-slot="props">
                {{ props.row.name }}
            </b-table-column>
            <b-table-column field="refId" label="BadmintonPlayer Id" v-slot="props">
                {{ props.row.refId }}
            </b-table-column>
            <b-table-column label="Køn" v-slot="props">
                <span>
                    <b-icon
                        :icon="props.row.gender === 'M' ? 'gender-male' : 'gender-female'">
                    </b-icon>
                    {{ props.row.gender }}
                </span>
            </b-table-column>
            <b-table-column v-slot="props">
                <b-button type="is-danger is-light" @click="confirm(props.row)">Meld afbud</b-button>
            </b-table-column>
        </b-table>
    </section>
</template>

<style scoped>

</style>
