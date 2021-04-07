<template>
    <b-select v-model="version" :expanded="expanded" placeholder="Vælge rankliste">
        <option
            v-for="version in rankingVersions"
            :key="version"
            :value="version">
            {{ version }}
        </option>
    </b-select>
</template>

<script>

import gql from 'graphql-tag'

export default {
    name: "RankingVersionSelect",
    props: ['value', 'expanded'],
    computed: {
        version: {
            get() {
                return this.value
            },
            set(newValue) {
                this.$emit('input', newValue);
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
