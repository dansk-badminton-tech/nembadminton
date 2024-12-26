<template>
    <b-autocomplete
        :data="data"
        v-model="player"
        @input="searchMembers"
        @typing="searchMembers"
        @select="option => selected = option"
        field="name"
        :clearable="true"
        :open-on-focus="true"
        required
        :readonly="selected !== null"
        @keyup.native="clearOnBackspace"
        placeholder="Søg efter medlem"
        keep-first
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
    props: {value: Object, clubs: Array},
    data(){
        return {
            data: [],
            querySearchName: '',
            player: null,
            selected: null
        }
    },
    watch: {
        value(val){
            this.player = null
            this.selected = val
        },
        selected(val){
            this.$emit('input', val)
        }
    },
    methods: {
        clearOnBackspace(nativeEvent) {
            if (nativeEvent.key === 'Backspace' && this.selected !== null) {
                this.selected = null;
                this.player = null;
            }
        },
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
