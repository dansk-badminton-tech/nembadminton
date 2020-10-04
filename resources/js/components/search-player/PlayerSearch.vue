<template>
    <div>
        <b-field :label="$t('roundsGenerator.findPlayer')">
            <b-autocomplete
                v-model="querySearchName"
                :clear-on-select="true"
                :clearable="true"
                :data="this.members.data"
                :keep-first="true"
                :loading="$apollo.queries.members.loading"
                :placeholder="$t('roundsGenerator.findPlayerPlaceholder')"
                field="name"
                @select="addMember"
                @typing="searchMembers">
                <template slot="empty">No results for {{ querySearchName }}</template>
            </b-autocomplete>
        </b-field>
    </div>
</template>

<script>
import {debounce} from "../../helpers";
import gql from 'graphql-tag'

export default {
    name: "PlayerSearch",
    methods: {
        addMember(option) {
            if (!option) {
                return
            }
            if (typeof this.addPlayer === 'function') {
                this.addPlayer(option)
            }
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
    props: {
        clubId: Number,
        addPlayer: Function,
        excludePlayers: Array
    },
    data() {
        return {
            querySearchName: '',
            searchName: '',
            members: {
                data: []
            }
        }
    },
    apollo: {
        members: {
            query: gql`query MembersSearch($name: String, $hasClubs: MembersSearchHasClubsWhereConditions, $excludeMembers: [Int!]){
                      membersSearch(name: $name, hasClubs: $hasClubs, excludeMembers: $excludeMembers, orderBy: { column: NAME, order: ASC }) {
                        data {
                          id
                          name
                          gender
                          points{
                            points
                            position
                            category
                          }
                        }
                        paginatorInfo {
                          count
                          total
                        }
                      }
                    }
                `,
            update: data => data.membersSearch,
            variables() {
                let params = {
                    name: '%' + this.searchName + '%',
                    excludeMembers: this.excludePlayers.map(member => member.id)
                }
                if (this.clubId) {
                    params.hasClubs = {
                        column: "ID",
                        operator: "EQ",
                        value: this.clubId
                    }
                }
                return params
            }
        }
    }
}
</script>

<style scoped>
.round-concers {
    border-radius: 25px;
    border: 2px solid #73AD21;
    padding: 20px;
}

.border {
    border: 2px solid #73AD21;
}
</style>
