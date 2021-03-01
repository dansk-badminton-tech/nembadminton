<template>
    <div>
        <b-field label="Badminton ID">
            <b-input v-model="badmintonId" type="text" @change="searchPlayer"/>
        </b-field>
        <div class="columns">
            <b-loading v-model="$apollo.loading" :can-cancel="true" :is-full-page="false"></b-loading>
            <div class="column is-half">
                <line-chart :chart-data="dataPoints" :options="optionsPoints"></line-chart>
            </div>
            <div class="column">
                <line-chart :chart-data="dataPositions" :options="optionsPosition"></line-chart>
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
            badmintonId: ''
        }
    },
    methods: {
        searchPlayer() {
            this.$apollo.queries.playerStats.refresh()
        }
    },
    apollo: {
        playerStats: {
            query: gql`
                    query($badmintonId: String){
                        playerStats(badmintonId: $badmintonId){
                            level{
                              points
                              position
                              version
                            },
                            mixWomen{
                              points
                              position
                            }
                            mixMen{
                              points
                              position
                            }
                            singleWomen{
                              points
                              position
                            }
                            singleMen{
                              points
                              position
                            }
                            doubleMen{
                              points
                              position
                            }
                            doubleWomen{
                              position
                              points
                            }
                          }
                    },
                `,
            variables: function () {
                return {
                    badmintonId: this.badmintonId ?? ''
                }
            },
            result(ApolloQueryResult, key) {
                let pointsLabels = ApolloQueryResult.data.playerStats.level.map(body => body.version)
                let pointsDataset = ApolloQueryResult.data.playerStats.level.map(body => body.points);
                this.dataPoints = {
                    labels: pointsLabels,
                    datasets: [{
                        label: 'Points',
                        fill: false,
                        borderColor: '#E09228',
                        data: pointsDataset
                    }]
                }

                let positionLabels = ApolloQueryResult.data.playerStats.level.map(body => body.version)
                let positionDataset = ApolloQueryResult.data.playerStats.level.map(body => body.position);
                this.dataPositions = {
                    labels: positionLabels,
                    datasets: [{
                        label: 'Position',
                        fill: false,
                        borderColor: '#E09228',
                        data: positionDataset
                    }]
                }
            },
            fetchPolicy: "network-only"
        }
    }
}
</script>

<style scoped>

</style>
