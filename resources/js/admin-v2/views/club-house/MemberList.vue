<script>
import CardComponent from "@/components/CardComponent.vue";
import CreateInvitationModal from "@/views/club-house/CreateInvitationModal.vue";
import clubhouse from "../../../queries/clubhouse.gql";
import gql from "graphql-tag";

export default {
    name: "MemberList",
    props: {'users': Array, 'loading': Boolean},
    inject: ['clubhouseId', 'user'],
    components: {CreateInvitationModal, CardComponent},
    apollo: {},
    data() {
        return {
            showModal: false,
            isDeleting: false
        }
    },
    methods: {
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
        <card-component
            title="Medlems liste"
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
                >
                    <b-table-column field="id" label="ID" numeric v-slot="props">
                        {{ props.row.id }}
                    </b-table-column>
                    <b-table-column sortable field="name" label="Navn" v-slot="props">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column sortable field="email" label="Email" v-slot="props">
                        {{ props.row.email }}
                    </b-table-column>
                    <b-table-column field="roles" label="Roles" numeric v-slot="props">
                        {{ props.row.roles.map(r => r.name).join(', ') }}
                    </b-table-column>
                    <b-table-column label="Funktioner" numeric v-slot="props">
                        <b-button
                            icon-left="delete"
                            size="is-small"
                            type="is-danger"
                            title="Fjern medlem fra klubben"
                            @click="deleteMembership(props.row)"
                            :disabled="props.row.id === user.id"
                        />
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
