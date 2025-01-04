<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import gql from "graphql-tag";
import LineChart from "@/components/Charts/LineChart.vue";
import MemberRankingProgression from "@/views/player/MemberRankingProgression.vue";

export default {
    name: "Stats",
    components: {MemberRankingProgression, LineChart, HeroBar, TitleBar},
    props: ['playerId'],
    data() {
        return {
            titleStack: ['Admin', 'Statistik'],
            memberStats: {
                mix: [],
                single: [],
                double: []
            }
        }
    },
    apollo: {
        memberStats: {
            query: gql`query memberStats($id: ID!){
                memberStats(id: $id){
                    member{
                        id
                        name
                    }
                    mix{
                        version
                        points
                    }
                    single{
                        version
                        points
                    }
                    double{
                        version
                        points
                    }
                }
            }`,
            variables(){
                return {
                    id: this.playerId
                }
            }
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Ranging progression for <i>{{ memberStats.member.name }}</i>
        </hero-bar>
        <section class="section is-main-section">
            <div class="columns is-multiline">
                <div class="column is-half">
                    <h1 class="title is-4">Mix</h1>
                    <MemberRankingProgression :data="memberStats.mix"/>
                </div>
                <div class="column is-half">
                    <h1 class="title is-4">Single</h1>
                    <MemberRankingProgression :data="memberStats.single"/>
                </div>
                <div class="column is-half">
                    <h1 class="title is-4">Double</h1>
                    <MemberRankingProgression :data="memberStats.double"/>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>

</style>
