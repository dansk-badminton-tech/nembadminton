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
            titleStack: ['Admin', 'Rediger'],
            submitting: false,
            email: '',
            clubs: [],
            filteredClubs: [],
            noNotification: false
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
                this.noNotification = this.email === null;
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
                                            email: this.noNotification ? null : this.email,
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
                this.$buefy.toast.open({message: `Fejl: Kunne ikke opdater afbudslink`, type: "is-danger", duration: 5000})
            }).finally(() => {
                this.submitting = false
            })
        }
    },
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Afbuds indsamling
        </hero-bar>
        <section class="section is-main-section">
            <b-loading v-model="$apollo.loading" :is-full-page="false"></b-loading>
            <form @submit.prevent="updateCancellation">
                <b-field addons label="Email" message="Email til notifikationer når et afbud modtages">
                    <div class="control">
                      <b-checkbox v-model="noNotification">Ingen notifikationer</b-checkbox>
                    </div>
                    <div class="control is-expanded">
                      <b-input :disabled="noNotification" type="email" v-model="email"></b-input>
                    </div>
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
