<template>
    <card-component
        title="Tilknyttet klubber"
        icon="home"
    >
        <h2 class="subtitle">Tilknyt flere klubber til dit klubhus og sammensæt holdrunder på tværs af klubber</h2>
        <b-button type="is-info" @click="openModal">Tilknyt klub</b-button>
        <b-table :data="me?.clubs">
            <b-table-column field="id" label="ID" width="40" numeric v-slot="props">
                {{ props.row.id }}
            </b-table-column>
            <b-table-column field="name1" label="Name" v-slot="props">
                {{ props.row.name1 }} {{
                    (me?.club?.id === props.row.id
                     ? '(Hovedklub)'
                     : '')
                }}
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
    </card-component>
</template>

<script>
import ME from "../../../queries/me.gql";
import gql from "graphql-tag";
import HeroBar from "../../components/HeroBar.vue";
import TitleBar from "../../components/TitleBar.vue";
import AddClubModel from "@/views/club-house/AddClubModel.vue";
import CardComponent from "@/components/CardComponent.vue";

export default {
    name: "MyClubs",
    components: {CardComponent, TitleBar, HeroBar},
    apollo: {
        me: {
            query: ME,
            pollInterval: 0
        }
    },
    methods: {
        openModal() {
            this.$buefy.modal.open({
                                       parent: this,
                                       component: AddClubModel,
                                       scroll: "keep",
                                       width: 640,
                                       events: {
                                           close: () => {
                                               this.$emit('input', false)
                                           }
                                       }
                                   })
        },
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
            titleStack: ['Admin', 'Mine klubber'],
            showModal: false
        }
    }
}
</script>

<style scoped>

</style>
