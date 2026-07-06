<script>

import ClubDashboard from "@/views/dashboard/ClubDashboard.vue";
import PlayerDashboard from "@/views/PlayerDashboard.vue";

export default {
    name: "Main",
    components: {ClubDashboard, PlayerDashboard},
    inject: ['user'],
    computed: {
        isPlayer(){
            return this.user?.primaryRole.name === 'player'
        }
    },
}

</script>

<template>
    <PlayerDashboard v-if="isPlayer" />
    <ClubDashboard v-else />
</template>

<style scoped>

</style>
