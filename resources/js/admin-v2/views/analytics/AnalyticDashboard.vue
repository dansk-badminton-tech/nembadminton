<script>
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";
import HighestPointGainChart from "@/views/dashboard/HighestPointGainChart.vue";
import CategoryPoints from "@/views/dashboard/CategoryPoints.vue";
import {getCurrentSeasonStart} from "@/helpers.js";

export default {
    name: "Analytics" ,
    components: {CategoryPoints, HighestPointGainChart, TitleBar, HeroBar},
    inject: ['clubhouseId'],
    data: () => {
        return {
            titleStack: ['Admin', 'Analytics'],
            activeTab: 0,
            category: 'HS',
            limit: 10,
            orderBy: 'DESC',
            selectedVintage: ["SEN"],
            vintages: [
                "SEN",
                "U19",
                "U17",
                "U15",
                "U13",
                "U11",
                "U9"
            ]
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
            <b-field group-multiline grouped>
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
                <b-field label="Række">
                    <b-checkbox-button v-for="vintage in vintages" :key="vintage" v-model="selectedVintage" :native-value="vintage" type="is-info">
                        <span>{{vintage}}</span>
                    </b-checkbox-button>
                </b-field>
            </b-field>
            <HighestPointGainChart :clubhouse-id="clubhouseId" :category="category" :limit="limit" :orderBy="orderBy" :vintages="selectedVintage" />
        </div>
        <div class="section">
            <h1 class="title">Ranglister</h1>
            <b-tabs v-model="activeTab" size="is-medium" position="is-centered" class="block">
                <b-tab-item label="Single">
                    <div class="columns">
                        <div class="column is-half">
                            <CategoryPoints ranking-list="MEN_SINGLE"/>
                        </div>
                        <div class="column is-half">
                            <CategoryPoints ranking-list="WOMEN_SINGLE"/>
                        </div>
                    </div>
                </b-tab-item>
                <b-tab-item label="Double">
                    <div class="columns">
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 1" ranking-list="MENS_DOUBLE"/>
                        </div>
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 1" ranking-list="WOMENS_DOUBLE"/>
                        </div>
                    </div>
                </b-tab-item>
                <b-tab-item label="Mix">
                    <div class="columns">
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 2" ranking-list="MEN_MIX"/>
                        </div>
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 2" ranking-list="WOMEN_MIX"/>
                        </div>
                    </div>
                </b-tab-item>
            </b-tabs>
        </div>
    </div>
</template>

<style scoped>

</style>
