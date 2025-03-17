<script>
import {computed} from "vue";

export default {
    name: "Default",
    props: ['clubhouseId'],
    provide(){
        return {
            clubhouseId: computed(() => parseInt(this.clubhouseId) ?? null)
        }
    }
}
</script>

<template>
    <router-view></router-view>
</template>

<style scoped>

</style>
