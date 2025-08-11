<script>
import gql from "graphql-tag";
import _ from "lodash/fp";
import clubhouse from "../../../queries/clubhouse.gql";
import { getCurrentSeason } from "@/helpers.js";
import ICalGenerator from "@/views/calendar/ICalGenerator.vue";
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";

export default {
    name: "CalendarGenerator",
    components: { TitleBar, HeroBar, ICalGenerator },
    inject: ["clubhouseId"],
    apollo: {
        calendarGenerator: {
            query: gql`
                query calendarGenerator($clubhouseId: Int!) {
                    calendarGenerator(clubhouseId: $clubhouseId){
                        clubs {
                            id
                            name1
                        }
                    }
                }
            `,
            variables() {
                return {
                    clubhouseId: this.clubhouseId
                };
            }
        }
    },
    data() {
        return {
            titleStack: ['Admin', 'Automatisk kalender'],
            calendarGenerator: {
                clubs: []
            },
            activeTab: 'google',
            showInstructions: false
        };
    },
    methods: {
        setActiveTab(tab) {
            this.activeTab = tab;
        },
        toggleInstructions() {
            this.showInstructions = !this.showInstructions;
        }
    }
};
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack" />
        <hero-bar :has-right-visible="false">
            📅 Automatisk kalender
        </hero-bar>

        <!-- Quick Overview Card -->
        <div class="section">
            <div class="card">
                <div class="card-content">
                    <div class="columns">
                        <div class="column">
                            <h2 class="title is-4">
                                <b-icon icon="calendar-sync" size="is-small"></b-icon>
                                Automatisk kalender-sync
                            </h2>
                            <p class="subtitle is-6">
                                Få dine holdkampe direkte i Google Kalender, Outlook eller Apple Kalender
                            </p>
                        </div>
                        <div class="column is-narrow">
                            <b-button
                                @click="toggleInstructions"
                                :icon-left="showInstructions ? 'chevron-up' : 'chevron-down'"
                                type="is-info"
                                outlined>
                                {{ showInstructions ? 'Skjul vejledning' : 'Vis vejledning' }}
                            </b-button>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="content">
                                <b-icon icon="check" type="is-success" size="is-small"></b-icon>
                                <span>Automatisk opdatering fra badmintonplayer.dk</span>
                            </div>
                        </div>
                        <div class="column">
                            <div class="content">
                                <b-icon icon="share" type="is-info" size="is-small"></b-icon>
                                <span>Del med hele holdet</span>
                            </div>
                        </div>
                        <div class="column">
                            <div class="content">
                                <b-icon icon="sync" type="is-primary" size="is-small"></b-icon>
                                <span>Synkroniserer løbende</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions (Collapsible) -->
        <div class="section" v-show="showInstructions">
            <div class="card">
                <div class="card-content">
                    <h3 class="title is-5">Sådan kommer du i gang:</h3>
                    <div class="steps">
                        <div class="columns">
                            <div class="column">
                                <div class="box has-background-light">
                                    <h4 class="title is-6">
                                        <span class="tag is-primary is-rounded">1</span>
                                        Vælg dit hold
                                    </h4>
                                    <p>Find dit hold nedenfor</p>
                                </div>
                            </div>
                            <div class="column">
                                <div class="box has-background-light">
                                    <h4 class="title is-6">
                                        <span class="tag is-primary is-rounded">2</span>
                                        Kopiér linket
                                    </h4>
                                    <p>Kopier det genererede kalender-link</p>
                                </div>
                            </div>
                            <div class="column">
                                <div class="box has-background-light">
                                    <h4 class="title is-6">
                                        <span class="tag is-primary is-rounded">3</span>
                                        Tilføj til kalender
                                    </h4>
                                    <p>Følg vejledningen for din kalender-app</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Instructions Tabs -->
                    <div class="mt-5">
                        <h4 class="title is-6">Vejledning for din kalender:</h4>

                        <div class="tabs is-boxed">
                            <ul>
                                <li :class="{ 'is-active': activeTab === 'google' }">
                                    <a @click="setActiveTab('google')">
                                        <span class="icon is-small">
                                            <b-icon icon="google" size="is-small"></b-icon>
                                        </span>
                                        <span>Google</span>
                                    </a>
                                </li>
                                <li :class="{ 'is-active': activeTab === 'outlook' }">
                                    <a @click="setActiveTab('outlook')">
                                        <span class="icon is-small">
                                            <b-icon icon="microsoft" size="is-small"></b-icon>
                                        </span>
                                        <span>Outlook</span>
                                    </a>
                                </li>
                                <li :class="{ 'is-active': activeTab === 'apple' }">
                                    <a @click="setActiveTab('apple')">
                                        <span class="icon is-small">
                                            <b-icon icon="apple" size="is-small"></b-icon>
                                        </span>
                                        <span>Apple</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div v-show="activeTab === 'google'" class="tab-pane">
                                <div class="box">
                                    <h5 class="title is-6">
                                        <b-icon icon="google" size="is-small"></b-icon>
                                        Google Kalender
                                    </h5>
                                    <ol>
                                        <li>Gå til <strong>Google Kalender</strong></li>
                                        <li>Klik på <strong>"Tilføj kalender"</strong> → <strong>"Fra URL"</strong></li>
                                        <li>Indsæt linket og tryk <strong>"Tilføj kalender"</strong></li>
                                        <li>Kampprogrammet vises som en ny kalender</li>
                                    </ol>
                                    <b-message type="is-info" size="is-small">
                                        <b-icon icon="information" size="is-small"></b-icon>
                                        Google opdaterer eksterne kalendere med nogle timers mellemrum
                                    </b-message>
                                </div>
                            </div>

                            <div v-show="activeTab === 'outlook'" class="tab-pane">
                                <div class="box">
                                    <h5 class="title is-6">
                                        <b-icon icon="microsoft" size="is-small"></b-icon>
                                        Outlook (web-version)
                                    </h5>
                                    <ol>
                                        <li>Gå til <strong>Outlook Kalender</strong></li>
                                        <li>Klik på <strong>"Tilføj kalender"</strong> → <strong>"Abonner fra web"</strong></li>
                                        <li>Indsæt linket og giv kalenderen et navn</li>
                                        <li>Klik <strong>"Importér"</strong></li>
                                    </ol>
                                </div>
                            </div>

                            <div v-show="activeTab === 'apple'" class="tab-pane">
                                <div class="box">
                                    <h5 class="title is-6">
                                        <b-icon icon="apple" size="is-small"></b-icon>
                                        Apple Kalender (Mac)
                                    </h5>
                                    <ol>
                                        <li>Åbn <strong>Apple Kalender</strong></li>
                                        <li>Gå til <strong>Arkiv</strong> → <strong>"Nyt kalenderabonnement…"</strong></li>
                                        <li>Indsæt linket og klik <strong>"Abonner"</strong></li>
                                        <li>Vælg opdateringsfrekvens og klik <strong>OK</strong></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Calendar Generators -->
        <div class="section">
            <h3 class="title is-4">
                <b-icon icon="account-group" size="is-small"></b-icon>
                Vælg hold
            </h3>
            <p class="subtitle is-6 mb-4">Klik på det hold, du vil generere kalender-link for</p>

            <div class="columns is-multiline">
                <div class="column is-half" v-for="team in calendarGenerator?.clubs" :key="team.id">
                    <div class="card">
                        <div class="card-content">
                            <div class="media">
                                <div class="media-left">
                                    <figure class="image is-48x48">
                                        <div class="is-flex is-align-items-center is-justify-content-center"
                                             style="width: 48px; height: 48px; background: #3273dc; border-radius: 6px;">
                                            <b-icon icon="account-group" type="is-white" size="is-small"></b-icon>
                                        </div>
                                    </figure>
                                </div>
                                <div class="media-content">
                                    <p class="title is-5">{{team.name1}}</p>
                                    <p class="subtitle is-6">Generér kalender-link</p>
                                </div>
                            </div>

                            <ICalGenerator :team-id="team.id" />
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!calendarGenerator?.clubs?.length" class="has-text-centered py-6">
                <b-icon icon="calendar" size="is-large" type="is-grey-light"></b-icon>
                <p class="title is-5 has-text-grey-light mt-3">Ingen hold fundet</p>
                <p class="subtitle is-6 has-text-grey">Der er endnu ikke registreret nogen hold.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.steps .box {
    height: 100%;
}

.tab-content {
    padding-top: 1rem;
}

.tab-pane {
    min-height: 150px;
}

.tag.is-rounded {
    margin-right: 0.5rem;
}

.media {
    margin-bottom: 1rem;
}

.content {
    margin-bottom: 0.5rem;
}
</style>
