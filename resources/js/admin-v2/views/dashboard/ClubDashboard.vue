<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            {{ me?.club?.name1 }}
        </hero-bar>
        <section class="section is-main-section">
            <tiles-block>
                <card-widget
                    class="tile is-child"
                    type="is-primary"
                    icon="account-multiple"
                    :number="clubStats?.players"
                    label="Spiller total"
                />
                <card-widget
                    class="tile is-child"
                    type="is-info"
                    icon="human-male"
                    :number="clubStats?.menPlayers"
                    label="Mænd"
                />
                <card-widget
                    class="tile is-child"
                    type="is-success"
                    icon="human-female"
                    :number="clubStats?.womenPlayers"
                    label="Kvinder"
                />
            </tiles-block>
            <h1 class="title">Medlemmer</h1>
            <h2 class="subtitle">Viser niveau ranglisten for indeværende måned</h2>
            <b-field label="Søg på navn">
                <b-input expanded @input="setName"></b-input>
            </b-field>
            <b-table :data="members.data"
                     :loading="$apollo.queries.members.loading"
                     :per-page="20"
                     :total="members.paginatorInfo.total"
                     backend-pagination
                     paginated
                     @page-change="onPageChange">
                <b-table-column v-slot="props" field="points" label="Points">
                    {{ props.row.latestLevelPoints }}
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
            </b-table>
            <ActivityLog/>
        </section>
    </div>
</template>

<script>

import gql from 'graphql-tag';
import ActivityLog from "../common/ActivityLog.vue";
import {debounce} from "@/helpers";
import MeQuery from '../../../queries/me.gql'
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import TilesBlock from "../../components/TilesBlock.vue";
import CardWidget from "../../components/CardWidget.vue";

export default {
    name: "ClubDashboard",
    components: {CardWidget, TilesBlock, HeroBar, TitleBar, ActivityLog},
    apollo: {
        clubStats: {
            query: gql`
                query clubStats{
                    clubStats{
                        players
                        womenPlayers
                        menPlayers
                    }
                }
            `
        },
        me: {
            query: MeQuery,
        },
        members: {
            query: gql`
                query members($page: Int, $name: String, $orderBy: [QueryMembersOrderByOrderByClause!]){
                    members(page: $page, name: $name, orderBy: $orderBy){
                        paginatorInfo{
                          total
                        }
                        data{
                          id
                          name
                          refId
                          vintage
                          latestLevelPoints
                          latestLevelPosition
                          latestLevelVersion
                        }
                      }
                }
            `,
            variables() {
                return {
                    name: '%' + this.name + '%',
                    orderBy: [{column: 'LATEST_LEVEL_POINTS', order: 'DESC'}],
                    page: this.page
                }
            }
        }
    },
    data() {
        return {
            titleStack: ['Admin', 'Dashboard'],
            name: '',
            page: 0,
            members: {
                data: [],
                paginatorInfo: {
                    total: 0
                }
            },
            columns: [
                {
                    field: 'id',
                    label: 'ID',
                    width: 40,
                    numeric: true
                },
                {
                    field: 'name',
                    label: 'Navn',
                },
                {
                    field: 'refId',
                    label: 'Badminton ID',
                }
            ],
        }
    },
    methods: {
        setName: debounce(function (name) {
            this.name = name
        }, 200),
        onPageChange(page) {
            this.page = page
        }
    }
}
</script>

