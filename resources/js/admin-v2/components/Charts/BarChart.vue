<script>

import {Bar as BarChartElement} from 'vue-chartjs/legacy'
import {BarElement, CategoryScale, Chart, Legend, LinearScale, LineController, LineElement, PointElement, Title, Tooltip} from "chart.js";

Chart.register(BarElement, CategoryScale, Tooltip, Legend, LinearScale, Title)
export default {
    name: "BarChart",
    components: {BarChartElement},
    props: {
        chartId: {
            type: String,
            default: 'bar-chart'
        },
        chartData: {
            type: Object,
            default: null
        },
        chartOptions: {
            type: Object,
            default: null
        },
        width: {
            type: Number,
            default: 400
        },
        height: {
            type: Number,
            default: 150
        },
        plugins: {
            type: Array,
            default: () => []
        }
    }
}
</script>

<template>
    <bar-chart-element
        :chart-id="chartId"
        :chart-options="chartOptions"
        :chart-data="chartData"
        :plugins="plugins"
        :width="width"
        :height="height"
    />
</template>

<style scoped>

</style>
