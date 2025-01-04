<script>
import LineChart from "@/components/Charts/LineChart.vue";

function pluck(array, key) {
    return array.map(o => o[key]);
}

const datasetObject = (data) => {
    return {
        fill: false,
        borderColor: '#00D1B2',
        borderWidth: 2,
        borderDash: [],
        borderDashOffset: 0.0,
        pointBackgroundColor: '#00D1B2',
        pointBorderColor: 'rgba(255,255,255,0)',
        pointHoverBackgroundColor: '#00D1B2',
        pointBorderWidth: 20,
        pointHoverRadius: 4,
        pointHoverBorderWidth: 15,
        pointRadius: 4,
        data: data,
        tension: 0.5,
        cubicInterpolationMode: 'default'
    }
}
export default {
    name: "MemberRankingProgression",
    props: {
        data: {
            type: Array,
            default: []
        }
    },
    components: {LineChart},
    data(){
        return {
            chartOptions: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        }
    },
    computed: {
        chartData(){
            if(this.data.length > 0){
                const labels = pluck(this.data, 'version')
                const data = pluck(this.data, 'points')
                return {
                    labels: labels,
                    datasets: [
                        datasetObject(data)
                    ]
                }
            }else{
                return {
                    labels: [],
                    datasets: [
                        {
                            label: 'Data One',
                            backgroundColor: '#f87979',
                            data: []
                        }
                    ]
                }
            }
        }
    }
}
</script>

<template>
    <LineChart
        :chart-data="chartData"
        :chart-options="chartOptions"
        :style="{height: '100%'}"
    />
</template>

<style scoped>

</style>
