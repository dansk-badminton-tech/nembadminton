<template>
    <div class="row">
        <div class="tile is-ancestor">
            <draggable :list="court.left" :swap="true" class="tile is-parent is-vertical" group="courts" tag="div">
                <article v-for="player in court.left" :key="player.id" class="tile is-child box">
                    <p class="subtitle">{{ player.name }}</p>
                    <b-button @click="removePlayer(court, player)">Slet</b-button>
                </article>
            </draggable>
            <draggable :list="court.right" :swap="true" class="tile is-parent is-vertical" group="courts" tag="div">
                <article v-for="player in court.right" :key="player.id" class="tile is-child box">
                    <p class="subtitle">{{ player.name }}</p>
                    <b-button @click="removePlayer(court, player)">Slet</b-button>
                </article>
            </draggable>
        </div>
    </div>
</template>
<style scoped>
.border {
    border: 2px solid #73AD21;
}

.round-corners {
    border-radius: 25px;
    border: 2px solid #73AD21;
    padding: 20px;
}
</style>
<script>
import draggable from "vuedraggable";

export default {
    name: "Court",
    display: "Two Lists",
    order: 1,
    filters: {
        filterNull(side) {
            return side
        }
    },
    components: {
        draggable
    },
    props: {
        court: Object,
        removePlayer: Function
    }
};
</script>
