<script>
import cancellationCollectorQuery from "../../../queries/cancellationCollector.gql";
import gql from "graphql-tag";
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";

export default {
    name: "UpdateCancellationCollector",
    components: {HeroBar, TitleBar},
    props: {
        id: Number
    },
    data() {
        return {
            submitting: false,
            email: ''
        }
    },
    apollo: {
        cancellationCollector: {
            query: cancellationCollectorQuery,
            result({data}) {
                // Set the email from the fetched data
                this.email = data.cancellationCollector.email;
            }
        }
    },
    methods: {
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
                                            email: this.email
                                        }
                                    },
                                    refetchQueries: [
                                        {query: cancellationCollectorQuery}
                                    ]
                                }
            ).then(() => {
                this.$router.push({name: 'cancellation-dashboard'})
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
                <b-field label="Email" message="Når der meldes afbud sendes der en email til denne email samt en kvitering til den som melder afbud">
                    <b-input v-model="email"></b-input>
                </b-field>
                <b-button :loading="submitting" native-type="submit">Opdater</b-button>
            </form>
        </section>
    </div>
</template>

<style scoped>

</style>
