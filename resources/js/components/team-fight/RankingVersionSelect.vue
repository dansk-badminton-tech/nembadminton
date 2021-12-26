<template>
    <b-select @focus="onFocus" v-model="version" :expanded="expanded" placeholder="Vælge rangliste">
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
    props: ['value', 'expanded', 'afterChange'],
    methods: {
        onFocus(){
            this.$emit('focus')
        }
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
