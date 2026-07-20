<script>
import CardComponent from "@/components/CardComponent.vue";
import CreateInvitationModal from "@/views/club-house/CreateInvitationModal.vue";
import EditRolesModal from "@/views/club-house/EditRolesModal.vue";
import clubhouse from "../../../queries/clubhouse.gql";
import gql from "graphql-tag";

export default {
    name: "MemberList",
    props: {'users': Array, 'loading': Boolean},
    inject: ['clubhouseId', 'user'],
    components: {CreateInvitationModal, EditRolesModal, CardComponent},
    apollo: {},
    data() {
        return {
            showModal: false,
            showEditModal: false,
            editTarget: null,
            isDeleting: false,
            resettingId: null
        }
    },
    methods: {
        canResetPlayerId(row) {
            return row.player_id
        },
        confirmResetPlayerId(user) {
            this.$buefy.dialog.confirm({
                title: 'Nulstil BadmintonID',
                message: `Er du sikker på at du vil nulstille BadmintonID for <strong>${user.name}</strong>?`,
                type: 'is-warning',
                hasIcon: true,
                icon: 'alert',
                confirmText: 'Nulstil',
                cancelText: 'Annuller',
                onConfirm: () => this.resetPlayerId(user)
            })
        },
        resetPlayerId(user) {
            this.resettingId = user.id
            this.$apollo.mutate({
                mutation: gql`mutation resetMemberPlayerId($clubhouseId: ID!, $userId: ID!) {
                    resetMemberPlayerId(clubhouseId: $clubhouseId, userId: $userId) { id player_id }
                }`,
                variables: {
                    clubhouseId: user.clubhouse.id,
                    userId: user.id
                },
                refetchQueries: [{query: clubhouse, variables: {id: this.clubhouseId}}]
            })
                .then(() => {
                    this.$buefy.snackbar.open({message: 'BadmintonID nulstillet', type: 'is-success', duration: 5000})
                })
                .catch(() => {
                    this.$buefy.snackbar.open({message: 'Kunne ikke nulstille BadmintonID', type: 'is-danger', duration: 5000})
                })
                .finally(() => {
                    this.resettingId = null
                })
        },
        deleteMembership(user) {
            this.isDeleting = true;
            this.$apollo.mutate({
                                    mutation: gql`mutation deleteMembership($userId: ID!, $clubhouseId: ID!){
                                        deleteMembership(userId: $userId, clubhouseId: $clubhouseId)
                                    }`,
                                    variables: {
                                        userId: user.id,
                                        clubhouseId: user.clubhouse.id
                                    },
                                    refetchQueries: [{query: clubhouse, variables: {id: this.clubhouseId}}]
                                })
                .then(() => {
                    this.$buefy.snackbar.open({message: `Medlem slettet`, type: "is-success", duration: 5000});
                })
                .catch((err) => {
                    this.$buefy.snackbar.open({message: `Fejl kunne ikke slette medlem`, type: "is-danger", duration: 5000});
                })
                .finally(() => {
                    this.isDeleting = false;
                })
        },
        openEditModal(user) {
            this.editTarget = user;
            this.showEditModal = true;
        }
    }
}
</script>

<template>
    <fragment>
        <b-modal
            v-model="showModal"
            has-modal-card
        >
            <template v-slot:default="props">
                <CreateInvitationModal @close="props.close"/>
            </template>
        </b-modal>
        <b-modal
            v-if="editTarget"
            v-model="showEditModal"
            has-modal-card
        >
            <template v-slot:default="props">
                <EditRolesModal :target="editTarget" :key="editTarget.id" @close="props.close"/>
            </template>
        </b-modal>
        <card-component
            title="Medlemmer"
            icon="home"
        >
            <template v-slot:header>
                <div class="card-header-icon">
                    <b-button @click="showModal = true" type="is-info" aria-label="more options">Inviter medlem</b-button>
                </div>
            </template>
            <template v-slot:default>
                <b-table
                    :data="users"
                    :loading="loading || isDeleting"
                    paginated
                    pagination-rounded
                >
                    <b-table-column field="id" label="ID" numeric v-slot="props">
                        {{ props.row.id }}
                    </b-table-column>
                    <b-table-column sortable field="name" searchable label="Navn" v-slot="props">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column sortable field="email" searchable label="Email" v-slot="props">
                        {{ props.row.email }}
                    </b-table-column>
                    <b-table-column sortable field="member" searchable label="BadmintonID" v-slot="props">
                        <template v-if="props.row.player_id">
                            {{props.row.player_id}} ({{ props.row.member?.name }})
                        </template>
                        <template v-else>
                            Ingen spiller tilknyttet
                        </template>
                    </b-table-column>
                    <b-table-column field="roles" label="Roller" numeric v-slot="props">
                        {{ props.row.roles.map(r => r.name).join(', ') }}
                    </b-table-column>
                    <b-table-column label="Funktioner" numeric v-slot="props">
                        <div class="buttons is-right">
                            <b-button
                                icon-left="pencil"
                                size="is-small"
                                type="is-info"
                                title="Rediger roller"
                                @click="openEditModal(props.row)"
                                :disabled="props.row.id === user.id"
                            />
                            <b-button
                                v-if="canResetPlayerId(props.row)"
                                icon-left="account-remove"
                                size="is-small"
                                type="is-warning"
                                title="Nulstil BadmintonID"
                                @click="confirmResetPlayerId(props.row)"
                                :loading="resettingId === props.row.id"
                            />
                            <b-button
                                icon-left="delete"
                                size="is-small"
                                type="is-danger"
                                title="Fjern medlem fra klubben"
                                @click="deleteMembership(props.row)"
                                :disabled="props.row.id === user.id"
                            />
                        </div>
                    </b-table-column>
                    <template v-slot:empty>
                        Ingen medlemmer fundet
                    </template>
                </b-table>
            </template>
        </card-component>
    </fragment>
</template>

<style scoped>

</style>
