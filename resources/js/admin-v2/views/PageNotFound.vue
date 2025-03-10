<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";

export default {
    name: "PageNotFound",
    components: {HeroBar, TitleBar},
    inject: ['clubhouseId'],
    data(){
        return {
            titleStack: ['Admin', '404']
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Page not found
        </hero-bar>
        <div class="section">
            <b-button
                tag="router-link"
                :to="'/c-'+clubhouseId+'/home'"
                type="is-link"
            >Gå til dashboard</b-button>
        </div>
    </div>
</template>

<style scoped>

</style>
