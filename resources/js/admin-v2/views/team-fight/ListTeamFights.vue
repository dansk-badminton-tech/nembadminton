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
                <div class="buttons">
                    <b-button
                        icon-left="pencil"
                        size="is-small"
                        tag="router-link"
                        type="is-link"
                        title="Rediger holdrunden"
                        v-bind:to="props.row.id+'/edit'">
                    </b-button>
                    <b-button
                        icon-left="content-copy"
                        title="Kopier holdrunden"
                        size="is-small"
                        @click="copyTeamFight(props.row.id)">
                    </b-button>
                    <b-button
                        icon-left="trash-can"
                        title="Slet holdrunden"
                        size="is-small"
                        type="is-danger"
                        @click="deleteTeamFight(props.row.id)">
                    </b-button>
                </div>
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
        deleteTeamFight(teamFightId) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette helt holdet?',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation ($id: ID!){
                                        deleteTeam(id: $id){
                                            id
                                        }
                                    }
                                `,
                                variables: {
                                    id: teamFightId
                                }
                            }).then(() => {
                            this.$buefy.snackbar.open(
                                {
                                    duration: 5000,
                                    type: 'is-success',
                                    message: "Holdrunden er nu slettet."
                                })
                            this.$apollo.queries.teams.refetch()
                        })
                    }
                })
        },
        copyTeamFight(teamFightId) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil kopier hele holdrunden? <br><br> Holdrunden kommer til at hedde "Kopi af ...." og du kan skifte ranglisten efter kopiring',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation ($id: ID!){
                                        copyTeam(id: $id){
                                            id
                                            name
                                        }
                                    }
                                `,
                                variables: {
                                    id: teamFightId
                                }
                            }).then(({data}) => {
                            this.$buefy.snackbar.open(
                                {
                                    duration: 5000,
                                    type: 'is-success',
                                    message: "Holdrunden kopiret. Den hedder \"" + data?.copyTeam?.name + "\""
                                })
                            this.$apollo.queries.teams.refetch()
                        })
                    }
                })
        },
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
