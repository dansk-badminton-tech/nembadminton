<script>
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";
import HighestPointGainChart from "@/views/dashboard/HighestPointGainChart.vue";
import {getCurrentSeasonStart} from "@/helpers.js";

export default {
    name: "Analytics" ,
    components: {HighestPointGainChart, TitleBar, HeroBar},
    data: () => {
        return {
            titleStack: ['Admin', 'Analytics'],
            category: 'HS',
            limit: 10,
            orderBy: 'DESC'
        }
    },
    computed: {
        currentSeason(){
            let currentYear = getCurrentSeasonStart().getFullYear();
            return currentYear+"/"+(currentYear+1)
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Analytics
        </hero-bar>
        <div class="section">
            <h1 class="title">Netto stigning i ranglistepoint pr. spiller for {{currentSeason}}</h1>
            <b-field grouped grouped-multiline>
                <b-field label="Sortering">
                    <b-select v-model="orderBy">
                        <option value="DESC">Høj til lav</option>
                        <option value="ASC">Lav til høj</option>
                    </b-select>
                </b-field>
                <b-field label="Kategori">
                    <b-select v-model="category">
                        <option value="HS">Herre single</option>
                        <option value="DS">Dame single</option>
                        <option value="HD">Herre double</option>
                        <option value="DD">Dame double</option>
                        <option value="MxH">Herre Mix</option>
                        <option value="MxD">Dame Mix</option>
                    </b-select>
                </b-field>
                <b-field label="Antal spiller">
                    <b-numberinput step="5" v-model="limit" />
                </b-field>
            </b-field>
            <HighestPointGainChart :category="category" :limit="limit" :orderBy="orderBy" />
        </div>
    </div>
</template>

<style scoped>

</style>
