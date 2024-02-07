<template>
    <b-select :loading="$apollo.queries.rankingVersions.loading" @focus="onFocus" v-model="version" :expanded="expanded" :placeholder="placeholder">
        <option
            v-for="version in rankingVersions"
            :key="version"
            :value="version"
        >
            {{ timeToMonth(version) }}
            {{ hintText(version) }}
        </option>
    </b-select>
</template>

<script>

import gql from 'graphql-tag'
import {timeToMonth} from "../team-fight/helper";

export default {
    name: "RankingVersionSelect",
    props: {
        'value': String,
        'expanded': Boolean|null,
        'afterChange': Function|null,
        'playingDate': Date|null,
        'placeholder': {
            type: String,
            default: "Vælge rangliste"
        }
    },
    methods: {
        onFocus(){
            this.$emit('focus')
        },
        timeToMonth: timeToMonth,
        hintText(currentVersion){
            if(this.playingDate === null || this.playingDate === undefined){
                return ''
            }
            let date = new Date(Date.parse(currentVersion))
            let lowerBound = new Date(Date.parse(currentVersion))
            lowerBound.setUTCDate(10)
            lowerBound.setHours(0, 0, 0,0)
            let upperBound = new Date(date.setMonth(date.getMonth()+1, 9))
            upperBound.setHours(0, 0, 0,0)
            if(lowerBound.getTime() <= this.playingDate.getTime() && this.playingDate.getTime() <= upperBound.getTime()){
                return '(Anbefalet baseret på spilledagen)'
            }
        },
    },
    computed: {
        version: {
            get() {
                return this.value
            },
            set(newValue) {
                this.$emit('input', newValue);
                this.$emit('change', newValue, this.value)
            }
        }
    },
    apollo: {
        rankingVersions: {
            query: gql`
                    query{
                        rankingVersions
                    }
                `
        }
    }
}
</script>

<style scoped>

</style>
