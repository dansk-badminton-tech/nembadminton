<template>
    <fragment>
        <div class="notification">
            <div class="buttons has-addons">
                <b-button @click="setGameDate(currentSeason)">{{currentSeasonButtonName}}</b-button>
                <b-button @click="setGameDate(previousSeason)">{{previousSeasonButtonName}}</b-button>
                <b-button @click="setGameDateToRest">Tidligere sæsoner</b-button>
                <p class="ml-4">Viser holdrunder med spilledatoer fra <b>{{this.gameDate.from}}</b> - <b>{{this.gameDate.to}}</b></p>
            </div>
        </div>
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
                <router-link v-bind:to="props.row.id+'/edit'">{{ props.row.name }}</router-link>
            </b-table-column>
            <b-table-column v-slot="props" sortable field="gameDate" label="Spilledato">
                {{ props.row.gameDate }}
            </b-table-column>
            <b-table-column v-slot="props" field="version" label="Rangliste">
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
                    v-bind:to="props.row.id+'/edit'">Rediger
                </b-button>
            </b-table-column>
        </b-table>
        <CreateTeamFightAction v-if="teams?.data?.length === 0"></CreateTeamFightAction>
    </fragment>
</template>
<script>
import gql from "graphql-tag";
import CreateTeamFightAction from "./CreateTeamFightAction.vue";
import {subAYear, getCurrentSeasonStart, addAYear} from "../../helpers";

const generateGameDate = (seasonStartDate)=>{
    return {
        from: seasonStartDate.toISOString().substring(0,10),
        to: addAYear(seasonStartDate, 1).toISOString().substring(0,10)
    }
}

export default {
    name: 'ListTeamFights',
    components: {CreateTeamFightAction},
    inject: ['clubhouseId'],
    props: {
        loading: Boolean,
    },
    computed: {
        currentSeason: getCurrentSeasonStart,
        previousSeason(){
            let date = getCurrentSeasonStart()
            return subAYear(date, 1)
        },
        currentSeasonButtonName(){
            let currentYear = this.currentSeason.getFullYear();
            return currentYear+"/"+(currentYear+1)
        },
        previousSeasonButtonName(){
            let year = this.previousSeason.getFullYear()
            return year+"/"+(year+1)
        }
    },
    data(){
        return {
            teams: [],
            currentPage: 0,
            perPage: 10,
            order: [{
                column: 'GAME_DATE',
                order: 'DESC'
            }],
            gameDate: generateGameDate(getCurrentSeasonStart())
        }
    },
    methods: {
        setGameDateToRest(){
            let date = new Date(this.previousSeason.getTime())
            this.gameDate = {
                from: "2020-01-01", // Magic number because nembadminton existed after this date
                to: date.toISOString().substring(0,10)
            }
        },
        setGameDate(seasonStartDate){
            let date = new Date(seasonStartDate.getTime())
            this.gameDate = {
                from: date.toISOString().substring(0,10),
                to: addAYear(date, 1).toISOString().substring(0,10)
            }
        },
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
                query Teams($clubhouseId: ID!, $first: Int!, $page: Int, $order: [QueryTeamsOrderOrderByClause!], $gameDate: DateRange){
                    teams(clubhouseId: $clubhouseId, order: $order, first: $first, page: $page, gameDate: $gameDate){
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
                    order: this.order,
                    gameDate: this.gameDate,
                    clubhouseId: this.clubhouseId
                }
            },
            fetchPolicy: 'network-only'
        }
    }

}
</script>
