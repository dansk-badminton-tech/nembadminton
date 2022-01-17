<template>
    <b-select expanded placeholder="Vælge rangliste" @input="handleInput">
        <option
            v-for="version in rankingVersionsBP"
            :key="version"
            :value="version">
            {{ version }}
        </option>
    </b-select>
</template>

<script>
import gql from "graphql-tag";

export default {
    name: 'RankingListDropdown',
    props: ['value', 'season'],
    methods: {
        handleInput(value) {
            this.$emit('input', value)
        }
    },
    apollo: {
        rankingVersionsBP: {
            query: gql`
                    query rankingVersionsBP{
                        rankingVersionsBP
                    }
                `
        }
    }
};
</script>
