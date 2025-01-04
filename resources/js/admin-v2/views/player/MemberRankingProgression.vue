<script>
import LineChart from "@/components/Charts/LineChart.vue";

const randomColor = () => {
    const randomInt = () => Math.floor(Math.random() * 256); // Random number between 0 and 255
    return `rgb(${randomInt()}, ${randomInt()}, ${randomInt()})`; // Generate RGB color string
};

const datasetObject = (data, label) => {
    const color = randomColor(); // Call randomColor here
    return {
        fill: false,
        label: label,
        borderColor: color, // Assign the random color
        borderWidth: 2,
        borderDash: [],
        borderDashOffset: 0.0,
        pointBackgroundColor: color, // Use the same random color
        pointBorderColor: 'rgba(255,255,255,0)',
        pointHoverBackgroundColor: color,
        pointBorderWidth: 20,
        pointHoverRadius: 4,
        pointHoverBorderWidth: 15,
        pointRadius: 4,
        data: data,
        tension: 0.5
    }
}

export default {
    name: "MemberRankingProgression",
    props: {
        memberDataSets: {
            type: Array,
            default: () => []
        },
        title: String,
    },
    components: {LineChart},
    data() {
        return {
            chartOptions: {
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'month'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: this.title
                    },
                }
            }
        }
    },
    computed: {
        chartData() {
            if (this.memberDataSets.length > 0) {
                const datasets = this.memberDataSets.map(member => {
                    return datasetObject(
                        member.data.map(point => ({y: point.points, x: point.version})),
                        member.member?.name
                    )
                })
                return {
                    datasets
                }
            } else {
                return {
                    datasets: [
                        {
                            label: 'Loading',
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
        :style="{height: '50vh'}"
    />
</template>

<style scoped>

</style>
