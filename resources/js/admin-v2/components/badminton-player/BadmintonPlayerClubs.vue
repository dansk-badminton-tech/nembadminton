<template>
    <b-select required="required" dusk="club-select" :loading="$apollo.queries.clubs.loading" expanded placeholder="Vælg klub" @input="handleInput">
        <option
            v-for="option in clubs"
            :key="option.id"
            :value="option.badmintonPlayerId">
            {{ option.name1 }}
        </option>
    </b-select>
</template>
<script>

import gql from "graphql-tag"

export default {
    name: 'BadmintonPlayerClubs',
    props: {
        'value': null,
        'required': {
            type: Boolean,
            default: false
        }
    },
    methods: {
        handleInput(value) {
            this.$emit('input', value)
        }
    },
    apollo: {
        clubs: {
            query: gql`
                query {
                 clubs{
                    id
                    name1
                    badmintonPlayerId
                  }
                }
               `
        },
    }
}
</script>
