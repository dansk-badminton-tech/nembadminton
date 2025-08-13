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
            titleStack: ['Spillerportal', 'Automatisk kalender'],
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
                                Tilføj dine holdkampe direkte til din kalender med ét klik
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
                                    <p>Find dit hold og vælg de hold du vil følge</p>
                                </div>
                            </div>
                            <div class="column">
                                <div class="box has-background-light">
                                    <h4 class="title is-6">
                                        <span class="tag is-primary is-rounded">2</span>
                                        Klik på din kalender
                                    </h4>
                                    <p>Tryk på Google, Apple eller Outlook knappen</p>
                                </div>
                            </div>
                            <div class="column">
                                <div class="box has-background-light">
                                    <h4 class="title is-6">
                                        <span class="tag is-primary is-rounded">3</span>
                                        Færdig!
                                    </h4>
                                    <p>Kampprogrammet vises nu i din kalender</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Instructions Tabs -->
                    <div class="mt-5">
                        <h4 class="title is-6">Hvad sker der efter du klikker på knappen:</h4>

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
                                    <div class="content">
                                        <p>Når du klikker på <strong>"Google Calendar"</strong> knappen:</p>
                                        <ol>
                                            <li>Du bliver sendt til Google Calendar</li>
                                            <li>Bekræft at du vil tilføje kalenderen</li>
                                            <li>Vælg hvilken Google-konto du vil tilføje den til</li>
                                            <li>Kampprogrammet vises nu i din Google Calendar</li>
                                        </ol>
                                        <b-message type="is-info" size="is-small">
                                            <b-icon icon="information" size="is-small"></b-icon>
                                            Google opdaterer eksterne kalendere automatisk med nogle timers mellemrum
                                        </b-message>
                                    </div>
                                </div>
                            </div>

                            <div v-show="activeTab === 'outlook'" class="tab-pane">
                                <div class="box">
                                    <h5 class="title is-6">
                                        <b-icon icon="microsoft" size="is-small"></b-icon>
                                        Outlook
                                    </h5>
                                    <div class="content">
                                        <p>Når du klikker på <strong>"Outlook"</strong> knappen:</p>
                                        <ol>
                                            <li>Du bliver sendt til Outlook Calendar</li>
                                            <li>Giv kalenderen et navn</li>
                                            <li>Klik <strong>"Importér"</strong> for at tilføje den</li>
                                            <li>Kampprogrammet vises nu i din Outlook kalender</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div v-show="activeTab === 'apple'" class="tab-pane">
                                <div class="box">
                                    <h5 class="title is-6">
                                        <b-icon icon="apple" size="is-small"></b-icon>
                                        Apple Kalender
                                    </h5>
                                    <div class="content">
                                        <p>Når du klikker på <strong>"Apple Calendar"</strong> knappen:</p>
                                        <ol>
                                            <li>Apple Calendar app åbner automatisk</li>
                                            <li>Bekræft at du vil abonnere på kalenderen</li>
                                            <li>Vælg opdateringsfrekvens (anbefaler "Hver time")</li>
                                            <li>Kampprogrammet vises nu i Apple Calendar</li>
                                        </ol>
                                        <b-message type="is-warning" size="is-small">
                                            <b-icon icon="information" size="is-small"></b-icon>
                                            Virker kun på Mac/iPhone med Apple Calendar app installeret
                                        </b-message>
                                    </div>
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
            <p class="subtitle is-6 mb-4">Vælg hvilke hold du vil følge og tilføj direkte til din kalender.</p>

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
                                    <p class="subtitle is-6">Klik på dine kalender-knapper efter at have valgt hold</p>
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
