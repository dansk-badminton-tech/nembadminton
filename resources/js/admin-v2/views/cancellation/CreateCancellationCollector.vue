<script>
import gql from "graphql-tag";

import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import ME from "../../../queries/me.gql";

export default {
    name: "CreateCancellationCollector",
    components: {HeroBar, TitleBar},
    data() {
        return {
            filteredClubs: [],
            submitting: false,
            email: '',
            clubs: [],
            titleStack: ['Admin', 'Afbud'],
            me: {
                clubs: []
            },
            noNotification: false
        }
    },
    apollo: {
        me: {
            query: ME,
            result({data}) {
                this.clubs = data.me.clubs
                this.filteredClubs = data.me.clubs
            }
        }
    },
    methods: {
        getFilteredClubs(text) {
            this.filteredClubs = this.me.clubhouse.clubs.filter((option) => {
                return option.name1
                             .toString()
                             .toLowerCase()
                             .indexOf(text.toLowerCase()) >= 0
            })
        },
        submitCancellation() {
            this.submitting = true;
            this.$apollo.mutate({
                                    mutation: gql`
                                        mutation ($input: CancellationCollectionInput!){
                                            createCancellationCollector(input: $input){
                                                id
                                                email
                                                sharingId
                                                updatedAt
                                                createdAt
                                            }
                                        }
                                    `,
                                    variables: {
                                        input: {
                                            email: this.noNotification ? null : this.email,
                                            clubs: {
                                                connect: this.clubs.map(c => c.id)
                                            }
                                        }
                                    },
                                    refetchQueries: [
                                        {query: ME}
                                    ]
                                })
                .then(({data}) => {
                this.$router.push({name: 'cancellation-view', params: {collectorId: data.createCancellationCollector.id}})
            }).catch(() => {
                this.$buefy.toast.open({message: `Fejl: Kunne ikke oprette afbudslink`, type: "is-danger", duration: 5000})
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
            Opret afbudslink
        </hero-bar>
        <section class="section is-main-section">
            <form @submit.prevent="submitCancellation">
                <b-field addons label="Email" message="Angiv email som notifikationer må sendes til, når et afbud modtages.">
                    <div class="control">
                        <b-checkbox v-model="noNotification">Ingen notifikationer</b-checkbox>
                    </div>
                    <div class="control is-expanded">
                        <b-input :disabled="noNotification" placeholder="fx daniel@gmail.com" v-model="email"></b-input>
                    </div>
                </b-field>
                <b-field label="Klubber">
                    <b-taginput
                        @typing="getFilteredClubs"
                        v-model="clubs"
                        :data="filteredClubs"
                        autocomplete
                        ellipsis
                        open-on-focus
                        field="name1">
                    </b-taginput>
                </b-field>
                <b-button :loading="submitting" native-type="submit">Opret</b-button>
            </form>
        </section>
    </div>
</template>

<style scoped>

</style>
