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
                    :number="clubhouseStats?.players"
                    label="Spillere totalt"
                />
                <card-widget
                    class="tile is-child"
                    type="is-info"
                    icon="human-male"
                    :number="clubhouseStats?.menPlayers"
                    label="Mænd"
                />
                <card-widget
                    class="tile is-child"
                    type="is-success"
                    icon="human-female"
                    :number="clubhouseStats?.womenPlayers"
                    label="Kvinder"
                />
            </tiles-block>
            <b-tabs v-model="activeTab" size="is-medium" position="is-centered" class="block">
                <b-tab-item label="Single">
                    <div class="columns">
                        <div class="column is-half">
                            <CategoryPoints ranking-list="MEN_SINGLE"/>
                        </div>
                        <div class="column is-half">
                            <CategoryPoints ranking-list="WOMEN_SINGLE"/>
                        </div>
                    </div>
                </b-tab-item>
                <b-tab-item label="Double">
                    <div class="columns">
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 1" ranking-list="MENS_DOUBLE"/>
                        </div>
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 1" ranking-list="WOMENS_DOUBLE"/>
                        </div>
                    </div>
                </b-tab-item>
                <b-tab-item label="Mix">
                    <div class="columns">
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 2" ranking-list="MEN_MIX"/>
                        </div>
                        <div class="column is-half">
                            <CategoryPoints v-if="activeTab === 2" ranking-list="WOMEN_MIX"/>

                        </div>
                    </div>
                </b-tab-item>
            </b-tabs>
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
import NotificationBar from "@/components/NotificationBar.vue";
import CategoryPoints from "@/views/dashboard/CategoryPoints.vue";

export default {
    name: "ClubDashboard",
    inject: ['clubhouseId'],
    components: {CategoryPoints, NotificationBar, CardWidget, TilesBlock, HeroBar, TitleBar, ActivityLog},
    apollo: {
        clubhouseStats: {
            query: gql`
                query clubhouseStats($id: ID!){
                    clubhouseStats(id: $id){
                        players
                        womenPlayers
                        menPlayers
                    }
                }
            `,
            variables() {
                return {
                    id: this.clubhouseId
                }
            },
            skip(){
                return this.clubhouseId === undefined
            }
        },
        me: {
            query: MeQuery,
        },
    },
    data() {
        return {
            titleStack: ['Admin', 'Dashboard'],
            activeTab: 0,
            name: '',
            page: 0,
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

