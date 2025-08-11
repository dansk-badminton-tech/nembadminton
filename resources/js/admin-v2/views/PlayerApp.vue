<script>
import { computed, defineComponent } from "vue";
import NavBar from "@/components/NavBar.vue";
import AsideMenu from "@/components/AsideMenu.vue";
import menu from '@/playerMenu.js'

export default defineComponent(
    {
        name: "PlayerApp",
        components: { AsideMenu, NavBar },
        props: {clubhouseId: String},
        computed: {
            menu(){
                return menu(this.clubhouseId)
            }
        },
        provide() {
            return {
                clubhouseId: computed(() => parseInt(this.clubhouseId) ?? null),
            }
        },
    }
);
</script>

<template>
    <div id="app">
        <nav-bar :hide-right-nav="true"/>
        <aside-menu title="Spillerportal" :menu="menu"/>
        <router-view/>
    </div>
</template>

<style scoped>

</style>
