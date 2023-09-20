<script>

import gql from "graphql-tag";
import {debounce} from "../../helpers";

export default {
    name: "EditPlayerModal",
    props: ['value'],
    methods: {
        resolveCategoryName(category){
            if(category === null){
                return 'Niveau'
            }
            return category
        },
        updatePoint: debounce(function(point, value){
            console.log(point)
            console.log(value)
            this.$apollo.mutate({
                                    mutation: gql`
                                mutation updateSquadPoint($input: UpdateSquadPointInput!){
                                    updateSquadPoint(input: $input){
                                        id
                                        points
                                        position
                                        category
                                        vintage
                                    }
                                }
                            `,
                                    variables: {
                                        input: {
                                            id: point.id,
                                            points: parseInt(value)
                                        }
                                    }
                                })
        }, 500)
    }
}
</script>

<template>
    <form action="">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Rediger {{value.name}}</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <p>Ændringen </p>
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
                    label="Close"
                    @click="$emit('close')" />
            </footer>
        </div>
    </form>
</template>

<style scoped>

</style>
