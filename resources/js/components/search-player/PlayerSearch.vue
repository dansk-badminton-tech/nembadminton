<template>
    <div>
        <b-field>
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
                <template slot-scope="props">
                    <div class="media">
                        <div class="media-content">
                            {{ props.option.name }}
                            <br>
                            <small>
                                {{ findPositions(props.option) }}
                            </small>
                        </div>
                    </div>
                </template>
                <template slot="empty">No results for {{ querySearchName }}</template>
            </b-autocomplete>
        </b-field>
    </div>
</template>

<script>
import {debounce, findPositions} from "../../helpers";
import gql from 'graphql-tag'

export default {
    name: "PlayerSearch",
    methods: {
        findPositions,
        addMember(option, event) {
            if(option === null){
                return;
            }
            this.category.players.push(option);
            const inputs = document.querySelectorAll('input')
            const index = Array.from(inputs).indexOf(event.target) + 1
            if(inputs[index] !== undefined){
                inputs[index].focus()
            }
            this.$root.$emit('playersearch.addMemberToCategory');
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
        clubId: String,
        category: Object,
        excludePlayers: Array,
        version: Date
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
            query: gql`query MembersSearch($name: String, $hasClubs: MembersSearchHasClubsWhereConditions, $excludeMembers: [Int!], $version: String){
                      membersSearch(name: $name, hasClubs: $hasClubs, excludeMembers: $excludeMembers, orderBy: { column: NAME, order: ASC }) {
                        data {
                          id
                          name
                          gender
                          refId
                          points(version: $version){
                            points
                            position
                            category
                            vintage
                          }
                        }
                        paginatorInfo {
                          count
                          total
                        }
                      }
                    }
                `,
            fetchPolicy: "network-only",
            skip() {
                return this.searchName === ''
            },
            update: data => data.membersSearch,
            variables() {
                let params = {
                    name: '%' + this.searchName + '%',
                    excludeMembers: this.excludePlayers.map(member => member.id),
                }
                if (!!this.version) {
                    params.version = this.version.getFullYear() + "-" + (this.version.getMonth() + 1) + "-" + this.version.getDate()
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
