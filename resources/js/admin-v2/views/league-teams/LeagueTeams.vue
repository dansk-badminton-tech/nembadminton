<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            <b-icon icon="soccer" size="is-small"></b-icon>
            Hold
        </hero-bar>
        
        <section class="section is-main-section">
            <!-- Header Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-title">
                        <b-icon icon="soccer" size="is-small"></b-icon>
                        <span class="ml-2">Hold Oversigt</span>
                        <span class="tag is-info ml-2" v-if="totalTeams > 0">{{ totalTeams }}</span>
                    </div>
                    <div class="card-header-icon">
                        <b-button
                            @click="showCreateModal = true"
                            icon-left="plus"
                            type="is-primary"
                            size="is-small">
                            Opret Nyt Hold
                        </b-button>
                    </div>
                </div>
                <div class="card-content">
                    <!-- Search and Filter Controls -->
                    <b-field grouped>
                        <b-field>
                            <b-input
                                v-model="searchQuery"
                                placeholder="Søg efter holdnavn..."
                                icon="magnify"
                                size="is-small"
                                clearable>
                            </b-input>
                        </b-field>
                    </b-field>
                    
                    <!-- League Teams Table -->
                    <b-table
                        :data="filteredTeams"
                        :loading="$apollo.queries.leagueTeams.loading"
                        :paginated="true"
                        :backend-pagination="true"
                        :per-page="perPage"
                        :total="leagueTeams.paginatorInfo.total"
                        :current-page="currentPage"
                        @page-change="page => currentPage = page"
                        backend-sorting
                        default-sort="name"
                        @sort="onSort">
                        
                        <b-table-column field="name" label="Holdnavn" sortable v-slot="props">
                            <b-icon icon="soccer" size="is-small" type="is-grey"></b-icon>
                            <span class="ml-1">{{ props.row.name }}</span>
                        </b-table-column>

                        <b-table-column field="club.name1" label="Klub" v-slot="props">
                            <b-icon icon="home" size="is-small" type="is-grey"></b-icon>
                            <span class="ml-1">{{ props.row.club?.name1 || 'Ingen klub' }}</span>
                        </b-table-column>

                        <b-table-column field="division.name" label="Division" v-slot="props">
                            <b-icon icon="trophy" size="is-small" type="is-grey"></b-icon>
                            <span class="ml-1">{{ props.row.division?.name || 'Ingen division' }}</span>
                        </b-table-column>

                        <b-table-column label="Oprettet af" v-slot="props">
                            <b-tag 
                                :type="getTeamOriginType(props.row)" 
                                size="is-small">
                                <b-icon 
                                    :icon="getTeamOriginIcon(props.row)" 
                                    size="is-small" 
                                    class="mr-1">
                                </b-icon>
                                {{ getTeamOriginText(props.row) }}
                            </b-tag>
                        </b-table-column>

                        <b-table-column field="created_at" label="Oprettet" sortable v-slot="props">
                            <b-icon icon="clock" size="is-small" type="is-grey"></b-icon>
                            <span class="ml-1">{{ formatDate(props.row.created_at) }}</span>
                        </b-table-column>
                        
                        <b-table-column label="Handlinger" v-slot="props">
                            <div v-show="!props.row.created_by_system" class="buttons">
                                <b-button
                                    icon-left="pencil"
                                    type="is-info"
                                    size="is-small"
                                    @click="editTeam(props.row)"
                                    aria-label="Rediger hold"
                                    title="Rediger hold"
                                />
                                <b-button
                                    icon-left="delete"
                                    type="is-danger"
                                    size="is-small"
                                    @click="confirmDeleteTeam(props.row)"
                                    aria-label="Slet hold"
                                    title="Slet hold"
                                />
                            </div>
                        </b-table-column>

                        <template v-slot:detail="props">
                            <div class="content">
                                <p><strong>Detaljer for {{ props.row.name }}</strong></p>
                                <ul>
                                    <li><strong>Klub:</strong> {{ props.row.club?.name1 || 'Ingen klub' }}</li>
                                    <li v-if="props.row.group"><strong>Division:</strong> {{ props.row.group?.division?.name }}</li>
                                    <li><strong>Oprettet:</strong> {{ formatDate(props.row.created_at) }}</li>
                                    <li><strong>Opdateret:</strong> {{ formatDate(props.row.updated_at) }}</li>
                                </ul>
                            </div>
                        </template>
                        
                        <template v-slot:empty>
                            <div class="has-text-centered py-6">
                                <b-icon icon="soccer-field" size="is-large" type="is-grey-light"></b-icon>
                                <p class="title is-5 has-text-grey-light mt-3">Ingen hold fundet</p>
                                <p class="subtitle is-6 has-text-grey">
                                    <span v-if="searchQuery || selectedClub">Prøv at justere dine søgekriterier.</span>
                                    <span v-else>Der er endnu ikke oprettet nogen hold.</span>
                                </p>
                            </div>
                        </template>
                    </b-table>
                </div>
            </div>
        </section>

        <!-- Create/Edit Modal -->
        <league-team-form-modal
            :is-active="showCreateModal || showEditModal"
            :team="selectedTeam"
            :clubs="clubs"
            :divisions="divisions.data"
            @close="closeModal"
            @saved="onTeamSaved"
        />
    </div>
