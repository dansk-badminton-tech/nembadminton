<template>
    <div>
        <h1 v-if="!$apollo.queries.me.loading" class="title">{{ me.club.name1 }}</h1>
        <h2 class="subtitle">Stamdata omkring klubben</h2>
        <div v-if="!$apollo.queries.clubStats.loading" class="columns is-multiline">
            <div class="column">
                <div class="box notification is-primary">
                    <div class="heading">Spiller</div>
                    <div class="title">{{ clubStats.players }}</div>
                    <div class="level">
                        <div class="level-item">
                            <div class="has-text-centered">
                                <div class="heading">Mand</div>
                                <div class="title is-5">{{ clubStats.menPlayers }}</div>
                            </div>
                        </div>
                        <div class="level-item">
                            <div class="has-text-centered">
                                <div class="heading">Kvinder</div>
                                <div class="title is-5">{{ clubStats.womenPlayers }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="title">Medlemmer</h1>
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
            <b-table-column v-slot="props" field="id" label="ID" width="40">
                {{ props.row.id }}
            </b-table-column>
            <b-table-column v-slot="props" field="name" label="Navn">
                {{ props.row.name }}
            </b-table-column>
            <b-table-column v-slot="props" field="refId" label="Badminton ID">
                {{ props.row.refId }}
            </b-table-column>
            <b-table-column v-slot="props" field="points" label="Niveau Position">
                {{ levelPoints(props.row.points, 'category')["null"][0].position }}
            </b-table-column>
            <b-table-column v-slot="props" label="">
                <b-button :to="'/player-stats/'+props.row.refId" size="is-small" tag="router-link" type="is-link">Stats</b-button>
            </b-table-column>
        </b-table>
        <MemberList/>
    </div>
</template>

<script>

import gql from 'graphql-tag';
import {debounce} from "../helpers";
import MemberList from "./MemberList";

export default {
    name: "ClubDashboard",
    components: {MemberList},
    apollo: {
        clubStats: {
            query: gql`
                query{
                    clubStats{
                        players
                        womenPlayers
                        menPlayers
                    }
                }
            `
        },
        me: {
            query: gql`
                query{
                    me{
                        id
                        club{
                            name1
                        }
                    }
                }`,
        },
        members: {
            query: gql`
                query($page: Int!, $name: String, $orderBy: [MembersOrderByOrderByClause!]){
                    members(page: $page, name: $name, orderBy: $orderBy){
                        paginatorInfo{
                          total
                        }
                        data{
                          id
                          name
                          refId
                          points{
                            points
                            position
                            category
                          }
                        }
                      }
                }
            `,
            variables() {
                return {
                    name: '%' + this.name + '%',
                    orderBy: [{column: 'NAME', order: 'ASC'}],
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
        levelPoints(points, key) {
            //console.log(points)
            return this.groupBy(points, key)
        },
        groupBy(xs, key) {
            return xs.reduce(function (rv, x) {
                (rv[x[key]] = rv[x[key]] || []).push(x);
                return rv;
            }, {});
        },
        onPageChange(page) {
            this.page = page
        }
    }
}
</script>

