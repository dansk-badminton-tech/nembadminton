<template>
    <div dusk="team-list-page">
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Hold
        </hero-bar>
        <section class="section is-main-section">
            <div class="mb-3 mt-3">
                <b-button
                    dusk="create-team-button"
                    type="is-link"
                    icon-left="plus"
                    @click="openCreate">
                    Opret hold
                </b-button>
            </div>
            <card-component title="Hold" class="has-table has-mobile-sort-spaced">
                <b-table
                    :data="teams.data"
                    :loading="$apollo.queries.teams.loading"
                    paginated
                    backend-pagination
                    :total="teams.paginatorInfo.total"
                    :per-page="perPage"
                    :current-page.sync="page"
                    @page-change="onPageChange">
                    <b-table-column field="name" label="Navn" v-slot="props">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column field="tier" label="Niveau" v-slot="props">
                        {{ resolveTierLabel(props.row) }}
                    </b-table-column>
                    <b-table-column field="groupName" label="Gruppe" v-slot="props">
                        {{ props.row.groupName || '—' }}
                    </b-table-column>
                    <b-table-column label="Handlinger" v-slot="props" width="180">
                        <b-button
                            size="is-small"
                            icon-left="pencil"
                            :dusk="`edit-team-${props.row.id}`"
                            @click="openEdit(props.row)">
                            Rediger
                        </b-button>
                        <b-button
                            size="is-small"
                            type="is-danger"
                            icon-left="delete"
                            class="ml-2"
                            :dusk="`delete-team-${props.row.id}`"
                            @click="confirmDelete(props.row)">
                            Slet
                        </b-button>
                    </b-table-column>
                    <template #empty>
                        <p class="has-text-centered">Ingen hold endnu — opret det første.</p>
                    </template>
                </b-table>
            </card-component>

            <b-modal v-model="modalOpen" :width="560" has-modal-card :can-cancel="['x', 'escape']">
                <team-form
                    :team="editingTeam"
                    :clubhouse-id="clubhouseId"
                    @cancel="modalOpen = false"
                    @saved="onSaved">
                </team-form>
            </b-modal>
        </section>
    </div>
</template>

<script>
import gql from "graphql-tag";
import TeamsQuery from "../../../queries/teams.gql";
import TeamForm from "./TeamForm.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import CardComponent from "../../components/CardComponent.vue";

export default {
    name: "TeamList",
    components: {TeamForm, TitleBar, HeroBar, CardComponent},
    props: {
        clubhouseId: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            titleStack: ['Admin', 'Hold'],
            teams: {data: [], paginatorInfo: {total: 0}},
            page: 1,
            perPage: 25,
            modalOpen: false,
            editingTeam: null
        }
    },
    apollo: {
        teams: {
            query: TeamsQuery,
            variables() {
                return {
                    clubhouseId: this.clubhouseId,
                    page: this.page,
                    first: this.perPage
                }
            },
            update(data) {
                return data.teams;
            }
        }
    },
    methods: {
        resolveTierLabel(team) {
            return team?.tier?.tierName || team?.customTierName || '—';
        },
        openCreate() {
            this.editingTeam = null;
            this.modalOpen = true;
        },
        openEdit(team) {
            this.editingTeam = team;
            this.modalOpen = true;
        },
        onSaved() {
            this.modalOpen = false;
            this.editingTeam = null;
            this.$apollo.queries.teams.refetch();
        },
        onPageChange(newPage) {
            this.page = newPage;
        },
        confirmDelete(team) {
            this.$buefy.dialog.confirm({
                title: 'Slet hold',
                message: `Er du sikker på, at du vil slette "${team.name}"?`,
                confirmText: 'Slet',
                cancelText: 'Annuller',
                type: 'is-danger',
                onConfirm: () => this.deleteTeam(team)
            });
        },
        deleteTeam(team) {
            this.$apollo.mutate({
                mutation: gql`
                    mutation($id: ID!) {
                        deleteTeam(id: $id) { id }
                    }
                `,
                variables: {id: team.id}
            }).then(() => {
                this.$buefy.toast.open({message: 'Hold slettet', type: 'is-success'});
                this.$apollo.queries.teams.refetch();
            }).catch((err) => {
                this.$buefy.toast.open({message: 'Kunne ikke slette holdet', type: 'is-danger', duration: 5000});
                console.error(err);
            });
        }
    }
}
</script>
