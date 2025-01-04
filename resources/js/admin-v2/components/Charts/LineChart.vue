<template>
    <line-chart-element
        :chart-id="chartId"
        :chart-options="chartOptions"
        :chart-data="chartData"
        :plugins="plugins"
        :width="width"
        :height="height"
    />
</template>

<script>
import {defineComponent} from 'vue'
import {Line as LineChartElement} from 'vue-chartjs/legacy'
import {
    Chart,
    LineElement,
    PointElement,
    LineController,
    LinearScale,
    CategoryScale,
    TimeScale,
    Tooltip,
    Legend,
    Title
} from 'chart.js'
import 'chartjs-adapter-moment';

Chart.register(LineElement, PointElement, LineController, LinearScale, CategoryScale, Tooltip, Legend,
               Title, TimeScale)

export default defineComponent({
                                   name: 'LineChart',
                                   components: {
                                       LineChartElement
                                   },
                                   props: {
                                       chartId: {
                                           type: String,
                                           default: 'line-chart'
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
                               })
</script>
