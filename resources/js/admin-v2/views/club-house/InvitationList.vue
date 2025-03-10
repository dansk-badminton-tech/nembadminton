<script>
import CardComponent from "@/components/CardComponent.vue";
import CreateInvitationModal from "@/views/club-house/CreateInvitationModal.vue";
import gql from "graphql-tag";
import clubhouse from "../../../queries/clubhouse.gql";

export default {
    name: "InvitationList",
    components: {CreateInvitationModal, CardComponent},
    props: {'invitations': Array, 'loading': Boolean},
    inject: ['clubhouseId'],
    data() {
        return {
            showModal: false
        }
    },
    methods: {
        deleteInvitation(invitation) {
            this.$apollo.mutate({
                mutation: gql`
                    mutation deleteInvitation($id: ID!){
                        deleteInvitation(id: $id){
                            id
                            status
                        }
                    }`,
                variables: {
                    id: invitation.idclu
                },
                refetchQueries: [{query: clubhouse, variables: {id: this.clubhouseId}}]
            }).then((data) => {
                this.$buefy.toast.open({message: `Invitation slettet`, type: "is-success", duration: 5000});
            }).catch((err) => {
                console.log(err)
                this.$buefy.toast.open({message: `Error deleting invitation`, type: "is-danger", duration: 5000});
            })
        },
        confirmDelete(invitation) {
            this.$buefy.dialog.confirm({
                                           message: 'Vil du slette denne invitation?',
                                           onConfirm: () => {this.deleteInvitation(invitation)}
                                       })
        },
        showInvitation(invitation){
            this.$buefy.dialog.alert({
                                        title: 'Invitation url',
                                         message: invitation.url
            })
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
                <CreateInvitationModal @close="props.close" />
            </template>
        </b-modal>
        <card-component
            title="Invitationer"
            icon="home"
        >
            <template v-slot:header>
                <div class="card-header-icon">
                    <b-button @click="showModal = true" type="is-info" aria-label="more options">Inviter medlem</b-button>
                </div>
            </template>
            <template v-slot:default>
                <b-table
                    :data="invitations"
                    :loading="loading"
                    paginated
                    pagination-rounded
                >
                    <b-table-column sortable field="id" label="ID" numeric v-slot="props">
                        {{ props.row.id }}
                    </b-table-column>
                    <b-table-column searchable sortable field="inviteeEmail" label="Email" v-slot="props">
                        {{ props.row.inviteeEmail }}
                    </b-table-column>
                    <b-table-column field="roles" label="Roller" v-slot="props">
                        {{ props.row.role }}
                    </b-table-column>
                    <b-table-column field="status" sortable label="Status" v-slot="props">
                        {{ props.row.status }}
                    </b-table-column>
                    <b-table-column field="expiresAt" sortable label="Udløber" v-slot="props">
                        {{ props.row.expiresAt }}
                    </b-table-column>
                    <b-table-column field="acceptedAt" sortable label="Accepteret" v-slot="props">
                        {{ props.row.acceptedAt }}
                    </b-table-column>
                    <b-table-column field="createdAt" sortable label="Oprettet"  v-slot="props">
                        {{ props.row.createdAt }}
                    </b-table-column>
                    <b-table-column label="Funktioner" v-slot="props">
                        <div class="buttons">
                            <b-button
                                icon-left="message"
                                size="is-small"
                                type="is-info"
                                title="Vis invitations url'en"
                                @click="showInvitation(props.row)"
                            />
                            <b-button
                                icon-left="delete"
                                size="is-small"
                                type="is-danger"
                                title="Slet invitation"
                                @click="confirmDelete(props.row)"
                                v-if="props.row.status === 'PENDING'"
                            />
                        </div>
                    </b-table-column>
                    <template v-slot:empty>
                        Ingen invitationer fundet
                    </template>
                </b-table>
            </template>
        </card-component>
    </fragment>
</template>

<style scoped>

</style>
