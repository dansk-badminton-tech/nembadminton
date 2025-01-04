<script>
import gql from "graphql-tag";
import MemberRankingProgression from "@/views/player/MemberRankingProgression.vue";

export default {
    name: "BulkMemberRankingProgression",
    components: {MemberRankingProgression},
    props: {
        memberIds: {
            type: Array,
            default: () => []
        },
        rankingList: String
    },
    apollo: {
        membersStats: {
            query: gql`query membersStats($ids: [ID!]!){
                membersStats(ids: $ids){
                    member{
                        id
                        name
                    }
                    mix{
                        version
                        points
                    }
                    single{
                        version
                        points
                    }
                    double{
                        version
                        points
                    }
                }
            }`,
            variables() {
                return {
                    ids: this.memberIds
                }
            }
        }
    },
    data() {
        return {
            membersStats: [],
            mapping: {
                "ALL_LEVEL": "ALL",
                "WOMEN_SINGLE": "single",
                "WOMENS_DOUBLE": "double",
                "WOMEN_MIX": "mix",
                "MEN_SINGLE": "single",
                "MENS_DOUBLE": "double",
                "MEN_MIX": "mix",
            }
        }
    },
    computed: {
        stats() {
            return this.membersStats.map((member) => {
                return {
                    member: member.member,
                    data: member[this.mapping[this.rankingList]]
                }
            })
        }
    }
}
</script>

<template>
    <MemberRankingProgression :member-data-sets="stats" title="Rang progression"/>
</template>

<style scoped>

</style>
