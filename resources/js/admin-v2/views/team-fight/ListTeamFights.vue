<template>
    <fragment>
        <b-table
            :data="teams?.data"
            :loading="$apollo.loading"
            paginated
            :total="teams?.paginatorInfo?.total"
            backend-pagination
            :per-page="perPage"
            @page-change="onPageChange"
            :default-sort="['GAME_DATE', 'DESC']"
            @sort="onSort"
        >
            <b-table-column v-slot="props" field="id" label="Navn">
                <router-link v-bind:to="'/team-fight/'+props.row.id+'/edit'">{{ props.row.name }}</router-link>
            </b-table-column>
            <b-table-column v-slot="props" sortable field="gameDate" label="Spilledato">
                {{ props.row.gameDate }}
            </b-table-column>
            <b-table-column v-slot="props" sortable field="version" label="Rangliste">
                {{ props.row.version }}
            </b-table-column>
            <b-table-column v-slot="props" sortable field="updatedAt" label="Oprettet">
                {{ props.row.createdAt }}
            </b-table-column>
            <b-table-column v-slot="props" label="Funktioner">
                <b-button
                    size="is-small"
                    tag="router-link"
                    type="is-link"
                    v-bind:to="'/team-fight/'+props.row.id+'/edit'">Rediger
                </b-button>
            </b-table-column>
        </b-table>
        <CreateTeamFightAction v-if="teams?.data?.length === 0"></CreateTeamFightAction>
    </fragment>
</template>
<script>
import gql from "graphql-tag";
import CreateTeamFightAction from "./CreateTeamFightAction.vue";

export default {
    name: 'ListTeamFights',
    components: {CreateTeamFightAction},
    props: {
        loading: Boolean
    },
    data(){
        return {
            teams: [],
            currentPage: 0,
            perPage: 20,
            order: [{
                column: 'GAME_DATE',
                order: 'DESC'
            }]
        }
    },
    methods: {
        onPageChange(page){
            this.currentPage = page
        },
        onSort(field, order){
            let mapping = {
                gameDate: 'GAME_DATE',
                updatedAt: 'UPDATED_AT'
            }
            this.order = [{
                column: mapping[field],
                order: order.toUpperCase()
            }]
        }
    },
    apollo: {
        teams: {
            query: gql`
                query Teams($first: Int!, $page: Int, $order: [QueryTeamsOrderOrderByClause!]){
                    teams(order: $order, first: $first, page: $page){
                        data{
                            id,
                            name,
                            version,
                            gameDate,
                            createdAt,
                            updatedAt
                        }
                        paginatorInfo{
                            total
                        }
                    }
                },
            `,
            variables () {
                // Use vue reactive properties here
                return {
                    first: this.perPage,
                    page: this.currentPage,
                    order: this.order
                }
            },
            fetchPolicy: 'network-only'
        }
    }

}
</script>
