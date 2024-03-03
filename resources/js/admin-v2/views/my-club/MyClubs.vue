<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Mine klubber
        </hero-bar>
        <section class="section is-main-section">
            <h2 class="subtitle">Tilknyt flere klubber til din profil og sammensæt holdkampe på tværs af klubber</h2>
            <b-button type="is-success" @click="openModal">Tilføj klub</b-button>
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
        </section>
    </div>
</template>

<script>
import ME from "../../../queries/me.gql";
import gql from "graphql-tag";
import HeroBar from "../../components/HeroBar.vue";
import TitleBar from "../../components/TitleBar.vue";
import AddClubModel from "@/views/my-club/AddClubModel.vue";

export default {
    name: "MyClubs",
    components: {TitleBar, HeroBar},
    apollo: {
        me: {
            query: ME,
            pollInterval: 5000
        }
    },
    methods: {
        openModal(){
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
