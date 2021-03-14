<template>
    <div>
        <b-field label="Badminton ID">
            <b-input v-model="badmintonId" type="text" @change="searchPlayer"/>
        </b-field>
        <b-loading v-model="$apollo.loading" :can-cancel="true" :is-full-page="false"></b-loading>
        <div v-if="playerStats !== undefined && playerStats !== null">
            <h1 class="title">{{ playerStats.player.name || '' }}</h1>
            <div class="columns is-desktop is-multiline">
                <div class="column is-half">
                    <line-chart :chart-data="dataPoints" :options="optionsPoints"></line-chart>
                </div>
                <div class="column is-half">
                    <line-chart :chart-data="dataPositions" :options="optionsPosition"></line-chart>
                </div>
                <div v-for="chardata in chartDatas" v-if="chardata.labels.length > 0" class="column is-half">
                    <line-chart :chart-data="chardata" :options="optionsPosition"></line-chart>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LineChart from "../components/charts/LineChart";
import gql from 'graphql-tag';

export default {
    name: "PlayerStats",
    components: {LineChart},
    data() {
        return {
            dataPoints: {
                labels: [],
                datasets: []
            },
            optionsPoints: {
                responsive: true,
                maintainAspectRatio: false
            },
            dataPositions: {
                labels: [],
                datasets: []
            },
            optionsPosition: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        display: true,
                        ticks: {
                            min: 1,
                            suggestedMin: 1,
                            reverse: true
                        }
                    }]
                }
            },
            badmintonId: '',
            chartDatas: []
        }
    },
    methods: {
        searchPlayer() {
            if (this.badmintonId.length < 6) {
                return;
            }
            this.$apollo.queries.playerStats.refresh()
        }
    },
    apollo: {
        playerStats: {
            query: gql`
                    query($badmintonId: String){
                        playerStats(badmintonId: $badmintonId){
                            player{
                                name
                            }
                            level{
                              points
                              position
                              version
                            },
                            mixWomen{
                              points
                              position
                              version
                            }
                            mixMen{
                              points
                              position
                              version
                            }
                            singleWomen{
                              points
                              position
                              version
                            }
                            singleMen{
                              points
                              position
                              version
                            }
                            doubleMen{
                              points
                              position
                              version
                            }
                            doubleWomen{
                              position
                              points
                              version
                            }
                          }
                    },
                `,
            skip: function () {
                return this.badmintonId.length < 6
            },
            variables: function () {
                return {
                    badmintonId: this.badmintonId
                }
            },
            result(ApolloQueryResult, key) {
                if (ApolloQueryResult.data.playerStats === null) {
                    return null
                }

                let generateChartData = (key, label, value = 'position') => {
                    if (ApolloQueryResult.data.playerStats.hasOwnProperty(key)) {
                        let labels = ApolloQueryResult.data.playerStats[key].map(body => body.version)
                        let dataset = ApolloQueryResult.data.playerStats[key].map(body => body[value]);
                        return {
                            labels: labels,
                            datasets: [{
                                label: label,
                                fill: false,
                                data: dataset
                            }]
                        }
                    } else {
                        return {
                            labels: [],
                            datasets: []
                        }
                    }
                }

                let categories = [
                    {key: 'doubleMen', label: 'Herre double'},
                    {key: 'doubleWomen', label: 'Dame double'},
                    {key: 'mixMen', label: 'Herre Mix double'},
                    {key: 'mixWomen', label: 'Dame Mix double'},
                    {key: 'singleMen', label: 'Herre single'},
                    {key: 'singleWomen', label: 'Dame single'}
                ]

                this.chartDatas = []
                for (let category of categories) {
                    this.chartDatas.push(generateChartData(category.key, category.label));
                }

                this.dataPoints = generateChartData('level', 'Points', 'points')
                this.dataPositions = generateChartData('level', 'Position')
            },
            fetchPolicy: "network-only"
        }
    }
}
</script>

<style scoped>

</style>
