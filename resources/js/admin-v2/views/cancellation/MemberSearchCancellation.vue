<template>
    <b-autocomplete
        :data="data"
        @input="searchMembers"
        @typing="searchMembers"
        @select="option => selected = option"
        field="name"
    >
        <template v-slot:default="{ option }">
            <div class="media">
                <div class="media-content">
                    {{ option.name }}
                    <br>
                    <small>
                        {{ (option.refId) }}
                    </small>
                </div>
            </div>
        </template>
    </b-autocomplete>
</template>

<script>
import gql from "graphql-tag";
import {debounce} from "@/helpers.js";

export default {
    name: "MemberSearchCancellation",
    props: {clubs: Array},
    data(){
        return {
            data: [],
            querySearchName: '',
            selected: null
        }
    },
    watch: {
        selected(val){
            this.$emit('input', val)
        }
    },
    methods: {
        searchMembers: debounce(function (name) {
            this.querySearchName = name
        }, 300),
    },
    apollo: {
        membersCancellationSearch: {
            query: gql`
                query membersCancellationSearch($clubIds: [Int!]!, $name: String) {
                    membersCancellationSearch(clubIds: $clubIds, name: $name, first: 10) {
                        data{
                            id
                            name
                            refId
                        }
                    }
                }
            `,
            variables() {
                return {
                    clubIds: this.clubs.map(c => parseInt(c.id)),
                    name: '%' + this.querySearchName + '%'
                };
            },
            result({data}) {
                this.data = data.membersCancellationSearch.data
            }
        }
    }
}
</script>
