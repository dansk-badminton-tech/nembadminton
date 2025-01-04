<script>
import gql from "graphql-tag";

export default {
    name: "CategoryPoints",
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
            page: 1,
            perPage: 20
        }
    },
    apollo: {
        memberSearchPoints: {
            query: gql`
                query memberSearchPoints($rankingList: RankingList!, $category: Mixed, $page: Int, $first: Int){
                    memberSearchPoints(version: "2024-12-02", playable: true, rankingList: $rankingList, first: $first, page: $page){
                        paginatorInfo{
                          total
                        }
                        data{
                          id
                          name
                          refId
                          vintage
                          points(version: "2024-12-02", where: {column: CATEGORY, value: $category}){
                            points
                            position
                            category
                            vintage
                          }
                        }
                      }
                }
            `,
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
                    page: this.page
                }
            }
        }
    },
    computed: {
        categoryTitle(){
            return this.categoryToTitle[this.rankingList]
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
        <b-table :data="memberSearchPoints.data"
                 :loading="$apollo.queries.memberSearchPoints.loading"
                 :per-page="20"
                 :total="memberSearchPoints.paginatorInfo.total"
                 backend-pagination
                 paginated
                 @page-change="onPageChange"
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
                <b-button size="is-small" tag="router-link" :to="'/player/'+props.row.id+'/stats'" title="Ranking progression" icon-right="chart-bell-curve-cumulative"></b-button>
            </b-table-column>
        </b-table>
    </div>
</template>

<style scoped>

</style>
