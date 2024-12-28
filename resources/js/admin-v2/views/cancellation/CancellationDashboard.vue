<script>
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";
import 'vue-cal/dist/vuecal.css'
import cancellationCollectorQuery from "../../../queries/cancellationCollector.gql";
import CancellationCollector from "@/views/cancellation/CancellationCollector.vue";
import gql from "graphql-tag";
import TeamMatchCalendar from "@/views/calendar/TeamMatchCalendar.vue";
import ME from "../../../queries/me.gql";
import moment from "moment";

const now = new Date()
now.setHours(0, 0, 0, 0)
const nowPlus14Days = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000);
nowPlus14Days.setHours(0, 0, 0, 0)
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
            selectedDateRange: null,
            perPage: 10,
            currentPage: 1,
            orderBy: [{
                column: 'CREATED_AT',
                order: 'DESC'
            }]
        }
    },
    components: {TeamMatchCalendar, CancellationCollector, TitleBar, HeroBar},
    apollo: {
        cancellationCollector: {
            query: cancellationCollectorQuery,
            variables() {
                return this.resolveVariables()
            },
            fetchPolicy: 'network-only',
        },
        me: {
            query: ME
        }
    },
    methods: {
        onSort(field, order) {
            this.orderBy = [
                {
                    column: 'CREATED_AT',
                    order: order.toUpperCase()
                }
            ]
        },
        resolveVariables() {
            const vars = {
                id: this.collectorId,
                hasDates: null,
                orderBy: this.orderBy,
                page: this.currentPage,
                first: this.perPage,
            };

            if (this.selectedDateRange !== null) {
                vars.hasDates = {
                    column: 'DATE',
                    operator: 'BETWEEN',
                    value: [
                        moment(this.selectedDateRange[0]).format('YYYY-MM-DDTHH:mm:ss'),
                        moment(this.selectedDateRange[1]).format('YYYY-MM-DDTHH:mm:ss')
                    ]
                };
            }
            return vars;
        },
        confirmDeleteCancellation(cancellation) {
            const variables = this.resolveVariables()
            this.$buefy.dialog.confirm(
                {
                    message: 'Er du sikker på, at du vil slette dette afbud?',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                                  mutation DeleteCancellation($id: ID!) {
                                                    deleteCancellation(id: $id) {
                                                        id
                                                    }
                                                  }
                                                `,
                                variables: {
                                    id: cancellation.id
                                },
                                refetchQueries: [{query: cancellationCollectorQuery, variables: variables}]
                            })
                            .then(() => {
                                this.$buefy.toast.open({
                                                           message: 'Afbud slettet',
                                                           type: 'is-success'
                                                       });
                            })
                            .catch(error => {
                                console.error('Error deleting cancellation:', error);
                                this.$buefy.toast.open({
                                                           message: 'Der opstod en fejl under sletningen af afbuddet.',
                                                           type: 'is-danger'
                                                       });
                            });
                    }
                });
        },
        resetSelectedDateRange() {
            this.selectedDateRange = null
        },
        confirmDeleteCancellationCollector() {
            this.$buefy.dialog.confirm({
                                           message: 'Er du sikker på, at du vil slette afbudslinket og alle afbud registreret af spillerne?',
                                           onConfirm: () => this.deleteCancellationCollector(),
                                        cancelText: 'Fortryd'
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
                this.$router.push({name: "cancellation-landing"})
            }).catch(error => {
                console.error('Error deleting cancellation collector:', error);
                this.$buefy.toast.open({
                                           message: 'An error occurred while deleting the cancellation collector',
                                           type: 'is-danger'
                                       });
            }).finally(() => {
                this.isDeleting = false;
            });
        },
        showMessage(row) {
            this.$buefy.dialog.alert({
                                         title: 'Besked',
                                         message: row.message || 'Ingen besked tilgængelig.',
                                         confirmText: 'OK',
                                         type: 'is-info',
                                         canCancel: true
                                     });
        },
        showEmail(row) {
            this.$buefy.dialog.alert({
                                         title: 'Email som har meldt afbud',
                                         message: row.email || 'Ingen email tilgængelig.',
                                         confirmText: 'OK',
                                         type: 'is-info',
                                         canCancel: true
                                     });
        },
        selectedDateRangeInput(input){
            const final = [input[0]]
            final[1] = new Date(input[1].getTime() + 24 * 60 * 60 * 1000 - 1);
            this.selectedDateRange = final
        }
    },
    computed: {
        hasCancellationLink() {
            return this.cancellationCollector !== null
        },
        showClubNames() {
            if (this.cancellationCollector?.clubs.length === 0) {
                return "Ingen klubber"
            }
            return this.cancellationCollector?.clubs.map(club => club.name1).join(", ")
        },
        showSelectedDateRange() {
            if (this.selectedDateRange === null) {
                return ''
            }
            return 'Søgte mellem ' + this.selectedDateRange?.map(d => d.toLocaleDateString()).join(" - ")
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <section class="section is-main-section">
            <b-button
                v-if="hasCancellationLink"
                tag="router-link"
                :to="'/cancellations/edit/'+collectorId">Rediger
            </b-button>
            <b-button
                v-if="hasCancellationLink"
                :loading="isDeleting"
                @click="confirmDeleteCancellationCollector"
                class="ml-2"
            >
                Slet
            </b-button>
            <hr>
            <CancellationCollector v-if="!!cancellationCollector" :cancellationCollector="cancellationCollector"/>
            <hr>
            <div v-if="!!cancellationCollector">
                <h1 class="title is-3">Oversigt over afbud</h1>
                <b-table
                    :data="cancellationCollector?.cancellations.data || []"
                    :narrowed="true"
                    :loading="$apollo.queries.cancellationCollector.loading"
                    :paginated="true"
                    :backend-pagination="true"
                    :total="cancellationCollector?.cancellations.paginatorInfo.total"
                    :per-page="perPage"
                    @page-change="page => this.currentPage = page"
                    backend-sorting
                    :default-sort="['createdAt', 'desc']"
                    @sort="onSort"
                >
                    <b-table-column field="createdAt" label="Oprettet" sortable v-slot="props">
                        {{ props.row.createdAt }}
                    </b-table-column>

                    <b-table-column field="member.name" label="Navn" v-slot="props">
                        {{ props.row.member.name }}
                    </b-table-column>

                    <b-table-column field="member.clubs" label="Klub" v-slot="props">
                        {{ props.row.member.clubs.map(c => c.name1).join(", ") }}
                    </b-table-column>

                    <b-table-column searchable field="dates" label="Afbudsdatoer">
                        <template
                            v-slot:searchable="props">
                            <b-field grouped>
                                <b-datepicker
                                    placeholder="Søg på afbudsdatoer"
                                    :value="selectedDateRange"
                                    @input="selectedDateRangeInput"
                                    size="is-small"
                                    :first-day-of-week="1"
                                    locale="da-DK"
                                    range>
                                </b-datepicker>
                                <p class="control">
                                    <b-button size="is-small" @click="resetSelectedDateRange">Nulstil</b-button>
                                </p>
                            </b-field>
                        </template>
                        <template v-slot="props">
                            {{ props.row.dates.map(d => d.date).join(", ") }}
                        </template>
                    </b-table-column>
                    <b-table-column v-slot="props">
                        <div class="buttons">
                            <b-button
                                icon-left="message"
                                size="is-small"
                                @click="showMessage(props.row)"
                                aria-label="Vis besked"
                                title="Vis besked"
                                v-if="props.row.message"
                            />
                            <b-button
                                icon-left="email"
                                size="is-small"
                                @click="showEmail(props.row)"
                                aria-label="Vis email"
                                title="Vis email"
                            />
                            <b-button
                                icon-left="delete"
                                type="is-danger"
                                size="is-small"
                                @click="confirmDeleteCancellation(props.row)"
                                aria-label="Slet afbud"
                                title="Slet afbud"
                            />
                        </div>
                    </b-table-column>
                    <template v-slot:empty="props">
                        <p>Ingen afbud fundet. {{ showSelectedDateRange }}</p>
                    </template>
                </b-table>
                <hr>
                <h1 class="title is-3">Kalender for holdkampe (Beta)</h1>
                <h2 class="subtitle">Viser alle holdkampe for {{ showClubNames }}. Dataen er hentet direkte fra badmintonplayer.dk.</h2>
                <TeamMatchCalendar :cancellation-collector="cancellationCollector" :clubs="cancellationCollector?.clubs"/>
            </div>
        </section>
    </div>
</template>
