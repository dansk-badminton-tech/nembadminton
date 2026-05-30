<template>
    <b-select :required="required" :loading="$apollo.queries.rankingVersions.loading" @focus="onFocus" v-model="version" :expanded="expanded" :placeholder="placeholder">
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
import {isRecommendedRankingVersionByPlayingDate} from "./ranking-version";

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
        },
        required: {
            type: Boolean,
            default: false
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
            if (isRecommendedRankingVersionByPlayingDate(currentVersion, this.playingDate)) {
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
