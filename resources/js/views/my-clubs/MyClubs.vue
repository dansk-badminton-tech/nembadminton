<template>
    <div>
        <h1 class="title">Mine klubber</h1>
        <h2 class="subtitle">Tilknyt flere klubber til din profil og sammensæt holdkampe på tværs af klubber</h2>
        <b-button type="is-success" @click="showModal = true">Tilføj klub</b-button>
        <b-table :data="me?.clubs">
            <b-table-column field="id" label="ID" width="40" numeric v-slot="props">
                {{ props.row.id }}
            </b-table-column>
            <b-table-column field="name1" label="Name" v-slot="props">
                {{ props.row.name1 }} {{(me?.club?.id === props.row.id ? '(Hovedklub)' : '')}}
            </b-table-column>
            <b-table-column field="initialized" label="Klar til brug" v-slot="props">
                <b-icon v-if="props.row.initialized" icon="check"></b-icon>
                <fragment v-else>
                    <b-icon icon="spinner" class="fa-spin"></b-icon>
                    (Import igang. ETA: 3-5 min)
                </fragment>
            </b-table-column>
            <b-table-column field="id" width="40" numeric v-slot="props">
                <b-button :disabled="me?.club?.id === props.row.id" @click="deleteClubConnection(props.row)" type="is-danger">Fjern tilknytning</b-button>
            </b-table-column>
        </b-table>
        <b-modal v-model="showModal" has-modal-card close-button-aria-label="Close">
            <template #default="props">
                <modal-form @close="props.close"></modal-form>
            </template>
        </b-modal>
    </div>
</template>

<script>
import ME from "../../queries/me.gql";
import BadmintonPlayerClubs from "../../components/badminton-player/BadmintonPlayerClubs";
import gql from "graphql-tag";

const ModalForm = {
    components: {BadmintonPlayerClubs},
    template: `
        <form action="">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Tilføj klub</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <BadmintonPlayerClubs v-model="clubId"></BadmintonPlayerClubs>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    label="Close"
                    @click="$emit('close')"/>
                <b-button
                    :loading="loading"
                    label="Tilknyt"
                    :disabled="clubId === null"
                    @click="updateClubs"
                    type="is-primary"/>
            </footer>
        </div>
        </form>
    `,
    data() {
        return {
            loading: false,
            clubId: null
        }
    },
    methods: {
        updateClubs() {
            this.loading = true;
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation updateMe($input: UpdateMe!){
                            updateMe(input: $input){
                                id
                                clubs {
                                    id
                                    name1
                                    initialized
                                }
                            }
                        }
                    `,
                    variables: {
                        input: {
                            clubs: {
                                connect: [this.clubId]
                            }
                        }
                    }
                }
            ).then(() => {
                this.$emit('close')
                this.$buefy.snackbar.open(
                    {
                        duration: 6000,
                        type: 'is-success',
                        message: 'Klubben er tilknyttet'
                    })
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 6000,
                        type: 'is-danger',
                        message: 'Kunne ikke tilknytte klub'
                    })

            }).finally(() => {
                this.loading = false
            })
        }
    }
}

export default {
    name: "MyClubs",
    components: {ModalForm},
    apollo: {
        me: {
            query: ME,
            pollInterval: 5000
        }
    },
    methods: {
        deleteClubConnection(club) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette tilknytningen? <br /><br /> Ingen af dine holdkampe vil blive ændret',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation updateMe($input: UpdateMe!){
                                        updateMe(input: $input){
                                            id
                                            clubs {
                                                id
                                                name1
                                                initialized
                                            }
                                        }
                                    }
                                `,
                                variables: {
                                    input: {
                                        clubs: {
                                            disconnect: [club.id]
                                        }
                                    }
                                }
                            }
                        ).then(() => {
                            this.$buefy.snackbar.open(
                                {
                                    duration: 5000,
                                    type: 'is-success',
                                    message: 'Tilknytningen er nu fjernet'
                                })

                        }).catch(() => {
                            this.$buefy.snackbar.open(
                                {
                                    duration: 6000,
                                    type: 'is-danger',
                                    message: 'Kunne ikke fjerne tilknytningen'
                                })
                        })
                    }
                })

        }
    },
    data() {
        return {
            showModal: false
        }
    }
}
</script>

<style scoped>

</style>
