<script>
import gql from "graphql-tag";
import BarChart from "@/components/Charts/BarChart.vue";

export default {
    name: "HighestPointGainChart",
    props: {category: String, limit: Number, orderBy: String, vintages: Array, clubhouseId: String},
    components: {BarChart},
    apollo: {
        highestPointGain: {
            query: gql`query highestPointGain($clubhouseId: ID!, $category: Category!, $limit: Int!, $orderBy: SortOrder!, $vintages: [Vintage!]!){
                highestPointGain(clubhouseId: $clubhouseId, category: $category, limit: $limit, orderBy: $orderBy, vintages: $vintages){
                    earliestPoints
                    latestPoints
                    totalIncrease
                    member {
                        id
                        name
                    }
                }
            }`,
            variables(){
                return {
                    category: this.category,
                    limit: this.limit,
                    orderBy: this.orderBy,
                    vintages: this.vintages,
                    clubhouseId: this.clubhouseId,
                }
            }
        }
    },
    computed:{
        highestPointGainDataset() {
            if (this.highestPointGain.length === 0) {
                return {}
            }

            function getRandomInt() {
                const min = 1;  // Minimum value (inclusive)
                const max = 100; // Maximum value (inclusive)
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            return {
                datasets: [
                    {
                        backgroundColor: [
                            "rgb(33, 150, 243)", // Blue
                        ],
                        borderColor: [
                            "rgb(33, 150, 243)", // Blue
                        ],
                        data: this.highestPointGain.map((point) => {
                            return {x: point.member.name, y: point.totalIncrease, earliestPoints: point.earliestPoints, latestPoints: point.latestPoints}
                        }),
                        borderWidth: 1,
                    },
                ]
            }
        }
    },
    data(){
        return {
            highestPointGain: [],
            chartOptions: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            footer(tooltipItems) {
                                if (this.orderBy === 'DESC'){
                                    return 'Lavpunkt: '+tooltipItems[0].raw.earliestPoints+', Højdepunkt: '+tooltipItems[0].raw.latestPoints
                                }else{
                                    return 'Lavpunkt: '+tooltipItems[0].raw.latestPoints+', Højdepunkt: '+tooltipItems[0].raw.earliestPoints
                                }
                            },
                        }
                    }
                }
            }
        }
    }
}
</script>

<template>
    <div class="is-relative">
        <b-loading :active="$apollo.queries.highestPointGain.loading" :is-full-page="false"></b-loading>
        <BarChart :chart-data="highestPointGainDataset" :chart-options="chartOptions"/>
    </div>
</template>

<style scoped>

</style>
