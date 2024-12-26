<script>
import cancellationCollectorQuery from "../../../queries/cancellationCollector.gql";
import gql from "graphql-tag";
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import ME from "../../../queries/me.gql";

export default {
    name: "UpdateCancellationCollector",
    components: {HeroBar, TitleBar},
    props: {
        collectorId: String
    },
    data() {
        return {
            titleStack: ['Afbuds indsamling', 'Opdater'],
            submitting: false,
            email: '',
            clubs: [],
            filteredClubs: [],
        }
    },
    apollo: {
        me: {
            query: ME,
            result({data}) {
                this.filteredClubs = data.me.clubs
            }
        },
        cancellationCollector: {
            query: gql`
                query cancellationCollector($id: ID!){
                    cancellationCollector(id: $id){
                        id
                        email
                        clubs {
                            id
                            name1
                        }
                        createdAt
                        updatedAt
                    }
                }
            `,
            variables() {
                return {
                    id: this.collectorId
                }
            },
            result({data}) {
                // Set the email from the fetched data
                this.clubs = data.cancellationCollector.clubs;
                this.email = data.cancellationCollector.email;
            },
            fetchPolicy: "network-only"
        }
    },
    methods: {
        getFilteredClubs(text) {
            this.filteredClubs = this.me.clubs.filter((option) => {
                return option.name1
                             .toString()
                             .toLowerCase()
                             .indexOf(text.toLowerCase()) >= 0
            })
        },
        updateCancellation() {
            this.submitting = true;
            this.$apollo.mutate({
                                    mutation: gql`
                                        mutation ($id: ID!, $input: CancellationCollectionInput!){
                                            updateCancellationCollector(id: $id, input: $input){
                                                id
                                                email
                                                sharingId
                                                updatedAt
                                                createdAt
                                            }
                                        }
                                    `,
                                    variables: {
                                        id: this.cancellationCollector.id,
                                        input: {
                                            email: this.email,
                                            clubs: {
                                                sync: this.clubs.map(c => c.id)
                                            }
                                        }
                                    },
                                    refetchQueries: [
                                        {query: cancellationCollectorQuery},
                                        {query: ME}
                                    ]
                                }
            ).then(() => {
                this.$router.push({name: 'cancellation-view', params: {collectorId: this.cancellationCollector.id}})
            }).catch(() => {
                this.$buefy.toast.open({message: `Fejl: Kunne ikke lave afbudslink`, type: "is-danger", duration: 5000})
            }).finally(() => {
                this.submitting = false
            })
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
            <form @submit.prevent="updateCancellation">
                <b-field label="Email" message="Email til notifikationer når et afbud modtages">
                    <b-input v-model="email"></b-input>
                </b-field>
                <b-field label="Clubs">
                    <b-taginput
                        @typing="getFilteredClubs"
                        v-model="clubs"
                        :data="filteredClubs"
                        autocomplete
                        ellipsis
                        field="name1"
                        aria-close-label="Delete this tag">
                    </b-taginput>
                </b-field>
                <b-button :loading="submitting" native-type="submit">Opdater</b-button>
            </form>
        </section>
    </div>
</template>

<style scoped>

</style>
