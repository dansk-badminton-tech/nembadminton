<script>

import gql from "graphql-tag";
import {debounce} from "../../helpers";

export default {
    name: "EditPlayerModal",
    props: ['player'],
    data() {
        return {
            loading: false
        }
    },
    methods: {
        resolveCategoryName(category) {
            if (category === null) {
                return 'Niveau'
            }
            return category
        },
        updatePoint: debounce(function (point, value) {
            this.loading = true
            this.$apollo
                .mutate({
                            mutation: gql`
                                mutation updateSquadPoint($input: UpdateSquadPointInput!){
                                    updateSquadPoint(input: $input){
                                        id
                                        points
                                        position
                                        category
                                        vintage
                                        corrected_manually
                                    }
                                }
                            `,
                            variables: {
                                input: {
                                    id: point.id,
                                    points: parseInt(value),
                                    corrected_manually: true
                                }
                            }
                        })
                .finally(() => {
                    this.loading = false
                })
        }, 500)
    }
}
</script>

<template>
    <form action="">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Rediger <strong>{{ player.name }}</strong> points</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <b-message type="is-warning">Ændringerne er kun lokale, så pointene ændres kun for denne spiller i denne kategori. Husk at opdatere pointene for samme spiller i anden kategori. En manuelt redigeret spiller er markeret med <b-icon type="is-info" icon="information" /></b-message>
                <hr/>
                <b-field v-for="innerPoints in player.points" :key="innerPoints.id" :label="resolveCategoryName(innerPoints.category)">
                    <b-input
                        type="number"
                        @input="updatePoint(innerPoints, $event)"
                        :value="innerPoints.points"
                        required>
                    </b-input>
                </b-field>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    label="Luk"
                    :loading="loading"
                    :disabled="loading"
                    @click="$emit('close')"/>
            </footer>
        </div>
    </form>
</template>

<style scoped>

</style>
