<template>
    <fragment>
        <b-field label="Find a name">
            <b-autocomplete
                v-model="querySearchName"
                placeholder="e.g. Daniel"
                :keep-first="true"
                :loading="$apollo.queries.members.loading"
                :data="this.members.data"
                :clear-on-select="true"
                field="name"
                @typing="searchMembers"
                @select="addMember"
                :clearable="true"
            >
                <template slot="empty">No results for {{ querySearchName }}</template>
            </b-autocomplete>
        </b-field>
        {{ selectedMembers }}
        <b-field :label="$t('createClub.clubName')">
            <b-input v-model="clubName"></b-input>
        </b-field>
    </fragment>
</template>

<script>
import {ValidationProvider} from "vee-validate";
import gql from 'graphql-tag'
import {debounce} from "../helpers";

export default {
    name: "RoundsGenerator",
    components: {
        ValidationProvider
    },
    data() {
        return {
            querySearchName: '',
            searchName: '',
            clubName: '',
            selectedMembers: [],
            members: {
                data: []
            }
        }
    },
    methods: {
        addMember(option) {
            if (!option) {
                return
            }
            this.selectedMembers.push(option)
        },
        searchMembers: debounce(function (name) {
            if (!name.length) {
                this.members.data = []
                return
            }
            this.searchName = name;
            this.$apollo.queries.members.refresh();
        }, 200)
    },
    apollo: {
        members: {
            query: gql`query MembersSearch($name: String){
                      members(name: $name, orderBy: { column: NAME, order: ASC }) {
                        data {
                          id
                          name
                          refId
                        }
                        paginatorInfo {
                          count
                          total
                        }
                      }
                    }
                `,
            variables() {
                return {
                    name: '%' + this.searchName + '%'
                }
            }
        }
    }
}
</script>

<style scoped>

</style>
