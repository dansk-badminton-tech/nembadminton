<template>
    <b-select :loading="$apollo.queries.badmintonPlayerClubs.loading" expanded placeholder="Vælge klub" @input="handleInput">
        <option
            v-for="option in badmintonPlayerClubs"
            :key="option.id"
            :value="option.id">
            {{ option.name }}
        </option>
    </b-select>
</template>
<script>

import gql from "graphql-tag"

export default {
    name: 'BadmintonPlayerClubs',
    props: ['value'],
    methods: {
        handleInput(value) {
            this.$emit('input', value)
        }
    },
    apollo: {
        badmintonPlayerClubs: {
            query: gql`
                query {
                 badmintonPlayerClubs{
                    id
                    name
                  }
                }
               `
        },
    }
}
</script>
