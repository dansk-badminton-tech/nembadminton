<script>
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";
import 'vue-cal/dist/vuecal.css'
import cancellationCollectorQuery from "../../../queries/cancellationCollector.gql";
import CancellationCollector from "@/views/cancellation/CancellationCollector.vue";
import gql from "graphql-tag";

export default {
    name: "CancellationDashboard",
    data: () => {
        return {
            titleStack: ['Admin', 'Afbud'],
            isDeleting: false
        }
    },
    components: {CancellationCollector, TitleBar, HeroBar},
    apollo: {
        cancellationCollector: {
            query: cancellationCollectorQuery
        }
    },
    methods: {
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
                                    refetchQueries: [{query: cancellationCollectorQuery}]
                                }).then(() => {
                this.$buefy.toast.open({
                                           message: 'Cancellation collector deleted successfully',
                                           type: 'is-success'
                                       });
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
                to="/cancellations/edit">Rediger
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
                @click="deleteCancellationCollector">
                Slet Afbud Indsamling
            </b-button>
            <hr>
            <CancellationCollector v-if="!!cancellationCollector" :cancellationCollector="cancellationCollector"/>
        </section>
    </div>
</template>
