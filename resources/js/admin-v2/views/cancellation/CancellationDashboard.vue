<script>
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";
import 'vue-cal/dist/vuecal.css'
import cancellationCollectorQuery from "../../../queries/cancellationCollector.gql";
import CancellationCollector from "@/views/cancellation/CancellationCollector.vue";
import gql from "graphql-tag";
import TeamMatchCalendar from "@/views/calendar/TeamMatchCalendar.vue";
import ME from "../../../queries/me.gql";

const nowPlus14Days = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000);
export default {
    name: "CancellationDashboard",
    props: {
        collectorId: String
    },
    data: () => {
        return {
            titleStack: ['Admin', 'Afbud'],
            isDeleting: false,
            me: {
                clubs: []
            },
            selectedDateRange: [new Date(), nowPlus14Days]
        }
    },
    components: {TeamMatchCalendar, CancellationCollector, TitleBar, HeroBar},
    apollo: {
        cancellationCollector: {
            query: cancellationCollectorQuery,
            variables() {
                return {
                    dateRange: this.selectedDateRange,
                    id: this.collectorId
                }
            }
        },
        me: {
            query: ME
        }
    },
    methods: {
        confirmDeleteCancellationCollector() {
            this.$buefy.dialog.confirm({
                                           message: 'Sikker på du vil slette? Alle afbud bliver slettet.',
                                           onConfirm: () => this.deleteCancellationCollector()
                                       })
        },
        deleteCancellationCollector() {
            this.isDeleting = true;
            this.$apollo.mutate({
                                    mutation: gql`
                  mutation DeleteCancellationCollector($id: ID!) {
                    deleteCancellationCollector(id: $id) {
                        id
                    }
                  }
                `,
                                    variables: {
                                        id: this.cancellationCollector.id
                                    },
                                    refetchQueries: [{query: cancellationCollectorQuery}, {query: ME}]
                                }).then(() => {
                this.$buefy.toast.open({
                                           message: 'Cancellation collector deleted successfully',
                                           type: 'is-success'
                                       });
                this.$router.push({name: "cancellation-redirect"})
            }).catch(error => {
                console.error('Error deleting cancellation collector:', error);
                this.$buefy.toast.open({
                                           message: 'An error occurred while deleting the cancellation collector',
                                           type: 'is-danger'
                                       });
            }).finally(() => {
                this.isDeleting = false;
            });
        }
    },
    computed: {
        hasCancellationLink() {
            return this.cancellationCollector !== null
        },
        showClubNames() {
            if (this.me.clubs.length === 0) {
                return "Ingen klubber"
            }
            return this.me.clubs.map(club => club.name1).join(", ")
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Afbuds indsamling
        </hero-bar>
        <section class="section is-main-section">
            <b-button
                v-if="hasCancellationLink"
                tag="router-link"
                :to="'/cancellations/edit/'+collectorId">Rediger
            </b-button>
            <div v-else class="box has-text-centered">
                <h2 class="title is-4">Kom igang</h2>
                <p>Indsammel afbud for holdturneringen</p>
                <div class="buttons is-centered">
                    <b-button tag="router-link" to="/cancellations/create" class="button is-link">Opret nu</b-button>
                </div>
            </div>
            <b-button
                v-if="hasCancellationLink"
                :loading="isDeleting"
                @click="confirmDeleteCancellationCollector">
                Slet
            </b-button>
            <hr>
            <CancellationCollector v-if="!!cancellationCollector" :cancellationCollector="cancellationCollector"/>
            <hr>
            <div v-if="!!cancellationCollector">
                <h1 class="title is-3">Afbud</h1>
                <b-field label="Søg på afbuds datoer">
                    <b-datepicker
                        placeholder="Søg på afbuds datoer"
                        v-model="selectedDateRange"
                        range>
                    </b-datepicker>
                </b-field>
                <b-table
                    :data="cancellationCollector?.cancellationPublic.data || []"
                    :narrowed="true"
                    :per-page="10">
                    <b-table-column field="createdAt" label="Oprettet" sortable v-slot="props">
                        {{ props.row.createdAt }}
                    </b-table-column>

                    <b-table-column field="member.name" label="Navn" sortable v-slot="props">
                        {{ props.row.member.name }}
                    </b-table-column>

                    <b-table-column field="dates" label="Afbuds datoer" sortable v-slot="props">
                        {{props.row.dates.map(d => d.date).join(", ")}}
                    </b-table-column>
                    <b-table-column field="message" label="Besked" sortable v-slot="props">
                        {{ props.row.message }}
                    </b-table-column>
                    <template #empty>
                        <p>Ingen afbud fundet</p>
                    </template>
                </b-table>
                <hr>
                <h1 class="title is-3">Holdkamp kalender (Beta)</h1>
                <h2 class="subtitle">Viser alle holdkampe for {{showClubNames}} senior for mine klubber. Data'en er hentet direkte fra badmintonplayer.dk</h2>
                <TeamMatchCalendar :clubs="me.clubs"  />
            </div>
        </section>
    </div>
</template>
