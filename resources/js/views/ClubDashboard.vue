<template>
    <div>
        <h1 v-if="!$apollo.queries.me.loading" class="title">{{ me.club.name1 }}</h1>
        <div class="level">
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading">Spiller total</p>
                    <p class="title">{{ clubStats.players }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading">Mænd</p>
                    <p class="title">{{ clubStats.menPlayers }}</p>
                </div>
            </div>
            <div class="level-item has-text-centered">
                <div>
                    <p class="heading">Kvinder</p>
                    <p class="title">{{ clubStats.womenPlayers }}</p>
                </div>
            </div>
        </div>
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
        <MemberList/>
    </div>
</template>

<script>

import gql from 'graphql-tag';
import {debounce} from "../helpers";
import MemberList from "./MemberList";
import MeQuery from '../queries/me.gql'

export default {
    name: "ClubDashboard",
    components: {MemberList},
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
            ]
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

