<script>
import gql from "graphql-tag";
import BulkMemberRankingProgression from "@/views/dashboard/BulkMemberRankingProgression.vue";

export default {
    name: "CategoryPoints",
    components: {BulkMemberRankingProgression},
    props: {
        rankingList: String
    },
    data(){
        return {
            memberSearchPoints: {
                data: [],
                paginatorInfo: {
                    total: 0
                }
            },
            categoryToTitle: {
                "ALL_LEVEL": "ALL",
                "WOMEN_SINGLE": "Dame single",
                "WOMENS_DOUBLE": "Dame double",
                "WOMEN_MIX": "Dame mix",
                "MEN_SINGLE": "Herre single",
                "MENS_DOUBLE": "Herre double",
                "MEN_MIX": "Herre mix"
            },
            latestRankingVersion: "2025-01-02",
            page: 1,
            perPage: 20,
            checkedMembers: []
        }
    },
    apollo: {
        latestRankingVersion: {
            query: gql`
                query latestRankingVersion{
                    latestRankingVersion
                }`
        },
        memberSearchPoints: {
            query: gql`
                query memberSearchPoints($version: Date!, $rankingList: RankingList!, $category: Mixed, $page: Int, $first: Int){
                    memberSearchPoints(version: $version, playable: true, rankingList: $rankingList, first: $first, page: $page){
                        paginatorInfo{
                          total
                        }
                        data{
                          id
                          name
                          refId
                          vintage
                          points(version: $version, where: {column: CATEGORY, value: $category}){
                            points
                            position
                            category
                            vintage
                          }
                        }
                      }
                }
            `,
            result(result) {
                if(this.checkedMembers.length === 0){
                    this.checkedMembers = result.data.memberSearchPoints.data.slice(0, 5);
                }
            },
            variables() {
                const mapping = {
                    "ALL_LEVEL": "ALL",
                    "WOMEN_SINGLE": "DS",
                    "WOMENS_DOUBLE": "DD",
                    "WOMEN_MIX": "MxD",
                    "MEN_SINGLE": "HS",
                    "MENS_DOUBLE": "HD",
                    "MEN_MIX": "MxH",
                }
                return {
                    category: mapping[this.rankingList],
                    rankingList: this.rankingList,
                    first: this.perPage,
                    page: this.page,
                    version: this.latestRankingVersion
                }
            }
        }
    },
    computed: {
        categoryTitle(){
            return this.categoryToTitle[this.rankingList]
        },
        memberIdsForStats(){
            return this.checkedMembers.map(m => m.id)
        }
    },
    methods: {
        onPageChange(page) {
            this.page = page
        }
    }
}
</script>

<template>
    <div>
        <h1 class="title">{{ categoryTitle }}</h1>
        <BulkMemberRankingProgression :member-ids="memberIdsForStats" :ranking-list="rankingList" />
        <hr />
        <b-table :data="memberSearchPoints.data"
                 :loading="$apollo.queries.memberSearchPoints.loading"
                 :per-page="20"
                 :total="memberSearchPoints.paginatorInfo.total"
                 backend-pagination
                 paginated
                 @page-change="onPageChange"
                 checkable
                 :checked-rows.sync="checkedMembers"
                 checkbox-type="is-info"
                 :custom-is-checked="(objA, objB) => {return objA.id === objB.id}"
                 >
            <b-table-column v-slot="props">
                # {{ ((page-1)*perPage) + props.index + 1}}
            </b-table-column>
            <b-table-column v-slot="props" field="points" label="Points">
                {{ props.row.points.map(p => p.points).join(", ") }}
            </b-table-column>
            <b-table-column v-slot="props" field="vintage" label="Aldersgruppe">
                {{ props.row.vintage }}
            </b-table-column>
            <b-table-column v-slot="props" field="name" label="Navn">
                {{ props.row.name }}
            </b-table-column>
            <b-table-column v-slot="props" field="refId" label="Badminton ID">
                {{ props.row.refId }}
            </b-table-column>
            <b-table-column v-slot="props" label="Stats">
                <b-button size="is-small" tag="router-link" :to="'/player/'+props.row.id+'/stats'" title="Ranging progression" icon-right="chart-bell-curve-cumulative"></b-button>
            </b-table-column>
        </b-table>
    </div>
</template>

<style scoped>

</style>
