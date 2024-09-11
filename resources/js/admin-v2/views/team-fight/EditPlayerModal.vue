<script>

import gql from "graphql-tag";
import {categories, debounce} from "../../helpers";
import EditPlayerAddPoint from "@/views/team-fight/EditPlayerAddPoint.vue";
import {difference, uniq} from "lodash/array.js";
import {isEmpty} from "lodash/lang.js";

export default {
    name: "EditPlayerModal",
    components: {EditPlayerAddPoint},
    props: ['player', 'version'],
    data() {
        return {
            loading: false,
            squadMember: {}
        }
    },
    computed:{
        hasMissingCategories(){
            const missingCategories1 = this.missingCategories || [];
            return missingCategories1.length > 0
        },
        missingCategories() {
            if (!isEmpty(this.squadMember)) {
                const uniqueExistingCategories = uniq(this.squadMember.points.map(item => item.category));
                return difference(categories(this.squadMember.gender), uniqueExistingCategories)
            }
        }
    },

    apollo: {
        squadMember: {
            query: gql`
                query getSquadMember($id: ID!) {
                    squadMember(id: $id) {
                        id
                        name
                        gender
                        vintage
                        points {
                            id
                            points
                            category
                            position
                            vintage
                        }
                    }
                }
            `,
            variables() {
                return {
                    id: this.player.id
                }
            }
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
                <p class="modal-card-title">Rediger <strong>{{ squadMember.name }}</strong> points</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <b-message type="is-warning">Ændringerne er kun lokale, så pointene ændres kun for denne spiller i denne kategori. Husk at opdatere pointene for samme spiller i anden kategori. En manuelt redigeret spiller er markeret med <b-icon type="is-info" icon="information" /></b-message>
                <hr/>
                <b-field v-for="innerPoints in squadMember.points" :key="innerPoints.id" :label="resolveCategoryName(innerPoints.category)">
                    <b-input
                        type="number"
                        @input="updatePoint(innerPoints, $event)"
                        :value="innerPoints.points"
                        required>
                    </b-input>
                </b-field>
                <EditPlayerAddPoint v-if="hasMissingCategories" :version="this.version" :missing-categories="missingCategories" :player="squadMember"/>
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