</template>

<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import LeagueTeamFormModal from "./LeagueTeamFormModal.vue";
import gql from "graphql-tag";
import moment from "moment";

const LEAGUE_TEAMS_QUERY = gql`
    query LeagueTeams($orderBy: [QueryLeagueTeamsOrderByOrderByClause!], $first: Int, $page: Int, $club_ids: [ID!]) {
        leagueTeams(orderBy: $orderBy, first: $first, page: $page, club_id: $club_ids) {
            paginatorInfo {
                count
                currentPage
                firstItem
                hasMorePages
                lastItem
                lastPage
                perPage
                total
            }
            data {
                id
                name
                created_by_system
                created_at
                updated_at
                club {
                    id
                    name1
                }
                division {
                    id
                    name
                }
                clubhouse {
                    id
                    name
                }
            }
        }
    }
`;

const DIVISIONS_QUERY = gql`
    query Divisions {
            divisions {
                data {  
                    id  
                    name
                    code
                }
            }
    }
`;

const DELETE_LEAGUE_TEAM_MUTATION = gql`
    mutation DeleteLeagueTeam($id: ID!) {
        deleteLeagueTeam(id: $id) {
            id
        }
    }
`;

export default {
    name: "LeagueTeams",
    components: {
        TitleBar,
        HeroBar,
        LeagueTeamFormModal
    },
    inject: ['user'],
    data() {
        return {
            titleStack: ['Admin', 'Hold'],
            searchQuery: '',
            selectedClub: '',
            showCreateModal: false,
            showEditModal: false,
            selectedTeam: null,
            currentPage: 1,
            perPage: 20,
            orderBy: [{
                column: 'NAME',
                order: 'ASC'
            }],
            divisions: {
                data: []
            },
            leagueTeams: {
                paginatorInfo: {
                    total: 0
                },
                data: []
            }
        }
    },
    apollo: {
        leagueTeams: {
            query: LEAGUE_TEAMS_QUERY,
            variables() {
                return this.queryVariables;
            }
        },
        divisions: {
            query: DIVISIONS_QUERY
        }
    },
    computed: {
        clubs() {
            return this.user?.clubhouse?.clubs || [];
        },
        queryVariables() {
            const vars = {
                orderBy: this.orderBy,
                first: this.perPage,
                page: this.currentPage
            };
            
            if (this.user) {
                vars.club_ids = this.user.clubhouse.clubs.map(club => parseInt(club.id));
            }
            
            return vars;
        },
        filteredTeams() {
            if (!this.leagueTeams?.data) return [];
            
            let teams = this.leagueTeams.data;
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                teams = teams.filter(team => 
                    team.name.toLowerCase().includes(query) ||
                    team.club?.name1?.toLowerCase().includes(query)
                );
            }
            
            return teams;
        },
        totalTeams() {
            return this.leagueTeams?.paginatorInfo?.total || 0;
        }
    },
    methods: {
        onSort(field, order) {
            this.orderBy = [{
                column: field.toUpperCase(),
                order: order.toUpperCase()
            }];
        },
        editTeam(team) {
            this.selectedTeam = team;
            this.showEditModal = true;
        },
        confirmDeleteTeam(team) {
            this.$buefy.dialog.confirm({
                message: `Er du sikker på, at du vil slette holdet "${team.name}"?`,
                onConfirm: () => this.deleteTeam(team)
            });
        },
        async deleteTeam(team) {
            try {
                await this.$apollo.mutate({
                    mutation: DELETE_LEAGUE_TEAM_MUTATION,
                    variables: { id: team.id },
                    refetchQueries: [{ query: LEAGUE_TEAMS_QUERY, variables: this.queryVariables }]
                });
                
                this.$buefy.toast.open({
                    message: 'Hold slettet succesfuldt',
                    type: 'is-success'
                });
            } catch (error) {
                console.error('Error deleting league team:', error);
                this.$buefy.toast.open({
                    message: 'Der opstod en fejl under sletning af holdet',
                    type: 'is-danger'
                });
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.showEditModal = false;
            this.selectedTeam = null;
        },
        onTeamSaved() {
            this.closeModal();
            this.$apollo.queries.leagueTeams.refetch();
        },
        getTeamOriginType(team) {
            if (team.created_by_system) return 'is-info';
            if (team.clubhouse.id) return 'is-success';
            return 'is-light';
        },
        getTeamOriginIcon(team) {
            if (team.created_by_system) return 'cog';
            if (team.clubhouse.id) return 'home-group';
            return 'help-circle';
        },
        getTeamOriginText(team) {
            if (team.created_by_system) return 'System';
            if (team.clubhouse.id) return 'Klubhus';
            return 'Ukendt';
        },
        formatDate(dateString) {
            return moment(dateString).format('DD/MM/YYYY HH:mm');
        }
    }
}
</script>

<style scoped>
.card + .card {
    margin-top: 1.5rem;
}

.tag {
    margin-left: 0.5rem;
}
</style>
