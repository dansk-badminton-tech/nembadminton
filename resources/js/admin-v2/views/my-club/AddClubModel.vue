<script>
import gql from "graphql-tag";
import BadmintonPlayerClubs from "../../../components/badminton-player/BadmintonPlayerClubs.vue";

export default {
    name: "AddClubModel",
    components: {BadmintonPlayerClubs},
    data() {
        return {
            loading: false,
            clubId: null
        }
    },
    methods: {
        updateClubs() {
            this.loading = true;
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
                                connect: [this.clubId]
                            }
                        }
                    }
                }
            ).then(() => {
                this.$emit('close')
                this.$buefy.snackbar.open(
                    {
                        duration: 6000,
                        type: 'is-success',
                        message: 'Klubben er tilknyttet'
                    })
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 6000,
                        type: 'is-danger',
                        message: 'Kunne ikke tilknytte klub'
                    })

            }).finally(() => {
                this.loading = false
            })
        }
    }
}
</script>

<template>
    <form action="">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Tilføj klub</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <BadmintonPlayerClubs v-model="clubId"></BadmintonPlayerClubs>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    label="Close"
                    @click="$emit('close')"/>
                <b-button
                    :loading="loading"
                    label="Tilknyt"
                    :disabled="clubId === null"
                    @click="updateClubs"
                    type="is-primary"/>
            </footer>
        </div>
    </form>
</template>

<style scoped>

</style>
