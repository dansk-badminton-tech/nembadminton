<template>
    <table class="table is-bordered">
        <tbody>
        <tr>
            <th>Reserver</th>
            <draggable :list="this.players" group="players" tag="td">
                <div v-for="player in this.players" :key="player.id" class="is-bordered">
                    {{ player.name }} {{ findPositions(player) }}
                </div>
            </draggable>
        </tr>
        </tbody>
    </table>
</template>
<style scoped>
.is-bordered {
    border: 1px solid #ccc;
    border-radius: 5px;
}
</style>
<script>
import Draggable from "vuedraggable"
import {findPositions} from "../helpers";

export default {
    name: 'PlayerList',
    components: {Draggable},
    props: {
        players: Array,
    },
    methods: {
        findPositions
    }
}
</script>
