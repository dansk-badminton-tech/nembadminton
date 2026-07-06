<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Vent...
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

<script>
import {defineComponent} from 'vue'
import TitleBar from '@/components/TitleBar.vue'
import HeroBar from '@/components/HeroBar.vue'

export default defineComponent(
    {
        name: 'HomeView',
        components: {HeroBar, TitleBar},
        inject: ['clubhouseId', 'user'],
        data() {
            return {
                titleStack: ['Videre', 'stiller']
            }
        },
        watch: {
            clubhouseId(newVal, oldVal) {
                if (Number.isInteger(parseInt(newVal))) {
                    this.$router.replace({name: 'home', params: {clubhouseId: newVal}});
                } else {
                    console.warn('clubhouseId is NOT an integer:', newVal);
                }
            }
        }
    })
</script>
