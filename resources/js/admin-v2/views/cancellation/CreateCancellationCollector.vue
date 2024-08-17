<script>
import gql from "graphql-tag";

import cancellationCollectorQuery from '@/../queries/cancellationCollector.gql';
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";

export default {
    name: "CreateCancellationCollector",
    components: {HeroBar, TitleBar},
    data() {
        return {
            submitting: false,
            email: '',
            titleStack: ['Afbuds indsamling']
        }
    },
    methods: {
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
                                            email: this.email
                                        }
                                    },
                                    refetchQueries: [
                                        {query: cancellationCollectorQuery}
                                    ]
                                }).then(() => {
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
            <form @submit.prevent="submitCancellation">
                <b-field label="Email" message="Når der meldes afbud sendes der en email til denne email samt en kvitering til den som melder afbud">
                    <b-input v-model="email" type="email" required></b-input>
                </b-field>
                <b-button :loading="submitting" native-type="submit">Opret afbuds link</b-button>
            </form>
        </section>
    </div>
</template>

<style scoped>

</style>
