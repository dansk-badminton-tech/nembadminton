<template>
    <div>
        <b-field>
            <b-autocomplete
                @input="searchMembers"
                :readonly="disabled"
                :open-on-focus="!disabled"
                :clear-on-select="true"
                :clearable="true"
                :data="searchResult"
                :keep-first="true"
                :loading="$apollo.queries.membersSearch.loading || $apollo.queries.memberSearchTeamFight.loading"
                :placeholder="$t('roundsGenerator.findPlayerPlaceholder')"
                field="name"
                @focus="focusedFlag = true"
                @blur="focusedFlag = false"
                @select="addMember"
                @typing="searchMembers">
                <template slot-scope="props">
                    <div class="media">
                        <div class="media-content">
                            {{ props.option.name }}
                            <b-icon
                                v-show="props.option.isInSquad"
                                icon="users"
                                size="is-small">
                            </b-icon>
                            <br>
                            <small>
                                {{ findPositions(props.option) }}
                            </small>
                        </div>
                    </div>
                </template>
                <template slot="empty">Ingen spiller fundet.</template>
            </b-autocomplete>
        </b-field>
    </div>
</template>

<script>
import {debounce, findPositions, resolveGenderFromCategory} from "../../helpers";
import gql from 'graphql-tag'
import {groupBy} from "lodash/collection";

import MemberSearchTeamFight from "../../queries/memberSearchTeamFight.gql";

export default {
    name: "PlayerSearch",
    methods: {
        findPositions,
        addMember(player, event) {
            if (player === null) {
                return;
            }
            this.$emit('select-player', player)
        },
        searchMembers: debounce(function (name) {
            this.querySearchName = name
        }, 300)
    },
    props: {
        clubId: String,
        category: Object,
        excludePlayers: Array,
        version: Date,
        squad: Object,
        disabled: Boolean
    },
    computed: {
        searchResult() {
            let removeDuplicates = Object.values(groupBy(this.memberSearchTeamFightResult, 'refId')).filter((v) => v.length === 1).flat()
            let allPlayers = removeDuplicates.concat(this.memberSearchResult)
            allPlayers = allPlayers.filter((v, i, a) => a.findIndex(t => (t.refId === v.refId)) === i)
            return allPlayers
        }
    },
    data() {
        return {
            memberSearchTeamFightResult: [],
            memberSearchResult: [],
            querySearchName: '',
            focusedFlag: false
        }
    },
    apollo: {
        memberSearchTeamFight: {
            query: MemberSearchTeamFight,
            variables() {
                return {
                    name: '%' + this.querySearchName + '%',
                    squadId: parseInt(this.squad.id),
                    gender: resolveGenderFromCategory(this.category.category)
                }
            },
            fetchPolicy: "network-only",
            result({data}) {
                this.memberSearchTeamFightResult = data.memberSearchTeamFight.data
            },
            skip() {
                return !this.focusedFlag || this.squad.id == null
            }
        },
        membersSearch: {
            query: gql`query membersSearch($name: String, $excludeMembers: [Int!], $version: String, $gender: [Gender!]){
                      membersSearch(name: $name, excludeMembers: $excludeMembers, orderBy: { column: NAME, order: ASC }, gender: $gender) {
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
                      }
                    }
                `,
            fetchPolicy: "network-only",
            skip() {
                return !this.focusedFlag
            },
            variables() {
                let params = {
                    name: '%' + this.querySearchName + '%',
                    excludeMembers: this.excludePlayers.map(member => member.id),
                    gender: resolveGenderFromCategory(this.category.category)
                }
                if (!!this.version) {
                    params.version = this.version.getFullYear() + "-" + (this.version.getMonth() + 1) + "-" + this.version.getDate()
                }
                return params
            },
            result({data}) {
                this.memberSearchResult = data.membersSearch.data
            }
        }
    }
}
</script>
