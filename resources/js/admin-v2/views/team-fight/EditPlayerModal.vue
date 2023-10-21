<script>

import gql from "graphql-tag";
import {debounce} from "../../helpers";

export default {
    name: "EditPlayerModal",
    props: ['value'],
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
                <p class="modal-card-title">Rediger <strong>{{ value.name }}</strong> points</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <p>Ændringerne er kun lokal. Så pointene ændres kun for denne spiller i denne kategori. Husk at opdater pointene for samme spiller i anden kategori. Manuel redigeret spiller er markeret med <b-icon icon="information" /></p>
                <hr/>
                <b-field v-for="points in value.points" :key="points.id" :label="resolveCategoryName(points.category)">
                    <b-input
                        type="number"
                        @input="updatePoint(points, $event)"
                        v-model="points.points"
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
