<script>
import CardComponent from "@/components/CardComponent.vue";
import CreateInvitationModal from "@/views/club-house/CreateInvitationModal.vue";

export default {
    name: "MemberList",
    props: {'users': Array},
    components: {CreateInvitationModal, CardComponent},
    apollo: {},
    data() {
        return {
            showModal: true
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
            title="Medlems liste"
            icon="home"
        >
            <template v-slot:header>
                <div class="card-header-icon">
                    <b-button @click="showModal = true" type="is-info" aria-label="more options">Tilføj spiller</b-button>
                </div>
            </template>
            <template v-slot:default>
                <b-table
                    :data="users"
                >
                    <b-table-column field="id" label="ID" numeric v-slot="props">
                        {{ props.row.id }}
                    </b-table-column>
                    <b-table-column field="name" label="Navn" numeric v-slot="props">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column field="roles" label="Roles" numeric v-slot="props">
                        {{ props.row.roles.map(r => r.name).join(', ') }}
                    </b-table-column>
                    <b-table-column label="Funktioner" numeric v-slot="props">

                    </b-table-column>
                </b-table>
            </template>
        </card-component>
    </fragment>
</template>

<style scoped>

</style>
